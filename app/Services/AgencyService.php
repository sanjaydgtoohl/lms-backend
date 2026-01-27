<?php
namespace App\Services;

use App\Contracts\Repositories\AgencyRepositoryInterface;
use App\Models\Agency;
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
                        
                        // Check if agency name already exists (excluding soft-deleted)
                        if (Agency::where('name', $data['name'][$i])->whereNull('deleted_at')->exists()) {
                            throw new \DomainException('Agency name "' . $data['name'][$i] . '" already exists. Agency name must be unique.');
                        }
    
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
                            // Get Direct agency ID
                            $directAgencyId = $this->getOrCreateDirectAgency();
                            
                            foreach($clients as $client){
                                // Remove Direct agency from this brand if it exists
                                BrandAgencyRelationship::where('brand_id', $client)
                                    ->where('agency_id', $directAgencyId)
                                    ->delete();
                                
                                // Assign this agency to the brand
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

            // Get Direct agency ID
            $directAgencyId = $this->getOrCreateDirectAgency();

            // Update agency basic info if name/type provided
            if (isset($data['name']) && !empty($data['name'])) {
                // Check if agency name already exists (excluding current agency and soft-deleted)
                $nameToCheck = is_array($data['name']) ? $data['name'][0] : $data['name'];
                
                if (Agency::where('name', $nameToCheck)->where('id', '!=', $id)->whereNull('deleted_at')->exists()) {
                    throw new \DomainException('Agency name "' . $nameToCheck . '" already exists. Agency name must be unique.');
                }

                $updateData = [
                    'name' => $nameToCheck,
                    'slug' => Str::slug($nameToCheck),
                ];

                // Add type if provided
                if (isset($data['type']) && !empty($data['type'])) {
                    $typeValue = is_array($data['type']) ? $data['type'][0] : $data['type'];
                    $updateData['agency_type'] = $typeValue;
                }

                // Add status if provided
                if (isset($data['status'])) {
                    $updateData['status'] = $data['status'];
                }

                // Update the agency
                $this->repo->updateAgency($id, $updateData);
            } elseif (isset($data['status'])) {
                // Update status only if provided
                $this->repo->updateAgency($id, ['status' => $data['status']]);
            }

            // Handle client/brand relationships
            if (isset($data['client']) && !empty($data['client'])) {
                $clients = is_array($data['client']) ? $data['client'] : [$data['client']];
                
                // Flatten if nested array
                $flatClients = [];
                foreach ($clients as $client) {
                    if (is_array($client)) {
                        $flatClients = array_merge($flatClients, $client);
                    } else {
                        $flatClients[] = $client;
                    }
                }

                // Permanently delete existing relationships for this agency
                BrandAgencyRelationship::where('agency_id', $id)->forceDelete();
                
                // Create new relationships and remove Direct from brands
                foreach ($flatClients as $brandId) {
                    // Remove Direct agency from this brand if it exists
                    BrandAgencyRelationship::where('brand_id', $brandId)
                        ->where('agency_id', $directAgencyId)
                        ->delete();
                    
                    // Assign this agency to the brand
                    $agencyClients = new BrandAgencyRelationship();
                    $agencyClients->agency_id = $id;
                    $agencyClients->brand_id = $brandId;
                    $agencyClients->save();
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

    private function getOrCreateDirectAgency(): int
    {
        $directAgency = Agency::withTrashed()
            ->whereRaw('LOWER(name) = ?', ['direct'])
            ->first();

        if (!$directAgency) {
            $directAgency = Agency::create([
                'name' => 'Direct',
                'slug' => 'direct',
                'status' => '1',
                'agency_type' => 1, // Assuming default agency type ID is 1
            ]);
        } elseif ($directAgency->trashed()) {
            $directAgency->restore();
        }

        return $directAgency->id;
    }

}