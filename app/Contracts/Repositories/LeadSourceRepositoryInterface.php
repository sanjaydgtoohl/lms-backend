<?php

/**
 * Lead Source Repository Interface
 * -----------------------------------------
 * Defines the contract for lead source data access operations.
 * Part of the repository layer to abstract database interactions
 * and support clean architecture principles.
 *
 * @package App\Contracts\Repositories
 * @author Achal Sharma
 * @version 1.0.0
 * @since 2026-05-05
 */
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
