<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User;
use App\Models\Organisation;
use App\Models\Lead;
use App\Models\Brief;
use App\Models\MissCampaign;
use App\Models\Planner;
use App\Support\DashboardFilters;
use App\Support\UserAccessScope;
use App\Support\PlannerMetrics;

class Dashboard extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = ['id'];

    public function fetchTotalUserCount(array $filters, ?User $user): int
    {
        $query = User::query()->whereNull('deleted_at');
        DashboardFilters::applyUserOrganisationFilter($query, $filters);
        DashboardFilters::applyDateFilter($query, $filters, 'created_at');

        if ($user) {
            $strictDescendantIds = UserAccessScope::getStrictDescendantsInOrganisation($user);
            $query->whereIn('id', $strictDescendantIds);
        }

        return $query->count();
    }

    public function fetchAccessibleOrganisations(array $filters, ?User $user): Collection
    {
        $organisationsQuery = Organisation::query()->orderBy('name');
        
        if (!empty($filters['organisation_ids'])) {
            $organisationsQuery->whereIn('id', $filters['organisation_ids']);
        } elseif ($user) {
            $accessibleOrgIds = UserAccessScope::getAccessibleOrganisationIds($user);
            if (!empty($accessibleOrgIds)) {
                $organisationsQuery->whereIn('id', $accessibleOrgIds);
            }
        }

        return $organisationsQuery->get(['id', 'name']);
    }

    public function fetchOrganisationChartRow(array $filters, ?User $user, int $organisationId, string $organisationName): array
    {
        $leadsQuery = Lead::query()->accessibleToUser($user)->whereNull('deleted_at');
        DashboardFilters::applyLeadDashboardFilters($leadsQuery, $filters);

        $preLeadsQuery = MissCampaign::query()->accessibleToUser($user)->notDeleted()->where('miss_campaigns.status', '1');
        DashboardFilters::applyMissCampaignDashboardFilters($preLeadsQuery, $filters);

        $briefsQuery = Brief::query()->accessibleToUser($user)->whereNull('deleted_at')->whereRaw('briefs.status != 15');
        DashboardFilters::applyBriefDashboardFilters($briefsQuery, $filters);

        return [
            'organisation_id' => $organisationId,
            'organisation_name' => $organisationName,
            'total_leads' => (int) (clone $leadsQuery)->count(),
            'pre_leads' => (int) (clone $preLeadsQuery)->count(),
            'briefs' => (int) (clone $briefsQuery)->count(),
            'brief_budget' => (float) (clone $briefsQuery)->sum('briefs.budget'),
        ];
    }

    public function fetchSalesPipelineCounts(array $filters, ?User $user): array
    {
        $leadQuery = Lead::query()->accessibleToUser($user)->whereNull('deleted_at');
        DashboardFilters::applyLeadDashboardFilters($leadQuery, $filters, 'leads');

        $briefQuery = Brief::query()->accessibleToUser($user)->whereNull('deleted_at')->whereRaw('briefs.status != 15');
        DashboardFilters::applyBriefDashboardFilters($briefQuery, $filters, 'briefs');

        return [
            'new_leads' => (int) (clone $leadQuery)->count(),
            'follow_up' => (int) (clone $leadQuery)->whereHas('callStatusRelation', function ($query) {
                $query->where('slug', 'follow-up');
            })->count(),
            'meeting_scheduled' => (int) (clone $leadQuery)->whereHas('callStatusRelation', function ($query) {
                $query->where('slug', 'meeting-schedule');
            })->count(),
            'briefs' => (int) (clone $briefQuery)->count(),
        ];
    }

    public function fetchPlannerBriefStatusCounts(array $filters, ?User $user): array
    {
        $briefQuery = Brief::query()->accessibleToUser($user)->whereNull('deleted_at')->whereRaw('briefs.status != 15');
        DashboardFilters::applyBriefDashboardFilters($briefQuery, $filters, 'briefs');

        return [
            'active_briefs' => (int) (clone $briefQuery)->whereDate('submission_date', '>=', now())->count(),
            'closed_briefs' => (int) (clone $briefQuery)->whereHas('briefStatus', function ($query) {
                $query->where('slug', 'closed');
            })->count(),
            'overdue_briefs' => (int) (clone $briefQuery)->where('submission_date', '<', now())->count(),
        ];
    }

    public function fetchPlannerOrganisationRow(array $filters, ?User $user, int $organisationId, string $organisationName): array
    {
        $plannerQuery = Planner::query()
            ->whereNull('planners.deleted_at')
            ->whereHas('brief', function ($query) use ($user, $filters) {
                $query->accessibleToUser($user)
                    ->whereNull('briefs.deleted_at')
                    ->whereRaw('briefs.status != 15');
                DashboardFilters::applyBriefDashboardFilters($query, $filters, 'briefs');
            });

        $assignedPlans = (int) (clone $plannerQuery)->count();
        $avgAssignmentDays = $this->calculateAverageAssignmentToSubmissionDays($plannerQuery);

        return [
            'organisation_id' => $organisationId,
            'organisation_name' => $organisationName,
            'assigned_plans' => $assignedPlans,
            'avg_assignment_days' => $avgAssignmentDays ? round((float) $avgAssignmentDays, 1) : 0,
        ];
    }

    public function fetchOverallAssignmentDays(array $filters, ?User $user): float
    {
        $plannerQuery = Planner::query()
            ->whereNull('planners.deleted_at')
            ->whereHas('brief', function ($query) use ($user, $filters) {
                $query->accessibleToUser($user)
                    ->whereNull('briefs.deleted_at')
                    ->whereRaw('briefs.status != 15');
                DashboardFilters::applyBriefDashboardFilters($query, $filters, 'briefs');
            });

        return $this->calculateAverageAssignmentToSubmissionDays($plannerQuery);
    }

    private function calculateAverageAssignmentToSubmissionDays($plannerQuery): float
    {
        $submittedQuery = PlannerMetrics::applySubmittedPlansScope(clone $plannerQuery);
        $avgDays = $submittedQuery
            ->selectRaw('AVG(' . PlannerMetrics::assignmentToSubmissionDaysSql() . ') as avg_days')
            ->value('avg_days');

        return $avgDays ? round((float) $avgDays, 1) : 0;
    }
}
