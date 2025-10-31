<?php

namespace App\Http\Controllers;

use App\Models\BrandAgencyRelationship;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

// Assuming Brand and Agency Models exist in App\Models
use App\Models\Brand;
use App\Models\Agency;

class BrandAgencyRelationshipController extends Controller
{
    /**
     * Display a listing of all Brand-Agency relationships (R).
     */
    public function index()
    {
        // Eager loading Brand and Agency data for efficiency
        $relationships = BrandAgencyRelationship::with(['brand', 'agency'])->get();

        return response()->json($relationships, Response::HTTP_OK);
    }

    /**
     * Store a newly created relationship (Attach Brand to Agency) (C).
     */
    public function store(Request $request)
    {
        // 1. Validation Rules
        $rules = [
            // Check if both IDs are present and exist in their respective tables
            'brand_id' => 'required|exists:brands,id', 
            'agency_id' => 'required|exists:agency,id', 
        ];

        // 2. Lumen Validation check
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        }

        $validatedData = $validator->validated();

        try {
            // Check for existing relationship to prevent duplicate entries
            $exists = BrandAgencyRelationship::where('brand_id', $validatedData['brand_id'])
                                              ->where('agency_id', $validatedData['agency_id'])
                                              ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'Relationship already exists between this Brand and Agency.'
                ], Response::HTTP_CONFLICT); // 409 Conflict
            }

            // Create the relationship record
            $relationship = BrandAgencyRelationship::create($validatedData);
            $relationship->load(['brand', 'agency']); // Load related data for response

            return response()->json([
                'message' => 'Brand and Agency successfully linked.',
                'relationship' => $relationship
            ], Response::HTTP_CREATED); // 201

        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Database Error: Could not create relationship.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR); // 500
        }
    }
    
    /**
     * Display the specified relationship by its ID (R).
     */
    public function show($id)
    {
        try {
            $relationship = BrandAgencyRelationship::with(['brand', 'agency'])->findOrFail($id);
            return response()->json($relationship, Response::HTTP_OK);
            
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Relationship not found.'], Response::HTTP_NOT_FOUND); // 404
        }
    }

    /**
     * Update operation is usually not needed for a simple pivot table like this.
     * We skip it here. If needed, it would be identical to store/destroy logic.
     */
    public function update(Request $request, $id)
    {
        return response()->json([
            'message' => 'Update is not supported for this pivot resource. Use POST for store/attach and DELETE for destroy/detach.'
        ], Response::HTTP_METHOD_NOT_ALLOWED); // 405
    }


    /**
     * Remove the specified relationship (Detach Brand from Agency) (D - Soft Delete).
     */
    public function destroy($id)
    {
        try {
            $relationship = BrandAgencyRelationship::findOrFail($id);
            $relationship->delete(); // Soft Delete

            return response()->json([
                'message' => 'Relationship successfully detached (soft deleted).'
            ], Response::HTTP_NO_CONTENT); // 204

        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Relationship not found.'], Response::HTTP_NOT_FOUND); // 404
        }
    }
}