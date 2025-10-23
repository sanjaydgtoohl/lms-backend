<?php

namespace App\Http\Controllers;

use App\Services\IndustryService;
use App\Services\ResponseService;
use App\Http\Resources\IndustryResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Exception;
use DomainException;
use Illuminate\Database\QueryException;

class IndustryController extends Controller
{
    protected $industryService;
    protected $responseService;

    public function __construct(IndustryService $industryService, ResponseService $responseService)
    {
        $this->industryService = $industryService;
        $this->responseService = $responseService;
    }

    public function index()
    {
        try {
            $industries = $this->industryService->getAllIndustries();

            if ($industries->isEmpty()) {
                return $this->responseService->success([], 'No industries found.');
            }

            return $this->responseService->paginated($industries, 'Industries fetched successfully.');
        } catch (QueryException $e) {
            return $this->responseService->error('Database error: ' . $e->getMessage(), null, 500, 'DB_ERROR');
        } catch (DomainException $e) {
            return $this->responseService->error($e->getMessage(), null, 400, 'DOMAIN_ERROR');
        } catch (Exception $e) {
            return $this->responseService->serverError('An unexpected error occurred while fetching industries.', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
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
                return $this->responseService->validationError($validator->errors()->toArray());
            }

            if (empty($data['slug']) && !empty($data['name'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            $industry = $this->industryService->createNewIndustry($data);
            return $this->responseService->created(new IndustryResource($industry), 'Industry created successfully.');
        } catch (QueryException $e) {
            return $this->responseService->error('Database error: ' . $e->getMessage(), null, 409, 'DB_ERROR');
        } catch (DomainException $e) {
            return $this->responseService->error($e->getMessage(), null, 409, 'DOMAIN_ERROR');
        } catch (Exception $e) {
            return $this->responseService->serverError('An unexpected error occurred while creating industry.', $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $industry = $this->industryService->getIndustry($id);
            return $this->responseService->success(new IndustryResource($industry), 'Industry fetched successfully.');
        } catch (QueryException $e) {
            return $this->responseService->error('Database error: ' . $e->getMessage(), null, 500, 'DB_ERROR');
        } catch (Exception $e) {
            return $this->responseService->notFound('Industry not found');
        }
    }

    public function update(Request $request, $id)
    {
        try {
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
                return $this->responseService->validationError($validator->errors()->toArray());
            }

            if (empty($data['slug']) && !empty($data['name'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            $industry = $this->industryService->updateIndustry($id, $data);
            return $this->responseService->updated(new IndustryResource($industry), 'Industry updated successfully.');
        } catch (QueryException $e) {
            return $this->responseService->error('Database error: ' . $e->getMessage(), null, 409, 'DB_ERROR');
        } catch (DomainException $e) {
            return $this->responseService->error($e->getMessage(), null, 409, 'DOMAIN_ERROR');
        } catch (Exception $e) {
            return $this->responseService->notFound('Industry not found');
        }
    }

    public function destroy($id)
    {
        try {
            $this->industryService->deleteIndustry($id);
            return $this->responseService->deleted('Industry deleted successfully.');
        } catch (QueryException $e) {
            return $this->responseService->error('Database error: ' . $e->getMessage(), null, 500, 'DB_ERROR');
        } catch (Exception $e) {
            return $this->responseService->notFound('Industry not found');
        }
    }
}