<?php

/**
 * MediaType Controller
 * -----------------------------------------
 * Handles HTTP requests for media type management, providing CRUD API endpoints.
 *
 * @package App\Http\Controllers
 * @author Achal Sharma
 * @version 1.0.0
 * @since 2026-04-08
 */

namespace App\Http\Controllers;

use App\Http\Resources\MediaTypeResource;
use App\Services\MediaTypeService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class MediaTypeController extends Controller
{
    protected ResponseService $responseService;
    protected MediaTypeService $mediaTypeService;

    public function __construct(MediaTypeService $mediaTypeService, ResponseService $responseService)
    {
        $this->mediaTypeService = $mediaTypeService;
        $this->responseService = $responseService;
    }

    public function index(Request $request)
    {
        try {
            $this->validate($request, [
                'per_page' => 'nullable|integer|min:1',
            ]);

            $perPage = (int) $request->get('per_page', 15);
            $mediaTypes = $this->mediaTypeService->getAll($perPage);

            return $this->responseService->paginated(
                MediaTypeResource::collection($mediaTypes),
                'Media types retrieved successfully'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:media_types,name,NULL,id,deleted_at,NULL',
                //'status' => 'nullable|boolean',
            ]);

            $validatedData = $validator->validate();
            $mediaType = $this->mediaTypeService->create($validatedData);

            return $this->responseService->created(
                new MediaTypeResource($mediaType),
                'Media type created successfully'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function show($id)
    {
        try {
            $mediaType = $this->mediaTypeService->findById((int) $id);

            return $this->responseService->success(
                new MediaTypeResource($mediaType),
                'Media type retrieved successfully'
            );
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }

    public function destroy($id)
    {
        try {
            $this->mediaTypeService->delete((int) $id);

            return $this->responseService->deleted('Media type deleted successfully');
        } catch (Throwable $e) {
            return $this->responseService->handleException($e);
        }
    }
}
