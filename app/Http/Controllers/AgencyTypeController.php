<?php

namespace App\Http\Controllers;

use App\Models\AgencyType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response;

class AgencyTypeController extends Controller
{
    public function index()
    {
        $types = AgencyType::where('status', '1')->get(['id', 'name', 'slug', 'status']);
        return response()->json($types, Response::HTTP_OK);
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:agency_type,name',
                'status' => 'nullable|in:1,2,15',
            ]);

            $validatedData['slug'] = Str::slug($validatedData['name']);
            $validatedData['status'] = $validatedData['status'] ?? '1';

            $agencyType = AgencyType::create($validatedData);

            return response()->json([
                'message' => 'Agency Type created successfully.',
                'type' => $agencyType
            ], Response::HTTP_CREATED);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation Failed',
                'errors' => $e->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Database Error: Could not create Agency Type.',
                'error_code' => $e->getCode()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        try {
            $type = AgencyType::findOrFail($id);
            return response()->json($type, Response::HTTP_OK);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Agency Type not found.'], Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $type = AgencyType::findOrFail($id);

            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255|unique:agency_type,name,' . $id,
                'status' => 'nullable|in:1,2,15',
            ]);

            if (isset($validatedData['name'])) {
                $validatedData['slug'] = Str::slug($validatedData['name']);
            }

            $type->update($validatedData);

            return response()->json([
                'message' => 'Agency Type updated successfully.',
                'type' => $type
            ], Response::HTTP_OK);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Agency Type not found.'], Response::HTTP_NOT_FOUND);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Validation Failed', 'errors' => $e->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function destroy($id)
    {
        try {
            $type = AgencyType::findOrFail($id);
            $type->delete(); // Soft Delete

            return response()->json([
                'message' => 'Agency Type deleted successfully (soft deleted).'
            ], Response::HTTP_NO_CONTENT);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Agency Type not found.'], Response::HTTP_NOT_FOUND);
        }
    }
}