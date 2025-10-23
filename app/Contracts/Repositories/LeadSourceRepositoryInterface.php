<?php

namespace App\Contracts\Repositories;

interface LeadSourceRepositoryInterface 
{
    public function getAllLeadSources();
    public function getLeadSourceById($id);
    public function createLeadSource(array $data);
    public function updateLeadSource($id, array $data);
    public function deleteLeadSource($id);
    public function findBySlug(string $slug);
}
