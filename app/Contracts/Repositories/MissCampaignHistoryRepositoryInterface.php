<?php

namespace App\Contracts\Repositories;

interface MissCampaignHistoryRepositoryInterface
{
    /**
     * Get all history records for a specific miss campaign ID.
     *
     * @param int $missCampaignId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByMissCampaignId($missCampaignId);

    /**
     * Get all history records.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all();

    /**
     * Find a history record by ID.
     *
     * @param int $id
     * @return \App\Models\MissCampaignHistory|null
     */
    public function find($id);

    /**
     * Create a new history record.
     *
     * @param array $data
     * @return \App\Models\MissCampaignHistory
     */
    public function create(array $data);

    /**
     * Update a history record by ID.
     *
     * @param int $id
     * @param array $data
     * @return \App\Models\MissCampaignHistory|null
     */
    public function update($id, array $data);

    /**
     * Delete a history record by ID.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id);
}