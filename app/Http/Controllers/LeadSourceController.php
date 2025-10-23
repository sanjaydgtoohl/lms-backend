<?php

namespace App\Http\Controllers;

use App\Services\LeadSourceService;
use App\Services\ResponseService;
use App\Http\Resources\LeadSourceResource;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Validation\ValidationException;

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
        try {
            $leadSources = $this->leadSourceService->getAllLeadSources();
            return $this->responseService->paginated(
                $leadSources,
                'Lead sources fetched successfully.'
            );
        } catch (Exception $e) {
            return $this->responseService->error('Failed to fetch lead sources.', [$e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required|string|max:255|unique:lead_source',
                'description' => 'nullable|string',
                'status' => 'nullable|in:1,2,15',
            ]);

            $leadSource = $this->leadSourceService->createNewLeadSource($request->all());

            return $this->responseService->created(
                new LeadSourceResource($leadSource),
                'Lead source created successfully.'
            );
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors());
        } catch (Exception $e) {
            return $this->responseService->error('Failed to create lead source.', [$e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $leadSource = $this->leadSourceService->getLeadSource($id);

            return $this->responseService->success(
                new LeadSourceResource($leadSource),
                'Lead source fetched successfully.'
            );
        } catch (ModelNotFoundException $e) {
            return $this->responseService->notFound('Lead source not found.');
        } catch (Exception $e) {
            return $this->responseService->error('Failed to fetch lead source.', [$e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'name' => 'sometimes|required|string|max:255|unique:lead_source,name,' . $id,
                'description' => 'nullable|string',
                'status' => 'sometimes|required|in:1,2,15',
            ]);

            $leadSource = $this->leadSourceService->updateLeadSource($id, $request->all());

            return $this->responseService->updated(
                new LeadSourceResource($leadSource),
                'Lead source updated successfully.'
            );
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors());
        } catch (ModelNotFoundException $e) {
            return $this->responseService->notFound('Lead source not found for update.');
        } catch (Exception $e) {
            return $this->responseService->error('Failed to update lead source.', [$e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->leadSourceService->deleteLeadSource($id);
            return $this->responseService->deleted('Lead source deleted successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->responseService->notFound('Lead source not found for deletion.');
        } catch (Exception $e) {
            return $this->responseService->error('Failed to delete lead source.', [$e->getMessage()], 500);
        }
    }
}
