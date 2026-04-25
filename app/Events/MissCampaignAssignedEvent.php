<?php

/**
 * MissCampaignAssignedEvent
 * -----------------------------------------
 * Event triggered when a miss campaign is assigned to a user
 *
 * @package App\Events
 * @author Achal Sharma
 * @version 1.0.0
 * @since 2026-04-19
 */

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class MissCampaignAssignedEvent
{
    use SerializesModels;

    protected int $missCampaignId;
    protected int $userId;

    public function __construct(int $missCampaignId, int $userId)
    {
        $this->missCampaignId = $missCampaignId;
        $this->userId = $userId;
    }

    public function getMissCampaignId(): int
    {
        return $this->missCampaignId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}
