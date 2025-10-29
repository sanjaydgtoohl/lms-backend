<?php

namespace App\Services;

use App\Contracts\Repositories\AgencyRepositoryInterface;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
// Import ValidationException
use Illuminate\Validation\ValidationException; 
use Throwable;

class AgencyService
{
    protected $agencyRepository;

    public function __construct(AgencyRepositoryInterface $agencyRepository)
    {
        $this->agencyRepository = $agencyRepository;
    }

    public function getAgencies()
    {
        return $this->agencyRepository->getPaginated();
    }

    public function getCreateData()
    {
        return $this->agencyRepository->getCreateDependencies();
    }

    public function getAgencyById(int $id)
    {
        return $this->agencyRepository->findById($id);
    }

    public function createAgency(array $data)
    {
        $data['slug'] = Str::slug($data['name']);
        $data['status'] = '1';
        return $this->agencyRepository->create($data);
    }

    public function updateAgency(int $id, array $data)
    {
        $data['slug'] = Str::slug($data['name']);
        return $this->agencyRepository->update($id, $data);
    }

    public function deleteAgency(int $id)
    {
        return $this->agencyRepository->delete($id);
    }

    /**
     * @throws ValidationException|Throwable
     */
    public function createAgencyBatch(array $agencies)
    {
        $agencyRules = [
            'name' => 'required|string|max:255',
            'agency_group_id' => 'nullable|exists:agency_groups,id',
            'agency_type_id' => 'required|exists:agency_types,id', // Fix: agency_type -> agency_types
            'brand_id' => 'required|exists:brands,id',
        ];

        $createdAgencies = [];
        $validationErrors = [];

        DB::beginTransaction();
        
        try {
            foreach ($agencies as $index => $agencyData) {
                $validator = Validator::make($agencyData, $agencyRules);

                if ($validator->fails()) {
                    $validationErrors["agency_" . $index] = $validator->errors()->toArray();
                } else {
                    $validatedData = $validator->validated();
                    $validatedData['slug'] = Str::slug($validatedData['name']);
                    $validatedData['status'] = '1';
                    $createdAgencies[] = $this->agencyRepository->create($validatedData);
                }
            }

            if (!empty($validationErrors)) {
                // Agar validation errors hain, toh rollback karein aur exception throw karein
                DB::rollBack();
                // Yeh exception controller ke handleException method dwara pakdi jayegi
                throw ValidationException::withMessages($validationErrors); 
            }

            // Sab theek hai, commit karein
            DB::commit();
            return $createdAgencies;

        } catch (Throwable $e) {
            DB::rollBack();
            throw $e; // Re-throw exception taaki controller ise handle kar sake
        }
    }
}