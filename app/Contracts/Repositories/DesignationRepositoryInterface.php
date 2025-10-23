<?php

namespace App\Contracts\Repositories;

interface DesignationRepositoryInterface 
{
    public function getAllDesignations();
    public function getDesignationById($id);
    public function createDesignation(array $data);
    public function updateDesignation($id, array $data);
    public function deleteDesignation($id);
    public function slugExists(string $slug, $excludeId = null): bool;
    public function findBySlug(string $slug);
}
