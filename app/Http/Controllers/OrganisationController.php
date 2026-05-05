<?php

namespace App\Http\Controllers;

use App\Services\OrganisationService;
use App\Services\ResponseService;
use App\Http\Resources\OrganisationResource;
use App\Traits\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\Validation\ValidationException;

/**
 * Organisation Controller
 * -----------------------------------------
 * Handles API operations for organisations including listing,
 * pagination, and fetching organisation details by ID.
 * Utilizes service layer for business logic, API resources for
 * data transformation, and ResponseService for standardized responses.
 *
 * @package App\Http\Controllers
 * @author Achal Sharma
 * @version 1.0.0
 * @since 2026-05-05
 */

class OrganisationController extends Controller
{
    use ValidatesRequests;

    protected $organisationService;
    protected $responseService;

    public function __construct(OrganisationService $organisationService, ResponseService $responseService)
    {
        $this->organisationService = $organisationService;
        $this->responseService = $responseService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->get('per_page', 10);
            $perPage = max(1, min($perPage, 100));
            $searchTerm = $request->get('search', null);
            $organisations = $this->organisationService->getAllOrganisations($perPage, $searchTerm);

            return $this->responseService->paginated(
                OrganisationResource::collection($organisations),
                'Organisations fetched successfully.'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    /**
     * Get list of organisations with only id and name (e.g., /api/v1/organisations/list)
     */
    public function list(): JsonResponse
    {
        try {
            $organisations = $this->organisationService->getAllOrganisations(perPage: 10000);
            $data = $organisations->map(function ($organisation) {
                return [
                    'id' => $organisation->id,
                    'name' => $organisation->name,
                ];
            });

            return $this->responseService->success(
                $data,
                'Organisations list fetched successfully.'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $organisation = $this->organisationService->getOrganisationById($id);

            return $this->responseService->success(
                new OrganisationResource($organisation),
                'Organisation fetched successfully.'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }
}
