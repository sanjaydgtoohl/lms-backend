<?php

namespace App\Events;

class LeadCallStatusAddedEvent
{
    protected int $leadId;
    protected int $callStatusId;
    protected ?int $previousCallStatusId;
    protected int $updatedByUserId;
    protected $timestamp;

    public function __construct(int $leadId, int $callStatusId, ?int $previousCallStatusId, int $updatedByUserId, $timestamp = null)
    {
        $this->leadId = $leadId;
        $this->callStatusId = $callStatusId;
        $this->previousCallStatusId = $previousCallStatusId;
        $this->updatedByUserId = $updatedByUserId;
        $this->timestamp = $timestamp ?? now();
    }

    public function getLeadId(): int
    {
        return $this->leadId;
    }

    public function getCallStatusId(): int
    {
        return $this->callStatusId;
    }

    public function getPreviousCallStatusId(): ?int
    {
        return $this->previousCallStatusId;
    }

    public function getUpdatedByUserId(): int
    {
        return $this->updatedByUserId;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }
}