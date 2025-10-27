<?php
namespace App\Http\Controllers;

use App\Services\AgencyService;
use App\Services\ResponseService;
use App\Http\Resources\AgencyResource;
use Illuminate\Http\Request;

class AgencyController extends Controller
{
    protected $service, $response;
    public function __construct(AgencyService $service, ResponseService $response) {
        $this->service = $service;
        $this->response = $response;
    }

    public function index() {
        $data = $this->service->getAll();
        return $this->response->paginated(AgencyResource::collection($data), 'Agencies fetched successfully.');
    }

    /**
     * Complex Store Method (Bulk Create)
     */
    public function store(Request $request) {
        // Complex validation
        $this->validate($request, [
            'group_type' => 'nullable|in:existing,new,none',
            'group_id' => 'required_if:group_type,existing|integer|exists:agency_groups,id',
            'group_name' => 'required_if:group_type,new|string|max:255|unique:agency_groups,name',
            
            'agencies' => 'required|array|min:1',
            'agencies.*.name' => 'required|string|max:255', // Unique rule agency slug service handle karegi
            'agencies.*.agency_type_id' => 'required|integer|exists:agency_type,id',
            
            'agencies.*.brands' => 'nullable|array',
            'agencies.*.brands.*.name' => 'required|string|max:255',
        ]);

        $models = $this->service->createComplexAgency($request->all());
        
        return $this->response->created(
            AgencyResource::collection($models), 
            'Agencies created successfully.'
        );
    }

    public function show($id) {
        $model = $this->service->getById($id);
        return $this->response->success(new AgencyResource($model), 'Agency fetched successfully.');
    }

    // Simple update (bulk update nahi)
    public function update(Request $request, $id) {
        $this->validate($request, [
            'name' => 'sometimes|required|string|max:255|unique:agency,name,' . $id,
            'agency_type_id' => 'sometimes|required|integer|exists:agency_type,id',
            'agency_group_id' => 'nullable|integer|exists:agency_groups,id',
            'status' => 'sometimes|required|in:1,2,15',
        ]);
        
        $model = $this->service->update($id, $request->only(['name', 'agency_type_id', 'agency_group_id', 'status']));
        return $this->response->updated(new AgencyResource($model), 'Agency updated successfully.');
    }

    public function destroy($id) {
        $this->service->delete($id);
        return $this->response->deleted('Agency deleted successfully.');
    }
}
