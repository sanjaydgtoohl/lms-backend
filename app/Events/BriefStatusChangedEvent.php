<?php

namespace App\Events;

class BriefStatusChangedEvent
{
    public $briefId;
    public $briefName;
    public $previousStatusId;
    public $previousStatusName;
    public $newStatusId;
    public $newStatusName;
    public $updatedByUserId;
    public $updatedByUserName;
    public $timestamp;

    public function __construct($briefId, $briefName, $previousStatusId, $previousStatusName, $newStatusId, $newStatusName, $updatedByUserId, $updatedByUserName, $timestamp = null)
    {
        $this->briefId = $briefId;
        $this->briefName = $briefName;
        $this->previousStatusId = $previousStatusId;
        $this->previousStatusName = $previousStatusName;
        $this->newStatusId = $newStatusId;
        $this->newStatusName = $newStatusName;
        $this->updatedByUserId = $updatedByUserId;
        $this->updatedByUserName = $updatedByUserName;
        $this->timestamp = $timestamp ?? now();
    }
}
