<?php

namespace App\Http\Controllers;

use App\Services\IndustryService;
use App\Services\ResponseService;   // <-- NAYI SERVICE IMPORT KI
use App\Http\Resources\IndustryResource; // Resource file (pehle se hai)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class IndustryController extends Controller
{
    
    protected $industryService;
    protected $responseService; // <-- NAYI SERVICE KE LIYE VARIABLE

    /**
     * Dono services ko constructor mein inject karein
     */
    public function __construct(
        IndustryService $industryService, 
        ResponseService $responseService // <-- INJECT KIYA GAYA
    ) {
        $this->industryService = $industryService;
        $this->responseService = $responseService; // <-- SET KIYA GAYA
    }

    /**
     * READ (Paginated)
     */
    public function index()
    {
        $industries = $this->industryService->getAllIndustries();
        
        // Naya paginated response method
        return $this->responseService->paginated($industries, 'Industries fetched successfully.');
    }

    /**
     * CREATE
     */
    public function store(Request $request)
    {
        // Accept either 'name' or legacy 'industry_name' param
        $data = $request->all();
        if (isset($data['industry_name']) && empty($data['name'])) {
            $data['name'] = $data['industry_name'];
        }

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'slug' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:1,2,15',
        ]);

        if ($validator->fails()) {
            // Throwing ValidationException so exception handler returns consistent response
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        // Generate slug from name if not provided
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $industry = $this->industryService->createNewIndustry($data);

        // Naya 'created' response method
        return $this->responseService->created(
            new IndustryResource($industry), 
            'Industry created successfully.'
        );
    }

    /**
     * READ (Single)
     * Koi try-catch nahi! 
     * Agar 'findOrFail' fail hota hai, toh 'Handler.php' 
     * 'ModelNotFoundException' pakdega aur 404 response bhej dega.
     */
    public function show($id)
    {
        $industry = $this->industryService->getIndustry($id);
        
        // Naya 'success' response method
        return $this->responseService->success(
            new IndustryResource($industry), 
            'Industry fetched successfully.'
        );
    }

    /**
     * UPDATE
     * Koi try-catch nahi!
     */
    public function update(Request $request, $id)
    {
        // Map legacy field if present and validate against migration columns
        $data = $request->all();
        if (isset($data['industry_name']) && empty($data['name'])) {
            $data['name'] = $data['industry_name'];
        }

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'slug' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:1,2,15',
        ]);

        if ($validator->fails()) {
            throw new \Illuminate\Validation\ValidationException($validator);
        }

        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $industry = $this->industryService->updateIndustry($id, $data);

        // Naya 'updated' response method
        return $this->responseService->updated(
            new IndustryResource($industry), 
            'Industry updated successfully.'
        );
    }

    /**
     * DELETE
     * Koi try-catch nahi!
     */
    public function destroy($id)
    {
        $this->industryService->deleteIndustry($id);
        
        // Naya 'deleted' response method
        return $this->responseService->deleted('Industry deleted successfully.');
    }
}

