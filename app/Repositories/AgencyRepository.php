<?php
namespace App\Repositories;

use App\Contracts\Repositories\AgencyRepositoryInterface;
use App\Models\Agency;
use App\Models\AgencyBrand;
use Illuminate\Support\Facades\DB;

class AgencyRepository implements AgencyRepositoryInterface
{
    protected $model;
    public function __construct(Agency $model) { $this->model = $model; }
    
    // Sabhi relationships load karein
    public function getAll() { 
        return $this->model->with(['agencyGroup', 'agencyType', 'brands'])
            ->orderBy('name')->paginate(10); 
    }
    public function findById($id) { 
        return $this->model->with(['agencyGroup', 'agencyType', 'brands'])
            ->findOrFail($id); 
    }
    public function create(array $data) { return $this->model->create($data); }
    public function update($id, array $data) { $model = $this->model->findOrFail($id); $model->update($data); return $model; }
    public function delete($id) { return $this->model->findOrFail($id)->delete(); }
    public function findBySlug(string $slug) { return $this->model->where('slug', $slug)->first(); }

    // Transaction mein Agency aur Brands create karein
    public function createAgencyWithBrands(array $agencyData, array $brandsData)
    {
        return DB::transaction(function () use ($agencyData, $brandsData) {
            // 1. Agency create karein
            $agency = $this->model->create($agencyData);

            // 2. Us agency ke brands create karein
            $createdBrands = [];
            foreach ($brandsData as $brand) {
                // 'agency_id' ko data mein add karein
                $brand['agency_id'] = $agency->id;
                $createdBrands[] = AgencyBrand::create($brand);
            }
            
            // Agency ko brands ke saath return karein
            $agency->load('brands', 'agencyGroup', 'agencyType');
            return $agency;
        });
    }
}
