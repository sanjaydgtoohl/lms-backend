<?php
namespace App\Http\Controllers;

use App\Services\AgencyTypeService;
use App\Services\ResponseService;
use App\Http\Resources\AgencyTypeResource;

class AgencyTypeController extends Controller
{
    protected $service, $response;
    public function __construct(AgencyTypeService $service, ResponseService $response) {
        $this->service = $service;
        $this->response = $response;
    }

    public function index() {
        $data = $this->service->getAll();
        return $this->response->success(AgencyTypeResource::collection($data), 'Agency types fetched successfully.');
    }
}
