<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    /**
     * Determine if the user can view any leads.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view the leads list
        // (but filtering is applied at the query level)
        return true;
    }

    /**
     * Determine if the user can view the given lead.
     * Super Admin (role_id = 8) can view all. Others: only creator or assigned user can view.
     */
    public function view(User $user, Lead $lead): bool
    {
        // Super Admin (role_id = 8) can view any lead
        if ($user->roles()->where('id', 8)->exists()) {
            return true;
        }

        // User can view if they are the one who created it (assigned_by)
        if ($lead->created_by === $user->id) {
            return true;
        }

        // User can view if they are assigned to it (assigned_to)
        if ($lead->current_assign_user === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can create leads.
     */
    public function create(User $user): bool
    {
        // Check if user has permission to create leads
        return $user->hasPermission('leads:create') || $user->hasRole('super-admin');
    }

    /**
     * Determine if the user can update the given lead.
     * Only creator and assigned user can update, with permission check.
     */
    public function update(User $user, Lead $lead): bool
    {
        // Check if user has permission to update leads
        if (!$user->hasPermission('leads:update')) {
            return false;
        }

        // User can update if they are the one who created it (assigned_by)
        if ($lead->created_by === $user->id) {
            return true;
        }

        // User can update if they are assigned to it (assigned_to)
        if ($lead->current_assign_user === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can delete the given lead.
     * Only creator can delete, with permission check.
     */
    public function delete(User $user, Lead $lead): bool
    {
        // Check if user has permission to delete leads
        if (!$user->hasPermission('leads:delete')) {
            return false;
        }

        // Only the user who created the lead (assigned_by) can delete it
        return $lead->created_by === $user->id;
    }

    /**
     * Determine if the user can restore the given lead.
     * Only creator can restore.
     */
    public function restore(User $user, Lead $lead): bool
    {
        // Only the creator can restore their lead
        return $lead->created_by === $user->id;
    }

    /**
     * Determine if the user can permanently delete the given lead.
     * Only creator can force delete.
     */
    public function forceDelete(User $user, Lead $lead): bool
    {
        // Only the creator can permanently delete their lead
        return $lead->created_by === $user->id;
    }
}
