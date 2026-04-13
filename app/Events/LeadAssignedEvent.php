<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class LeadAssignedEvent
{
    use SerializesModels;

    protected int $leadId;
    protected int $userId;

    public function __construct(int $leadId, int $userId)
    {
        $this->leadId = $leadId;
        $this->userId = $userId;
    }

    public function getLeadId(): int
    {
        return $this->leadId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}