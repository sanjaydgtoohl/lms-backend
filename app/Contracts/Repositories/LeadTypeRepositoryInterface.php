<?php

namespace App\Contracts\Repositories;

/**
 * Lead Type Repository Interface
 * -----------------------------------------
 * Defines the contract for lead type data access operations.
 * Part of the repository layer to abstract database interactions
 * and support clean architecture principles.
 *
 * @package App\Contracts\Repositories
 * @author Achal Sharma
 * @version 1.0.0
 * @since 2026-05-05
 */

interface LeadTypeRepositoryInterface
{
    public function getAllLeadTypes(int $perPage = 10, ?string $searchTerm = null);
    public function listLeadTypes();
    public function getLeadTypeById(int $id);
    public function createLeadType(array $data);
}