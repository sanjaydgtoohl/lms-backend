<?php

namespace App\Contracts\Repositories;

/**
 * Organisation Repository Interface
 * -----------------------------------------
 * Defines the contract for organisation data access operations.
 * Part of the repository layer to abstract database interactions
 * and support clean architecture principles.
 *
 * @package App\Contracts\Repositories
 * @author Achal Sharma
 * @version 1.0.0
 * @since 2026-05-05
 */

interface OrganisationRepositoryInterface
{
    public function getAllOrganisations(int $perPage = 10, ?string $searchTerm = null);
}