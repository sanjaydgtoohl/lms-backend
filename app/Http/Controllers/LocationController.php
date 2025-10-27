<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LocationController extends Controller
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('CSC_API_KEY');
        $this->baseUrl = "https://api.countrystatecity.in/v1";

        if (empty($this->apiKey)) {
            Log::error('CountryStateCity API Key (CSC_API_KEY) .env file mein set nahi hai.');
        }
    }

    private function makeApiCall($endpoint)
    {
        // ... (Yeh function same rahega, ismein badlaav nahi hai) ...
        if (empty($this->apiKey)) {
            Log::error('API configuration error.');
            return null; 
        }
        try {
            $response = Http::withHeaders([
                'X-CSCAPI-KEY' => $this->apiKey
            ])->get($this->baseUrl . $endpoint);
            if ($response->failed()) {
                Log::error('CSC API Call Failed: ' . $response->body());
                return null;
            }
            return $response->json(); 
        } catch (\Exception $e) {
            Log::error('CSC API Request Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * API Endpoint: Sabhi desh (countries) laane ke liye
     */
    public function getCountries()
    {
        try {
            // 1. Pehle DB se check karein (SoftDeletes wale bhi aa sakte hain, isliye withTrashed() use karein)
            $countries = Country::orderBy('name', 'asc')->get();

            if ($countries->isEmpty()) {
                $apiCountries = $this->makeApiCall('/countries');

                if ($apiCountries) {
                    DB::transaction(function () use ($apiCountries) {
                        foreach ($apiCountries as $country) {
                            // updateOrCreate istemal karein taaki data dobara na daale
                            Country::updateOrCreate(
                                ['id' => $country['id']], // Check karein agar ID pehle se hai
                                [
                                    'name' => $country['name'],
                                    'slug' => Str::slug($country['name']), // <-- SLUG GENERATE KAREIN
                                    'iso2' => $country['iso2'],            // <-- ISO2 SAVE KAREIN
                                    // 'status' column default '1' le lega
                                ]
                            );
                        }
                    });
                    $countries = Country::orderBy('name', 'asc')->get();
                } else {
                    return response()->json(['error' => 'Failed to fetch data from external API.'], 502);
                }
            }
            
            return response()->json($countries);

        } catch (\Exception $e) {
            Log::error('getCountries Error: ' . $e->getMessage());
            return response()->json(['error' => 'An internal server error occurred.'], 500);
        }
    }

    /**
     * API Endpoint: Ek desh ke sabhi rajya (states) laane ke liye
     * AB YEH ID SE CHALEGA, ISO2 SE NAHI
     */
    public function getStates(Request $request, $country_id) // <-- ID ka istemal karein
    {
        try {
            $country = Country::findOrFail($country_id);

            $states = $country->states()->orderBy('name', 'asc')->get();

            if ($states->isEmpty()) {
                // Check karein ki desh ka iso2 code hai ya nahi
                if(empty($country->iso2)) {
                    return response()->json(['error' => 'Country ISO code is missing, cannot fetch states.'], 404);
                }

                $apiStates = $this->makeApiCall("/countries/{$country->iso2}/states");

                if ($apiStates) {
                    DB::transaction(function () use ($apiStates, $country) {
                        foreach ($apiStates as $state) {
                            State::updateOrCreate(
                                ['id' => $state['id']],
                                [
                                    'name' => $state['name'],
                                    'slug' => Str::slug($state['name']), // <-- SLUG GENERATE KAREIN
                                    'iso2' => $state['iso2'],            // <-- ISO2 SAVE KAREIN
                                    'country_id' => $country->id,
                                ]
                            );
                        }
                    });
                    $states = $country->states()->orderBy('name', 'asc')->get();
                } else {
                    return response()->json(['error' => 'Failed to fetch data from external API.'], 502);
                }
            }
            return response()->json($states);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Country not found with ID: ' . $country_id], 404);
        } catch (\Exception $e) {
            Log::error('getStates Error: ' . $e->getMessage());
            return response()->json(['error' => 'An internal server error occurred.'], 500);
        }
    }

    /**
     * API Endpoint: Ek rajya ke sabhi sheher (cities) laane ke liye
     * AB YEH STATE ID SE CHALEGA
     */
    public function getCities(Request $request, $state_id) // <-- ID ka istemal karein
    {
        try {
            $state = State::with('country')->findOrFail($state_id); // Country ko bhi load karein
            $country = $state->country;

            $cities = $state->cities()->orderBy('name', 'asc')->get();

            if ($cities->isEmpty()) {
                if(empty($country->iso2) || empty($state->iso2)) {
                    return response()->json(['error' => 'Country or State ISO code is missing, cannot fetch cities.'], 404);
                }

                $apiCities = $this->makeApiCall("/countries/{$country->iso2}/states/{$state->iso2}/cities");

                if ($apiCities) {
                    DB::transaction(function () use ($apiCities, $state, $country) {
                        foreach ($apiCities as $city) {
                            City::updateOrCreate(
                                ['id' => $city['id']],
                                [
                                    'name' => $city['name'],
                                    'state_id' => $state->id,
                                    'country_id' => $country->id,
                                ]
                            );
                        }
                    });
                    $cities = $state->cities()->orderBy('name', 'asc')->get();
                } else {
                    return response()->json(['error' => 'Failed to fetch data from external API.'], 502);
                }
            }
            
            return response()->json($cities);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => "State not found with ID: {$state_id}"], 404);
        } catch (\Exception $e) {
            Log::error('getCities Error: ' . $e->getMessage());
            return response()->json(['error' => 'An internal server error occurred.'], 500);
        }
    }
}