<?php

namespace App\Observers;

use App\Models\Brief;
use App\Models\User;

class BriefObserver
{
    /**
     * Handle the Brief "creating" event.
     *
     * @param  \App\Models\Brief  $brief
     * @return void
     */
    public function creating(Brief $brief)
    {
        if (empty($brief->assign_user_id)) {
            $organisationId = $this->determineOrganisationId($brief);
            $plannerAdmin = $this->findPlannerAdmin($organisationId);

            if ($plannerAdmin) {
                $brief->assign_user_id = $plannerAdmin->id;
            }
        }
    }

    /**
     * Determine the organisation ID for the brief.
     * Prioritizes the Lead's organisation, with a fallback to the creator's organisation.
     *
     * @param Brief $brief
     * @return int|null
     */
    private function determineOrganisationId(Brief $brief): ?int
    {
        // Primary: Get organisation from the associated Lead
        if ($brief->contact_person_id) {
            $lead = \App\Models\Lead::find($brief->contact_person_id);
            if ($lead && $lead->organisation_id) {
                return $lead->organisation_id;
            }
        }

        // Fallback: Get organisation from the user creating the brief
        if ($brief->created_by) {
            $creator = User::find($brief->created_by);
            if ($creator && $creator->organisation_id) {
                return $creator->organisation_id;
            }
        }

        return null;
    }

    /**
     * Find a Planner Admin, optionally filtered by organisation.
     *
     * @param int|null $organisationId
     * @return User|null
     */
    private function findPlannerAdmin(?int $organisationId): ?User
    {
        $query = User::whereHas('roles', function ($q) {
            $q->where('slug', 'planner-admin')
              ->orWhere('name', 'Planner Admin');
        });

        if ($organisationId) {
            $query->where(function ($q) use ($organisationId) {
                $q->where('organisation_id', $organisationId)
                  ->orWhereHas('organisations', function ($subQ) use ($organisationId) {
                      $subQ->where('organisations.id', $organisationId);
                  });
            });
        }

        return $query->first();
    }
}
