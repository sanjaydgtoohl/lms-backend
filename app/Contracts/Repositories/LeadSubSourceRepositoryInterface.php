<?php

namespace App\Contracts\Repositories;

interface LeadSubSourceRepositoryInterface 
{
    public function getAllLeadSubSources(array $filters = []);
    public function getLeadSubSourceById($id);
    public function createLeadSubSource(array $data);
    public function updateLeadSubSource($id, array $data);
    public function deleteLeadSubSource($id);
    public function findBySlug(string $slug);
}
