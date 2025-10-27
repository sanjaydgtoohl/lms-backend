<?php
namespace App\Services;

use App\Contracts\Repositories\AgencyRepositoryInterface;
use App\Contracts\Repositories\AgencyGroupRepositoryInterface;
use Illuminate\Support\Str;

class AgencyService
{
    protected $agencyRepo;
    protected $groupRepo;

    public function __construct(
        AgencyRepositoryInterface $agencyRepo,
        AgencyGroupRepositoryInterface $groupRepo
    ) {
        $this->agencyRepo = $agencyRepo;
        $this->groupRepo = $groupRepo;
    }

    public function getAll() { return $this->agencyRepo->getAll(); }
    public function getById($id) { return $this->agencyRepo->findById($id); }
    public function delete($id) { return $this->agencyRepo->delete($id); }

    /**
     * Workflow ke hisaab se Complex Create Method
     */
    public function createComplexAgency(array $data)
    {
        $groupId = null;

        // Step 1: Group ko handle karein
        if (isset($data['group_type'])) {
            if ($data['group_type'] === 'existing' && isset($data['group_id'])) {
                $groupId = $data['group_id'];
                // Check karein ki group exist karta hai ya nahi
                $this->groupRepo->findById($groupId); 
            } 
            elseif ($data['group_type'] === 'new' && isset($data['group_name'])) {
                // Naya group create karein
                $groupSlug = $this->createUniqueGroupSlug($data['group_name']);
                $newGroup = $this->groupRepo->create([
                    'name' => $data['group_name'],
                    'slug' => $groupSlug,
                    'status' => '1'
                ]);
                $groupId = $newGroup->id;
            }
        }

        // Step 2: Agencies aur Brands ko loop karke create karein
        $createdAgencies = [];
        foreach ($data['agencies'] as $agency) {
            // 2a. Agency ka data prepare karein
            $agencyData = [
                'name' => $agency['name'],
                'slug' => $this->createUniqueAgencySlug($agency['name']),
                'agency_type_id' => $agency['agency_type_id'],
                'agency_group_id' => $groupId, // Pehle step se Group ID
                'status' => $agency['status'] ?? '1'
            ];
            
            // 2b. Brands ka data prepare karein
            $brandsData = [];
            if (isset($agency['brands'])) {
                foreach ($agency['brands'] as $brand) {
                    $brandsData[] = [
                        'name' => $brand['name'],
                        'slug' => $this->createUniqueBrandSlug($brand['name']), // Brand slug
                        'status' => $brand['status'] ?? '1'
                    ];
                }
            }

            // 2c. Repository ko bhej dein transaction mein create karne ke liye
            $createdAgencies[] = $this->agencyRepo->createAgencyWithBrands($agencyData, $brandsData);
        }

        return $createdAgencies;
    }

    // Update method (Simple - bulk update nahi hai)
    public function update($id, array $data) {
        if (isset($data['name'])) {
            $model = $this->agencyRepo->findById($id);
            if ($model->name !== $data['name']) {
                $data['slug'] = $this->createUniqueAgencySlug($data['name'], $id);
            }
        }
        return $this->agencyRepo->update($id, $data);
        // Note: Nested brands ko update karna ek alag (complex) endpoint hona chahiye
    }


    // --- Slug Helper Methods ---
    private function createUniqueAgencySlug(string $name, $excludeId = null): string {
        $slug = Str::slug($name); $originalSlug = $slug; $count = 1;
        $existing = $this->agencyRepo->findBySlug($slug);
        while ($existing && $existing->id != $excludeId) {
            $slug = $originalSlug . '-' . $count++;
            $existing = $this->agencyRepo->findBySlug($slug);
        }
        return $slug;
    }
    
    private function createUniqueGroupSlug(string $name, $excludeId = null): string {
        $slug = Str::slug($name); $originalSlug = $slug; $count = 1;
        $existing = $this->groupRepo->findBySlug($slug);
        while ($existing && $existing->id != $excludeId) {
            $slug = $originalSlug . '-' . $count++;
            $existing = $this->groupRepo->findBySlug($slug);
        }
        return $slug;
    }
    
    // Brand slug ko unique karne ke liye, humein BrandRepository ki zaroorat padegi
    // Abhi ke liye simple slug use kar rahe hain
    private function createUniqueBrandSlug(string $name): string {
        // Asli implementation mein BrandRepository se check karna chahiye
        return Str::slug($name);
    }
}
