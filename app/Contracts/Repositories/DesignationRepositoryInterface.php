<?php

namespace App\Contracts\Repositories;

interface DesignationRepositoryInterface 
{
    public function getAllDesignations();
    public function getDesignationById($id);
    public function createDesignation(array $data);
    public function updateDesignation($id, array $data);
    public function deleteDesignation($id);
    // Check whether a slug exists for a designation (optionally excluding an id)
    public function slugExists(string $slug, $excludeId = null): bool;
    // Find designation by slug
    public function findBySlug(string $slug);
}
