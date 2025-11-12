<?php

namespace App\Contracts\Repositories;

interface AgencyRepositoryInterface 
{
    public function getAllAgency(int $perPage = 10);
    public function getAgencyById(int $id);
    public function getAgencyBySlug(string $slug);
    public function createAgency(array $data);
    public function updateAgency(int $id, array $data);
    public function deleteAgency(int $id);
}