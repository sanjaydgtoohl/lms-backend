<?php

namespace App\Traits;

use Carbon\Carbon;

trait HasTimestamps
{
    /**
     * Get formatted created_at timestamp
     */
    public function getCreatedAtFormattedAttribute(): string
    {
        return $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : '';
    }

    /**
     * Get formatted updated_at timestamp
     */
    public function getUpdatedAtFormattedAttribute(): string
    {
        return $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : '';
    }

    /**
     * Get human readable time difference for created_at
     */
    public function getCreatedAtHumanAttribute(): string
    {
        return $this->created_at ? $this->created_at->diffForHumans() : '';
    }

    /**
     * Get human readable time difference for updated_at
     */
    public function getUpdatedAtHumanAttribute(): string
    {
        return $this->updated_at ? $this->updated_at->diffForHumans() : '';
    }

    /**
     * Check if record was created recently
     */
    public function wasCreatedRecently(int $minutes = 5): bool
    {
        if (!$this->created_at) {
            return false;
        }

        return $this->created_at->greaterThan(Carbon::now()->subMinutes($minutes));
    }
}
