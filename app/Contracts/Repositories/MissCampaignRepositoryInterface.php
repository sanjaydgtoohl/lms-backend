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
    public function updateStatus(int $id, string $status, ?int $assignTo = null, ?int $assignBy = null);
    public function updateAssign(int $id, int $assignTo, ?int $assignBy = null);
    public function updateComment(int $id, ?string $comment = null);
}
