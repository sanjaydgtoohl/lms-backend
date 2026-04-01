<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class BriefAssignedEvent
{
    use SerializesModels;

    public $briefId;
    public $userId;

    public function __construct(int $briefId, int $userId)
    {
        $this->briefId = $briefId;
        $this->userId = $userId;
    }
}