<?php

namespace App\Policies;

use App\Models\Brief;
use App\Models\User;

class BriefPolicy
{
    /**
     * Determine if the user can view any briefs.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view the briefs list
        // (but filtering is applied at the query level)
        return true;
    }

    /**
     * Determine if the user can view the given brief.
     * Super Admin (role_id = 8) can view all. Others: only creator or assigned user can view.
     */
    public function view(User $user, Brief $brief): bool
    {
        // Super Admin (role_id = 8) can view any brief
        if ($user->roles()->where('id', 8)->exists()) {
            return true;
        }

        // User can view if they created it (created_by)
        if ($brief->created_by === $user->id) {
            return true;
        }

        // User can view if they are assigned to it (assign_user_id)
        if ($brief->assign_user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can create briefs.
     */
    public function create(User $user): bool
    {
        // Check if user has permission to create briefs
        return $user->hasPermission('briefs:create') || $user->hasRole('admin');
    }

    /**
     * Determine if the user can update the given brief.
     * Only creator and assigned user can update, with permission check.
     */
    public function update(User $user, Brief $brief): bool
    {
        // Check if user has permission to update briefs
        if (!$user->hasPermission('briefs:update')) {
            return false;
        }

        // User can update if they created it (created_by)
        if ($brief->created_by === $user->id) {
            return true;
        }

        // User can update if they are assigned to it (assign_user_id)
        if ($brief->assign_user_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the user can delete the given brief.
     * Only creator can delete, with permission check.
     */
    public function delete(User $user, Brief $brief): bool
    {
        // Check if user has permission to delete briefs
        if (!$user->hasPermission('briefs:delete')) {
            return false;
        }

        // Only the user who created the brief (created_by) can delete it
        return $brief->created_by === $user->id;
    }

    /**
     * Determine if the user can restore the given brief.
     * Only creator can restore.
     */
    public function restore(User $user, Brief $brief): bool
    {
        // Only the creator can restore their brief
        return $brief->created_by === $user->id;
    }

    /**
     * Determine if the user can permanently delete the given brief.
     * Only creator can force delete.
     */
    public function forceDelete(User $user, Brief $brief): bool
    {
        // Only the creator can permanently delete their brief
        return $brief->created_by === $user->id;
    }
}
