<?php

namespace App\Repositories;

use App\Models\MissCampaignHistory;
use App\Contracts\Repositories\MissCampaignHistoryRepositoryInterface;

class MissCampaignHistoryRepository implements MissCampaignHistoryRepositoryInterface
{
    public function getByMissCampaignId($missCampaignId)
    {
        return MissCampaignHistory::with(['assignBy', 'assignTo'])
            ->where('miss_campaign_id', $missCampaignId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
    public function all()
    {
        return MissCampaignHistory::all();
    }

    public function find($id)
    {
        return MissCampaignHistory::find($id);
    }

    public function create(array $data)
    {
        return MissCampaignHistory::create($data);
    }

    public function update($id, array $data)
    {
        $history = MissCampaignHistory::find($id);
        if ($history) {
            $history->update($data);
        }
        return $history;
    }

    public function delete($id)
    {
        $history = MissCampaignHistory::find($id);
        if ($history) {
            $history->delete();
            return true;
        }
        return false;
    }
}
