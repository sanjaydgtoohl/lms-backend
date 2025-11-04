<?php

namespace App\Http\Controllers;

use App\Services\LeadSubSourceService;
use App\Services\ResponseService;
use App\Http\Resources\LeadSubSourceResource;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Exception;

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
     * Filter: /api/v1/lead-sub-sources?lead_source_id=1&per_page=5
     */
    public function index(Request $request)
    {
        try {
            // Validate the lead_source_id AND search filters
            $this->validate($request, [
                'lead_source_id' => 'nullable|integer|exists:lead_source,id',
                'search'         => 'nullable|string|max:100' // Search ke liye validation add karein
            ]);
            
            // 1. Get pagination parameter
            $perPage = (int) $request->get('per_page', 10);

            // 2. SAHI TAREEKA: Saare filters ko ek hi array mein daalein
            $filters = [
                'lead_source_id' => $request->input('lead_source_id'),
                'search'         => $request->input('search', null) // 'search' ko bhi isi array mein daalein
            ];
            
            // 3. Service ko sirf $filters aur $perPage bheinjein
            // Repository $filters['search'] ko khud check kar lega
            $leadSubSources = $this->leadSubSourceService->getAllLeadSubSources($filters, $perPage);

            // Handle empty result set gracefully
            if ($leadSubSources->isEmpty() && !($leadSubSources instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $leadSubSources->total() > 0)) {
                return $this->responseService->success([], 'No lead sub-sources found.');
            }

            return $this->responseService->paginated(
                LeadSubSourceResource::collection($leadSubSources),
                'Lead sub-sources fetched successfully.'
            );
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors());
        } catch (Exception $e) {
            return $this->responseService->error('Failed to fetch lead sub-sources.', [$e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'lead_source_id' => 'required|integer|exists:lead_source,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'nullable|in:1,2,15',
            ]);

            $leadSubSource = $this->leadSubSourceService->createNewLeadSubSource($request->all());

            return $this->responseService->created(
                new LeadSubSourceResource($leadSubSource),
                'Lead sub-source created successfully.'
            );
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors());
        } catch (Exception $e) {
            return $this->responseService->error('Failed to create lead sub-source.', [$e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $leadSubSource = $this->leadSubSourceService->getLeadSubSource($id);

            return $this->responseService->success(
                new LeadSubSourceResource($leadSubSource),
                'Lead sub-source fetched successfully.'
            );
        } catch (ModelNotFoundException $e) {
            return $this->responseService->notFound('Lead sub-source not found.');
        } catch (Exception $e) {
            return $this->responseService->error('Failed to fetch lead sub-source.', [$e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
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
        } catch (ValidationException $e) {
            return $this->responseService->validationError($e->errors());
        } catch (ModelNotFoundException $e) {
            return $this->responseService->notFound('Lead sub-source not found for update.');
        } catch (Exception $e) {
            return $this->responseService->error('Failed to update lead sub-source.', [$e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->leadSubSourceService->deleteLeadSubSource($id);
            return $this->responseService->deleted('Lead sub-source deleted successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->responseService->notFound('Lead sub-source not found for deletion.');
        } catch (Exception $e) {
            return $this->responseService->error('Failed to delete lead sub-source.', [$e->getMessage()], 500);
        }
    }
}
