<?php

namespace App\Events;

class BriefStatusChangedEvent
{
    protected $briefId;
    protected $briefName;
    protected $previousStatusId;
    protected $previousStatusName;
    protected $newStatusId;
    protected $newStatusName;
    protected $updatedByUserId;
    protected $updatedByUserName;
    protected $timestamp;

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

    public function getBriefId()
    {
        return $this->briefId;
    }

    public function getBriefName()
    {
        return $this->briefName;
    }

    public function getPreviousStatusId()
    {
        return $this->previousStatusId;
    }

    public function getPreviousStatusName()
    {
        return $this->previousStatusName;
    }

    public function getNewStatusId()
    {
        return $this->newStatusId;
    }

    public function getNewStatusName()
    {
        return $this->newStatusName;
    }

    public function getUpdatedByUserId()
    {
        return $this->updatedByUserId;
    }

    public function getUpdatedByUserName()
    {
        return $this->updatedByUserName;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }
}
