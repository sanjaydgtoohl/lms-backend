<?php

/**
 * MissCampaign Repository Interface
 * -----------------------------------------
 * Defines the contract for miss campaign data access operations and repository methods.
 *
 * @package App\Contracts\Repositories
 * @author Achal Sharma
 * @version 1.0.0
 * @since 2026-04-08
 */

namespace App\Contracts\Repositories;

interface MissCampaignRepositoryInterface
{
    public function getAllMissCampaigns(int $perPage = 10, ?string $searchTerm = null);
    public function getMissCampaignList();
    public function getMissCampaignById(int $id);
    public function getMissCampaignBySlug(string $slug);
    public function createMissCampaign(array $data);
    public function updateMissCampaign(int $id, array $data);
    public function deleteMissCampaign(int $id);
    public function updateStatus(int $id, string $status): bool;
    public function assignUser(int $id, int $userId, int $assignBy): bool;
}
