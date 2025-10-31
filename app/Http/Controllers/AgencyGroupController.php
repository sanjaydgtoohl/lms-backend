<?php

namespace App\Http\Controllers;

use App\Models\AgencyGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator; // <-- Essential for Lumen validation

class AgencyGroupController extends Controller
{
    public function index()
    {
        $groups = AgencyGroup::where('status', '1')->get(['id', 'name', 'slug', 'status']);
        return response()->json($groups, Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        // 1. Use Validator::make() for Lumen validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:agency_groups,name',
            'status' => 'nullable|in:1,2,15',
        ]);

        // 2. Handle validation failure
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Use the validated data
        $validatedData = $validator->validated();

        try {
            $validatedData['slug'] = Str::slug($validatedData['name']);
            $validatedData['status'] = $validatedData['status'] ?? '1';

            $agencyGroup = AgencyGroup::create($validatedData);

            return response()->json([
                'message' => 'Agency Group created successfully.',
                'group' => $agencyGroup
            ], Response::HTTP_CREATED);

        } catch (QueryException $e) {
            // Catches database specific errors (e.g., if columns were missing)
            return response()->json([
                'message' => 'Database Error: Could not create Agency Group.',
                'error_code' => $e->getCode()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
             // Catches unexpected errors
            return response()->json([
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        try {
            $group = AgencyGroup::findOrFail($id);
            return response()->json($group, Response::HTTP_OK);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Agency Group not found.'], Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $group = AgencyGroup::findOrFail($id);

            // 1. Use Validator::make() for Lumen validation
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255|unique:agency_groups,name,' . $id,
                'status' => 'nullable|in:1,2,15',
            ]);

            // 2. Handle validation failure
            if ($validator->fails()) {
                return response()->json(['message' => 'Validation Failed', 'errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validatedData = $validator->validated();

            if (isset($validatedData['name'])) {
                $validatedData['slug'] = Str::slug($validatedData['name']);
            }
            
            // Check if any data was actually sent for update
            if (empty($validatedData)) {
                 return response()->json(['message' => 'No data provided for update.'], Response::HTTP_BAD_REQUEST);
            }

            $group->update($validatedData);

            return response()->json([
                'message' => 'Agency Group updated successfully.',
                'group' => $group
            ], Response::HTTP_OK);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Agency Group not found.'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
             return response()->json([
                'message' => 'An unexpected error occurred during update.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            $group = AgencyGroup::findOrFail($id);
            $group->delete(); // Soft Delete

            return response()->json([
                'message' => 'Agency Group deleted successfully (soft deleted).'
            ], Response::HTTP_NO_CONTENT);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Agency Group not found.'], Response::HTTP_NOT_FOUND);
        }
    }
}