<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator; // <-- IMPORTANT: Use the Validator facade for Lumen

class AgencyController extends Controller
{
    public function index()
    {
        $agencies = Agency::with(['agencyGroup', 'agencyType', 'brand'])
                            ->where('status', '1')
                            ->get();

        return response()->json($agencies, Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        // 1. Define Validation Rules
        $rules = [
            'name' => 'required|string|max:255|unique:agency,name',
            'agency_group_id' => 'nullable|exists:agency_groups,id', 
            'agency_type_id' => 'required|exists:agency_type,id', 
            'brand_id' => 'nullable|exists:brands,id', 
            'status' => 'nullable|in:1,2,15',
        ];

        // 2. Create and Check the Validator
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validatedData = $validator->validated();

        try {
            $validatedData['slug'] = Str::slug($validatedData['name']);
            $validatedData['status'] = $validatedData['status'] ?? '1';

            $agency = Agency::create($validatedData);
            $agency->load(['agencyGroup', 'agencyType', 'brand']); 

            return response()->json([
                'message' => 'Agency created successfully.',
                'agency' => $agency
            ], Response::HTTP_CREATED);

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Database Error: Could not create Agency due to a data constraint.',
                'error_code' => $e->getCode()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        try {
            $agency = Agency::with(['agencyGroup', 'agencyType', 'brand'])->findOrFail($id);
            return response()->json($agency, Response::HTTP_OK);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Agency not found.'], Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $agency = Agency::findOrFail($id);
            
            // 1. Define Validation Rules for update
            $rules = [
                'name' => 'sometimes|required|string|max:255|unique:agency,name,' . $id,
                'agency_group_id' => 'nullable|exists:agency_groups,id', 
                'agency_type_id' => 'sometimes|required|exists:agency_type,id', 
                'brand_id' => 'nullable|exists:brands,id', 
                'status' => 'nullable|in:1,2,15',
            ];

            // 2. Create and Check the Validator
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json(['message' => 'Validation Failed', 'errors' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $validatedData = $validator->validated();
            
            if (isset($validatedData['name'])) {
                $validatedData['slug'] = Str::slug($validatedData['name']);
            }
            
            if (empty($validatedData)) {
                 return response()->json(['message' => 'No data provided for update.'], Response::HTTP_BAD_REQUEST);
            }

            $agency->update($validatedData);
            $agency->load(['agencyGroup', 'agencyType', 'brand']);

            return response()->json([
                'message' => 'Agency updated successfully.',
                'agency' => $agency
            ], Response::HTTP_OK);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Agency not found.'], Response::HTTP_NOT_FOUND);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An unexpected error occurred while updating the agency.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            $agency = Agency::findOrFail($id);
            $agency->delete(); // Soft Delete

            return response()->json([
                'message' => 'Agency deleted successfully (soft deleted).'
            ], Response::HTTP_NO_CONTENT);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Agency not found.'], Response::HTTP_NOT_FOUND);
        }
    }
}