<?php

namespace App\Events;

class LeadStatusChangedEvent
{
    protected int $leadId;
    protected int $status;

    public function __construct(int $leadId, int $status)
    {
        $this->leadId = $leadId;
        $this->status = $status;
    }

    public function getLeadId(): int
    {
        return $this->leadId;
    }

    public function getStatus(): int
    {
        return $this->status;
    }
}