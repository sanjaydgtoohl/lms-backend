<?php

namespace App\Http\Controllers;

use App\Services\LeadSourceService;
use App\Services\ResponseService;
use App\Http\Resources\LeadSourceResource;
use Illuminate\Http\Request;

class LeadSourceController extends Controller
{
    protected $leadSourceService;
    protected $responseService;

    public function __construct(
        LeadSourceService $leadSourceService, 
        ResponseService $responseService
    ) {
        $this->leadSourceService = $leadSourceService;
        $this->responseService = $responseService;
    }

    public function index()
    {
        $leadSources = $this->leadSourceService->getAllLeadSources();
        return $this->responseService->paginated($leadSources, 'Lead sources fetched successfully.');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255|unique:lead_source', // Table ka naam 'lead_source' hai
            'description' => 'nullable|string',
            'status' => 'nullable|in:1,2,15',
        ]);

        $leadSource = $this->leadSourceService->createNewLeadSource($request->all());
        
        return $this->responseService->created(
            new LeadSourceResource($leadSource), 
            'Lead source created successfully.'
        );
    }

    public function show($id)
    {
        $leadSource = $this->leadSourceService->getLeadSource($id);
        return $this->responseService->success(
            new LeadSourceResource($leadSource), 
            'Lead source fetched successfully.'
        );
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            // 'unique' rule ko current ID ignore karne ke liye update kiya
            'name' => 'sometimes|required|string|max:255|unique:lead_source,name,' . $id,
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:1,2,15',
        ]);

        $leadSource = $this->leadSourceService->updateLeadSource($id, $request->all());
        
        return $this->responseService->updated(
            new LeadSourceResource($leadSource), 
            'Lead source updated successfully.'
        );
    }

    public function destroy($id)
    {
        $this->leadSourceService->deleteLeadSource($id);
        return $this->responseService->deleted('Lead source deleted successfully.');
    }
}
