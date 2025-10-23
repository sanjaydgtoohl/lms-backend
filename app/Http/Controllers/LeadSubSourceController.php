<?php

namespace App\Http\Controllers;

use App\Services\LeadSubSourceService;
use App\Services\ResponseService;
use App\Http\Resources\LeadSubSourceResource;
use Illuminate\Http\Request;

class LeadSubSourceController extends Controller
{
    protected $leadSubSourceService;
    protected $responseService;

    public function __construct(
        LeadSubSourceService $leadSubSourceService, 
        ResponseService $responseService
    ) {
        $this->leadSubSourceService = $leadSubSourceService;
        $this->responseService = $responseService;
    }

    /**
     * Sabhi Sub-sources laayein.
     * Filter: /api/v1/lead-sub-sources?lead_source_id=1
     */
    public function index(Request $request)
    {
        // Query parameter se filter validate karein
        $this->validate($request, [
            'lead_source_id' => 'nullable|integer|exists:lead_source,id'
        ]);
        
        $filters = $request->only(['lead_source_id']);
        $leadSubSources = $this->leadSubSourceService->getAllLeadSubSources($filters);
        
        return $this->responseService->paginated(
            LeadSubSourceResource::collection($leadSubSources), // Collection resource use karein
            'Lead sub-sources fetched successfully.'
        );
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            // Parent ID zaroori hai aur 'lead_source' table mein hona chahiye
            'lead_source_id' => 'required|integer|exists:lead_source,id',
            'name' => 'required|string|max:255', // Unique rule yahan zaroori nahi, slug unique hai
            'description' => 'nullable|string',
            'status' => 'nullable|in:1,2,15',
        ]);

        $leadSubSource = $this->leadSubSourceService->createNewLeadSubSource($request->all());
        
        return $this->responseService->created(
            new LeadSubSourceResource($leadSubSource), 
            'Lead sub-source created successfully.'
        );
    }

    public function show($id)
    {
        $leadSubSource = $this->leadSubSourceService->getLeadSubSource($id);
        return $this->responseService->success(
            new LeadSubSourceResource($leadSubSource), 
            'Lead sub-source fetched successfully.'
        );
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'lead_source_id' => 'sometimes|required|integer|exists:lead_source,id',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:1,2,15',
        ]);

        $leadSubSource = $this->leadSubSourceService->updateLeadSubSource($id, $request->all());
        
        return $this->responseService->updated(
            new LeadSubSourceResource($leadSubSource), 
            'Lead sub-source updated successfully.'
        );
    }

    public function destroy($id)
    {
        $this->leadSubSourceService->deleteLeadSubSource($id);
        return $this->responseService->deleted('Lead sub-source deleted successfully.');
    }
}
