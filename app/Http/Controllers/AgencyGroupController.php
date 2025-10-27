<?php
namespace App\Http\Controllers;

use App\Services\AgencyGroupService;
use App\Services\ResponseService;
use App\Http\Resources\AgencyGroupResource;
use Illuminate\Http\Request;

class AgencyGroupController extends Controller
{
    protected $service, $response;
    public function __construct(AgencyGroupService $service, ResponseService $response) {
        $this->service = $service;
        $this->response = $response;
    }

    public function index() {
        $data = $this->service->getAll();
        return $this->response->paginated(AgencyGroupResource::collection($data), 'Agency groups fetched successfully.');
    }
    public function store(Request $request) {
        $this->validate($request, ['name' => 'required|string|max:255|unique:agency_groups']);
        $model = $this->service->create($request->all());
        return $this->response->created(new AgencyGroupResource($model), 'Agency group created successfully.');
    }
    public function show($id) {
        $model = $this->service->getById($id);
        return $this->response->success(new AgencyGroupResource($model), 'Agency group fetched successfully.');
    }
    public function update(Request $request, $id) {
        $this->validate($request, ['name' => 'sometimes|required|string|max:255|unique:agency_groups,name,' . $id]);
        $model = $this->service->update($id, $request->all());
        return $this->response->updated(new AgencyGroupResource($model), 'Agency group updated successfully.');
    }
    public function destroy($id) {
        $this->service->delete($id);
        return $this->response->deleted('Agency group deleted successfully.');
    }
}
