<?php

namespace App\Http\Controllers;

use App\Services\DesignationService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Exception;
use DomainException;
use Illuminate\Database\QueryException;
use App\Http\Resources\DesignationResource;

class DesignationController extends Controller
{
    
    protected $designationService;
    protected $responseService;
    
    public function __construct(DesignationService $designationService, ResponseService $responseService)
    {
        $this->designationService = $designationService;
        $this->responseService = $responseService;
    }

    public function index()
    {
        $designations = $this->designationService->getAllDesignations();
        return $this->responseService->paginated($designations, 'Designations fetched successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->responseService->validationError($validator->errors()->toArray());
        }

        try {
            $designation = $this->designationService->createNewDesignation($request->all());
            return $this->responseService->created(new DesignationResource($designation), 'Designation created successfully');
        } catch (DomainException $e) {
            return $this->responseService->error($e->getMessage(), null, 409, 'SLUG_CONFLICT');
        } catch (QueryException $e) {
            // handle DB errors (unique constraint etc.)
            return $this->responseService->error('Database error: ' . $e->getMessage(), null, 409, 'DB_ERROR');
        } catch (Exception $e) {
            return $this->responseService->serverError('An unexpected error occurred', $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $designation = $this->designationService->getDesignation($id);
            return $this->responseService->success(new DesignationResource($designation), 'Designation fetched successfully');
        } catch (Exception $e) {
            return $this->responseService->notFound('Designation not found');
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->responseService->validationError($validator->errors()->toArray());
        }

        try {
            $designation = $this->designationService->updateDesignation($id, $request->all());
            return $this->responseService->updated(new DesignationResource($designation), 'Designation updated successfully');
        } catch (DomainException $e) {
            return $this->responseService->error($e->getMessage(), null, 409, 'SLUG_CONFLICT');
        } catch (QueryException $e) {
            return $this->responseService->error('Database error: ' . $e->getMessage(), null, 409, 'DB_ERROR');
        } catch (Exception $e) {
            return $this->responseService->notFound('Designation not found');
        }
    }

    // destroy() method ko change nahi kiya gaya hai
    public function destroy($id)
    {
        try {
            $this->designationService->deleteDesignation($id);
            return $this->responseService->deleted('Designation deleted successfully.');
        } catch (Exception $e) {
            return $this->responseService->notFound('Designation not found');
        }
    }
}   