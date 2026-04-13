<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class BriefAssignedEvent
{
    use SerializesModels;

    protected int $briefId;
    protected int $userId;

    public function __construct(int $briefId, int $userId)
    {
        $this->briefId = $briefId;
        $this->userId = $userId;
    }

    public function getBriefId(): int
    {
        return $this->briefId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}