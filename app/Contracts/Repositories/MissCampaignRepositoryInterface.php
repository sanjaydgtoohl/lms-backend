<?php

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
}
