<?php

namespace App\Services;

use App\Contracts\Repositories\OrganisationRepositoryInterface;
use Illuminate\Support\Facades\Log;
use DomainException;
use Illuminate\Database\QueryException;
use Exception;

/**
 * Organisation Service
 * -----------------------------------------
 * Handles business logic for organisation management,
 * interacting with the repository layer for data access.
 * Implements clean architecture principles with dependency injection,
 * structured exception handling, and logging.
 *
 * @package App\Services
 * @author Achal Sharma
 * @version 1.0.0
 * @since 2026-05-05
 */

class OrganisationService
{
    protected $organisationRepository;

    /**
     * Inject the Organisation repository interface
     */
    public function __construct(OrganisationRepositoryInterface $organisationRepository)
    {
        $this->organisationRepository = $organisationRepository;
    }

    /**
     * Get all organisations.
     */
    public function getAllOrganisations(int $perPage = 10, ?string $searchTerm = null)
    {
        try {
            return $this->organisationRepository->getAllOrganisations($perPage, $searchTerm);
        } catch (QueryException $e) {
            Log::error('Database error fetching organisations: ' . $e->getMessage());
            throw new DomainException('Database error while fetching organisations.');
        } catch (Exception $e) {
            Log::error('Unexpected error fetching organisations: ' . $e->getMessage());
            throw new DomainException('Unexpected error while fetching organisations.');
        }
    }

    /**
     * Get organisation by ID
     */
    public function getOrganisationById(int $id)
    {
        try {
            $organisation = $this->organisationRepository->getOrganisationById($id);
            if (!$organisation) {
                throw new DomainException('Organisation not found.');
            }
            return $organisation;
        } catch (QueryException $e) {
            Log::error('Database error fetching organisation: ' . $e->getMessage());
            throw new DomainException('Database error while fetching organisation.');
        } catch (Exception $e) {
            Log::error('Unexpected error fetching organisation: ' . $e->getMessage());
            throw new DomainException('Unexpected error while fetching organisation.');
        }
    }
}