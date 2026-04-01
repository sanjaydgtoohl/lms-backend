<?php

namespace App\Events;

class LeadCallStatusAddedEvent
{
    public $leadId;
    public $callStatusId;
    public $previousCallStatusId;
    public $updatedByUserId;
    public $timestamp;

    public function __construct(int $leadId, int $callStatusId, ?int $previousCallStatusId, int $updatedByUserId, $timestamp = null)
    {
        $this->leadId = $leadId;
        $this->callStatusId = $callStatusId;
        $this->previousCallStatusId = $previousCallStatusId;
        $this->updatedByUserId = $updatedByUserId;
        $this->timestamp = $timestamp ?? now();
    }
}