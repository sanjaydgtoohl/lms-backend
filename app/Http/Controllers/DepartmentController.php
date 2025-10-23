<?php

namespace App\Http\Controllers;

use App\Services\DepartmentService;
use App\Services\ResponseService;
use App\Http\Resources\DepartmentResource;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use DomainException;
use Exception;

class DepartmentController extends Controller
{
    protected $departmentService;
    protected $responseService;

    public function __construct(DepartmentService $departmentService, ResponseService $responseService)
    {
        $this->departmentService = $departmentService;
        $this->responseService = $responseService;
    }

    /**
     * Get all departments
     */
    public function index()
    {
        try {
            $departments = $this->departmentService->getAllDepartments();
            return $this->responseService->paginated($departments, 'Departments fetched successfully.');
        } catch (DomainException $e) {
            return $this->responseService->error($e->getMessage(), null, 500, 'DOMAIN_ERROR');
        } catch (QueryException $e) {
            return $this->responseService->error('Database error: ' . $e->getMessage(), null, 500, 'DB_ERROR');
        } catch (Exception $e) {
            return $this->responseService->serverError('Unexpected error while fetching departments.', $e->getMessage());
        }
    }

    /**
     * Create a new department
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required|string|max:255|unique:departments',
                'description' => 'nullable|string',
                'status' => 'nullable|in:1,2,15',
            ]);

            $department = $this->departmentService->createNewDepartment($request->all());
            return $this->responseService->created(new DepartmentResource($department), 'Department created successfully.');
        } catch (DomainException $e) {
            return $this->responseService->error($e->getMessage(), null, 400, 'DOMAIN_ERROR');
        } catch (QueryException $e) {
            return $this->responseService->error('Database error: ' . $e->getMessage(), null, 500, 'DB_ERROR');
        } catch (Exception $e) {
            return $this->responseService->serverError('Unexpected error while creating department.', $e->getMessage());
        }
    }

    /**
     * Get a single department
     */
    public function show($id)
    {
        try {
            $department = $this->departmentService->getDepartment($id);
            return $this->responseService->success(new DepartmentResource($department), 'Department fetched successfully.');
        } catch (DomainException $e) {
            return $this->responseService->notFound($e->getMessage());
        } catch (QueryException $e) {
            return $this->responseService->error('Database error: ' . $e->getMessage(), null, 500, 'DB_ERROR');
        } catch (Exception $e) {
            return $this->responseService->serverError('Unexpected error while fetching department.', $e->getMessage());
        }
    }

    /**
     * Update an existing department
     */
    public function update(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'name' => 'sometimes|required|string|max:255|unique:departments,name,' . $id,
                'description' => 'nullable|string',
                'status' => 'sometimes|required|in:1,2,15',
            ]);

            $department = $this->departmentService->updateDepartment($id, $request->all());
            return $this->responseService->updated(new DepartmentResource($department), 'Department updated successfully.');
        } catch (DomainException $e) {
            return $this->responseService->notFound($e->getMessage());
        } catch (QueryException $e) {
            return $this->responseService->error('Database error: ' . $e->getMessage(), null, 500, 'DB_ERROR');
        } catch (Exception $e) {
            return $this->responseService->serverError('Unexpected error while updating department.', $e->getMessage());
        }
    }

    /**
     * Delete a department
     */
    public function destroy($id)
    {
        try {
            $this->departmentService->deleteDepartment($id);
            return $this->responseService->deleted('Department deleted successfully.');
        } catch (DomainException $e) {
            return $this->responseService->notFound($e->getMessage());
        } catch (QueryException $e) {
            return $this->responseService->error('Database error: ' . $e->getMessage(), null, 500, 'DB_ERROR');
        } catch (Exception $e) {
            return $this->responseService->serverError('Unexpected error while deleting department.', $e->getMessage());
        }
    }
}
