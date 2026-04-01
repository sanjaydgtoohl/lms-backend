<?php

namespace App\Events;

class LeadStatusChangedEvent
{
    public $leadId;
    public $status;

    public function __construct(int $leadId, int $status)
    {
        $this->leadId = $leadId;
        $this->status = $status;
    }
}