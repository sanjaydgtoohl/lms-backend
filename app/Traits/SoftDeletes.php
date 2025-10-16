<?php

namespace App\Traits;

use Carbon\Carbon;

trait SoftDeletes
{
    /**
     * Get formatted deleted_at timestamp
     */
    public function getDeletedAtFormattedAttribute(): string
    {
        return $this->deleted_at ? $this->deleted_at->format('Y-m-d H:i:s') : '';
    }

    /**
     * Get human readable time difference for deleted_at
     */
    public function getDeletedAtHumanAttribute(): string
    {
        return $this->deleted_at ? $this->deleted_at->diffForHumans() : '';
    }

    /**
     * Check if record is soft deleted
     */
    public function isDeleted(): bool
    {
        return !is_null($this->deleted_at);
    }

    /**
     * Restore soft deleted record
     */
    public function restore(): bool
    {
        if ($this->isDeleted()) {
            $this->deleted_at = null;
            return $this->save();
        }
        return false;
    }

    /**
     * Force delete record
     */
    public function forceDelete(): bool
    {
        return parent::delete();
    }
}