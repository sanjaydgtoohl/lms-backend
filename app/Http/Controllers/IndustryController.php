<?php

namespace App\Http\Controllers;

use App\Services\IndustryService;  // <-- 1. Service ko import karein
use App\Traits\ApiResponse;         // <-- 2. Trait ko import karein
use Illuminate\Http\Request;
use Illuminate\Http\Response;       // HTTP Status codes ke liye (200, 404, etc.)
use Illuminate\Support\Facades\Validator;
use Exception; // Error handling ke liye

class IndustryController extends Controller
{
    // Dono ko use karein
    use ApiResponse;
    protected $industryService;

    /**
     * Constructor mein Service ko inject karein.
     * Controller ab seedha Repository se baat NAHI karega.
     */
    public function __construct(IndustryService $industryService)
    {
        $this->industryService = $industryService;
    }

    /**
     * READ: Saari industries laao (GET /api/industries)
     */
    public function index()
    {
        $industries = $this->industryService->getAllIndustries();
        return $this->successResponse($industries, 'Industries fetched successfully.');
    }

    /**
     * CREATE: Nayi industry banao (POST /api/industries)
     */
    public function store(Request $request)
    {
        // 1. Validate karein
        $validator = Validator::make($request->all(), [
            'industry_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            // Agar validation fail ho, toh Trait se error bhejein
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        }

        // 2. Service ko data dein create karne ke liye
        $industry = $this->industryService->createNewIndustry($request->all());
        
        // 3. Trait se success response bhejein (Status 201 Created)
        return $this->successResponse($industry, 'Industry created successfully.', Response::HTTP_CREATED);
    }

    /**
     * READ (Single): Ek industry laao (GET /api/industries/{id})
     */
    public function show($id)
    {
        try {
            // 1. Service se data maangein
            $industry = $this->industryService->getIndustry($id);
            
            // 2. Success response bhejein
            return $this->successResponse($industry, 'Industry fetched successfully.');

        } catch (Exception $e) {
            // 3. Agar service ko data nahi mila (findOrFail), toh error bhejein
            return $this->errorResponse('Industry not found.', Response::HTTP_NOT_FOUND); // 404
        }
    }

    /**
     * UPDATE: Industry update karo (PUT /api/industries/{id})
     */
    public function update(Request $request, $id)
    {
        // 1. Validate karein
        $validator = Validator::make($request->all(), [
            'industry_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        }

        try {
            // 2. Service ko data dein update karne ke liye
            $industry = $this->industryService->updateIndustry($id, $request->all());
            
            // 3. Success response bhejein
            return $this->successResponse($industry, 'Industry updated successfully.');

        } catch (Exception $e) {
            // 4. Agar data nahi mila, toh error bhejein
            return $this->errorResponse('Industry not found.', Response::HTTP_NOT_FOUND); // 404
        }
    }

    /**
     * DELETE: Industry delete karo (DELETE /api/industries/{id})
     */
    public function destroy($id)
    {
        try {
            // 1. Service ko bolo delete kare
            $this->industryService->deleteIndustry($id);
            
            // 2. Success (data mein null bhejo kyunki record delete ho gaya)
            return $this->successResponse(null, 'Industry deleted successfully.');

        } catch (Exception $e) {
            // 3. Agar data nahi mila, toh error bhejein
            return $this->errorResponse('Industry not found.', Response::HTTP_NOT_FOUND); // 404
        }
    }
}