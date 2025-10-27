<?php
namespace App\Contracts\Repositories;

interface AgencyRepositoryInterface
{
    public function getAll();
    public function findById($id);
    public function create(array $data); // Simple create
    public function update($id, array $data);
    public function delete($id);
    public function findBySlug(string $slug);
    
    // Nested creation ke liye
    public function createAgencyWithBrands(array $agencyData, array $brandsData);
}
