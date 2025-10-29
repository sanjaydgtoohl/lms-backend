<?php

namespace App\Http\Controllers;

use App\Services\LocationService;
use App\Services\ResponseService;
use App\Http\Resources\CountryResource;
use App\Http\Resources\StateResource;
use App\Http\Resources\CityResource;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    protected $service, $response;

    public function __construct(LocationService $service, ResponseService $response)
    {
        $this->service = $service;
        $this->response = $response;
    }

    // Country methods
    public function getCountries()
    {
        $data = CountryResource::collection($this->service->countries());
        return $this->response->success($data, "Countries fetched successfully");
    }

    public function storeCountry(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255|unique:countries,name',
        ]);
        
        $country = $this->service->createCountry($request->all());
        return $this->response->created(new CountryResource($country), 'Country created successfully');
    }

    public function updateCountry(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255|unique:countries,name,' . $id,
        ]);
        
        $country = $this->service->updateCountry($id, $request->all());
        return $this->response->updated(new CountryResource($country), 'Country updated successfully');
    }

    public function deleteCountry($id)
    {
        $this->service->deleteCountry($id);
        return $this->response->deleted('Country deleted successfully');
    }

    // State methods
    public function getStates($country_id)
    {
        $data = StateResource::collection($this->service->states($country_id));
        return $this->response->success($data, "States fetched successfully");
    }

    public function storeState(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'country_id' => 'required|integer|exists:countries,id',
        ]);
        
        $state = $this->service->createState($request->all());
        return $this->response->created(new StateResource($state), 'State created successfully');
    }

    public function updateState(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'country_id' => 'sometimes|required|integer|exists:countries,id',
        ]);
        
        $state = $this->service->updateState($id, $request->all());
        return $this->response->updated(new StateResource($state), 'State updated successfully');
    }

    public function deleteState($id)
    {
        $this->service->deleteState($id);
        return $this->response->deleted('State deleted successfully');
    }

    // City methods
    public function getCities($country_id, $state_id)
    {
        $data = CityResource::collection($this->service->cities($country_id, $state_id));
        return $this->response->success($data, "Cities fetched successfully");
    }

    public function storeCity(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'country_id' => 'required|integer|exists:countries,id',
            'state_id' => 'required|integer|exists:states,id',
        ]);
        
        $city = $this->service->createCity($request->all());
        return $this->response->created(new CityResource($city), 'City created successfully');
    }

    public function updateCity(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'country_id' => 'sometimes|required|integer|exists:countries,id',
            'state_id' => 'sometimes|required|integer|exists:states,id',
        ]);
        
        $city = $this->service->updateCity($id, $request->all());
        return $this->response->updated(new CityResource($city), 'City updated successfully');
    }

    public function deleteCity($id)
    {
        $this->service->deleteCity($id);
        return $this->response->deleted('City deleted successfully');
    }
}
