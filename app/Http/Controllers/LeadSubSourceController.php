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
    /**
     * The LeadSubSourceService instance.
     *
     * @var LeadSubSourceService
     */
    protected $leadSubSourceService;

    /**
     * The ResponseService instance.
     *
     * @var ResponseService
     */
    protected $responseService;

    /**
     * Create a new LeadSubSourceController instance.
     *
     * @param LeadSubSourceService $leadSubSourceService
     * @param ResponseService $responseService
     */
    public function __construct(
        LeadSubSourceService $leadSubSourceService, 
        ResponseService $responseService
    ) {
        $this->leadSubSourceService = $leadSubSourceService;
        $this->responseService = $responseService;
    }

    /**
     * Display a listing of lead sub-sources with optional filtering.
     * 
     * GET /lead-sub-sources
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @queryParam lead_source_id int ID of the parent lead source to filter by. Example: 1
     * @queryParam per_page int Number of items per page. Example: 5
     * @queryParam search string Search term to filter results. Example: "marketing"
     */
    public function index(Request $request)
    {
        try {
            // Validate the lead_source_id and search filters
            $this->validate($request, [
                'lead_source_id' => 'nullable|integer|exists:lead_source,id',
                'search'         => 'nullable|string|max:100' // Add validation for search parameter
            ]);
            
            // 1. Get pagination parameter
            $perPage = (int) $request->get('per_page', 10);

            // 2. Collect all filters in a single array for consistency
            $filters = [
                'lead_source_id' => $request->input('lead_source_id'),
                'search'         => $request->input('search', null) // Include search parameter in filters
            ];
            
            // 3. Pass only filters and perPage to service
            // Repository will handle the search filter internally
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

    /**
     * Get list of lead sub-sources with only id and name (e.g., /api/v1/lead-sub-sources/list)
     */
    public function list()
    {
        try {
            $leadSubSources = $this->leadSubSourceService->getAllLeadSubSources(filters: [], perPage: 10000);
            $data = $leadSubSources->items() ? collect($leadSubSources->items())->map(function ($subSource) {
                return [
                    'id' => $subSource->id,
                    'name' => $subSource->name,
                ];
            }) : collect([]);
            return $this->responseService->success($data, 'Lead sub-sources list retrieved');
        } catch (Exception $e) {
            return $this->responseService->error('Failed to fetch lead sub-sources list.', [$e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created lead sub-source.
     * 
     * POST /lead-sub-sources
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'lead_source_id' => 'required|integer|exists:lead_source,id',
                'name' => 'required|string|max:255|unique:lead_sub_source,name',
                'description' => 'nullable|string',
                'status' => 'nullable|in:1,2,15',
            ], [
                'name.unique' => 'This sub source name already exists.'
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

    /**
     * Display the specified lead sub-source.
     * 
     * GET /lead-sub-sources/{id}
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Update the specified lead sub-source.
     * 
     * PUT /lead-sub-sources/{id}
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $leadSubSource = $this->leadSubSourceService->getLeadSubSource($id);
            $leadSourceId = $request->input('lead_source_id', $leadSubSource->lead_source_id);
            
            $this->validate($request, [
                'lead_source_id' => 'sometimes|required|integer|exists:lead_source,id',
                'name' => 'sometimes|required|string|max:255|unique:lead_sub_source,name,' . $id,
                'description' => 'nullable|string',
                'status' => 'sometimes|required|in:1,2,15',
            ], [
                'name.unique' => 'This sub source name already exists. Each sub source must have a unique name across all lead sources.'
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

    /**
     * Remove the specified lead sub-source.
     * 
     * DELETE /lead-sub-sources/{id}
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Get lead sub-sources by source ID
     * 
     * GET /lead-sub-sources/by-source/{sourceId}
     * 
     * @param int $sourceId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBySourceId($sourceId)
    {
        try {
            $leadSubSources = $this->leadSubSourceService->getLeadSubSourcesBySourceId($sourceId);

            $response = $this->responseService->success(
                LeadSubSourceResource::collection($leadSubSources),
                'Lead sub-sources fetched successfully by source ID.'
            );

            // Add total to meta
            $responseData = $response->getData(true);
            $responseData['meta']['total'] = $leadSubSources->count();
            
            return response()->json($responseData, 200);
        } catch (Exception $e) {
            return $this->responseService->error('Failed to fetch lead sub-sources.', [$e->getMessage()], 500);
        }
    }
}
