<?php
namespace App\Services;

use App\Contracts\Repositories\AgencyRepositoryInterface;
use App\Models\BrandAgencyRelationship;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Services\ResponseService;

class AgencyService
{
    protected $repo;

     /**
     * @var ResponseService
     */
    protected ResponseService $responseService;

    
    public function __construct(ResponseService $responseService,AgencyRepositoryInterface $repo) { 
        $this->repo = $repo;
        $this->responseService = $responseService; 
    }
    
    public function getAll() { 
        return $this->repo->getAllAgency(); 
    }
    
    public function getById($id) {
        return $this->repo->getAgencyById($id);
    }
    
    public function delete($id) { 
        return $this->repo->deleteAgency($id); 
    }

    public function create(array $data) {
        try {
            DB::beginTransaction();
    
            $agency = null;
            $is_parent = null;
    
            if (!empty($data['name']) && !empty($data['type'])) {
                $count = count($data['name']);
    
                for ($i = 0; $i < $count; $i++) {
                    if (!empty($data['name'][$i]) && !empty($data['type'][$i])) {
    
                        $agencyData = [
                            'name'              => $data['name'][$i],
                            'agency_type'    => $data['type'][$i],
                            'slug'              => Str::slug($data['name'][$i]),
                            'is_parent'         => $is_parent,
                            'status'            => 1,
                        ];

                        $clients = !empty($data['client']) && !empty($data['client'][$i]) ? $data['client'][$i] : [];
    
                        $agency = $this->repo->createAgency($agencyData);
    
                        // Set the first created agency as parent
                        if ($i == 0) {
                            $is_parent = $agency->id;
                        }

                        if(!empty($clients) && is_array($clients)){
                            foreach($clients as $client){
                               $agencyClients = new BrandAgencyRelationship(); 
                               $agencyClients->agency_id = $agency->id;
                               $agencyClients->brand_id = $client;
                               $agencyClients->save();
                            }
                        }
                    }
                }
            }
    
            DB::commit();
    
            return $agency;
        } catch (ValidationException $e) {
            DB::rollBack();
            return $this->responseService->validationError($e->errors(), 'Registration validation failed');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseService->serverError('Registration failed: ' . $e->getMessage());
        }
    
    }
    
    public function update($id, array $data) {
        try {
            DB::beginTransaction();

            // Handle array-based name and type updates
            if (!empty($data['name']) && !empty($data['type'])) {
                $count = count($data['name']);

                for ($i = 0; $i < $count; $i++) {
                    if (!empty($data['name'][$i]) && !empty($data['type'][$i])) {
                        $agencyData = [
                            'name' => $data['name'][$i],
                            'agency_type' => $data['type'][$i],
                            'slug' => Str::slug($data['name'][$i]),
                        ];

                        if (isset($data['status'])) {
                            $agencyData['status'] = $data['status'];
                        }

                        if ($i == 0) {
                            // Update the main agency
                            $this->repo->updateAgency($id, $agencyData);
                        }

                        // Handle client relationships - update existing records
                        if (isset($data['client']) && !empty($data['client'][$i]) && is_array($data['client'][$i])) {
                            $clients = $data['client'][$i];
                            
                            // Get existing relationships for this agency
                            $existingRelationships = BrandAgencyRelationship::where('agency_id', $id)->get();
                            
                            // Update existing records with new brand IDs
                            foreach ($existingRelationships as $index => $relationship) {
                                if (isset($clients[$index])) {
                                    $relationship->update(['brand_id' => $clients[$index]]);
                                }
                            }
                            
                            // If there are more clients than existing relationships, create new ones
                            if (count($clients) > $existingRelationships->count()) {
                                for ($j = $existingRelationships->count(); $j < count($clients); $j++) {
                                    $agencyClients = new BrandAgencyRelationship();
                                    $agencyClients->agency_id = $id;
                                    $agencyClients->brand_id = $clients[$j];
                                    $agencyClients->save();
                                }
                            }
                        }
                    }
                }
            } elseif (isset($data['name']) && is_string($data['name'])) {
                // Handle single name update (backward compatibility)
                $model = $this->repo->getAgencyById($id);
                if ($model->name !== $data['name']) {
                    $data['slug'] = Str::slug($data['name']);
                }
                $this->repo->updateAgency($id, $data);
            } else {
                // Update without name/type changes - handle client relationships if provided
                if (isset($data['client']) && is_array($data['client'])) {
                    $clients = $data['client'];
                    
                    // Get existing relationships for this agency
                    $existingRelationships = BrandAgencyRelationship::where('agency_id', $id)->get();
                    
                    // Update existing records with new brand IDs
                    foreach ($existingRelationships as $index => $relationship) {
                        if (isset($clients[$index])) {
                            $relationship->update(['brand_id' => $clients[$index]]);
                        }
                    }
                    
                    // If there are more clients than existing relationships, create new ones
                    if (count($clients) > $existingRelationships->count()) {
                        for ($j = $existingRelationships->count(); $j < count($clients); $j++) {
                            $agencyClients = new BrandAgencyRelationship();
                            $agencyClients->agency_id = $id;
                            $agencyClients->brand_id = $clients[$j];
                            $agencyClients->save();
                        }
                    }
                    
                    // Remove client from data array to avoid updating it in the main update
                    unset($data['client']);
                }
                
                // Update remaining data if any
                if (!empty($data)) {
                    $this->repo->updateAgency($id, $data);
                }
            }

            DB::commit();
            return $this->repo->getAgencyById($id);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function createUniqueSlug(string $name, $excludeId = null): string {
        return Str::slug($name);
    }
}