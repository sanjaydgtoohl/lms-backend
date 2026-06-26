<?php

namespace App\Services;

use App\Models\User;
use App\Models\Lead;
use App\Models\Brief;
use App\Models\Planner;
use App\Models\MissCampaign;
use App\Models\Organisation;
use App\Contracts\Repositories\LeadRepositoryInterface;
use App\Support\DashboardFilters;
use App\Support\PlannerMetrics;
use App\Support\UserAccessScope;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardService
{
    protected LeadRepositoryInterface $leadRepository;

    public function __construct(LeadRepositoryInterface $leadRepository)
    {
        $this->leadRepository = $leadRepository;
    }

    /**
     * @param array<string, mixed> $filters
     * @throws Exception
     */
    public function getDashboardData(array $filters = []): array
    {
        try {
            return [
                'total_user_count' => $this->getTotalUserCount($filters),
                'pending_lead_count' => $this->getPendingLeadCount($filters),
                'team_performance' => null,
                'open_alerts' => null,
            ];
        } catch (Exception $e) {
            Log::error('Error fetching dashboard data', ['exception' => $e]);
            throw new Exception('Unable to fetch dashboard data');
        }
    }

    /**
     * @param array<string, mixed> $filters
     * @throws Exception
     */
    public function getChartMetrics(array $filters = []): array
    {
        try {
            $user = Auth::user();

            if (!$user) {
                throw new Exception('User not authenticated');
            }

            if (
                empty($filters['organisation_ids'])
                && !UserAccessScope::isSuperAdmin($user)
                && empty(UserAccessScope::getAccessibleOrganisationIds($user))
            ) {
                return $this->buildAggregateChartMetrics($user, $filters);
            }

            $organisationsQuery = Organisation::query()->orderBy('name');
            if (!empty($filters['organisation_ids'])) {
                $organisationsQuery->whereIn('id', $filters['organisation_ids']);
            } elseif (!UserAccessScope::isSuperAdmin($user)) {
                $accessibleOrgIds = UserAccessScope::getAccessibleOrganisationIds($user);
                if (!empty($accessibleOrgIds)) {
                    $organisationsQuery->whereIn('id', $accessibleOrgIds);
                }
            }

            $organisations = $organisationsQuery->get(['id', 'name']);
            $rows = [];

            foreach ($organisations as $organisation) {
                $organisationFilter = array_merge($filters, [
                    'organisation_ids' => [(int) $organisation->id],
                ]);

                $rows[] = $this->buildOrganisationChartRow($user, $organisation->id, $organisation->name, $organisationFilter);
            }

            if ($rows === [] && !UserAccessScope::isSuperAdmin($user)) {
                return $this->buildAggregateChartMetrics($user, $filters);
            }

            return [
                'by_organisation' => $rows,
                'totals' => [
                    'total_leads' => array_sum(array_column($rows, 'total_leads')),
                    'pre_leads' => array_sum(array_column($rows, 'pre_leads')),
                    'briefs' => array_sum(array_column($rows, 'briefs')),
                    'brief_budget' => array_sum(array_column($rows, 'brief_budget')),
                ],
            ];
        } catch (Exception $e) {
            Log::error('Error fetching dashboard chart metrics', ['exception' => $e]);
            throw new Exception('Unable to fetch dashboard chart metrics');
        }
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    private function buildAggregateChartMetrics($user, array $filters): array
    {
        $row = $this->buildOrganisationChartRow($user, 0, 'My Data', $filters);

        return [
            'by_organisation' => [$row],
            'totals' => [
                'total_leads' => $row['total_leads'],
                'pre_leads' => $row['pre_leads'],
                'briefs' => $row['briefs'],
                'brief_budget' => $row['brief_budget'],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    private function buildOrganisationChartRow($user, int $organisationId, string $organisationName, array $filters): array
    {
        $leadsQuery = Lead::query()
            ->accessibleToUser($user)
            ->whereNull('deleted_at');
        DashboardFilters::applyLeadDashboardFilters($leadsQuery, $filters);

        $preLeadsQuery = MissCampaign::query()
            ->accessibleToUser($user)
            ->notDeleted()
            ->where('miss_campaigns.status', '1');
        DashboardFilters::applyMissCampaignDashboardFilters($preLeadsQuery, $filters);

        $briefsQuery = Brief::query()
            ->accessibleToUser($user)
            ->whereNull('deleted_at')
            ->whereRaw('briefs.status != 15');
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

    /**
     * @param array<string, mixed> $filters
     */
    public function getTotalUserCount(array $filters = []): int
    {
        try {
            $query = User::query()->whereNull('deleted_at');
            DashboardFilters::applyUserOrganisationFilter($query, $filters);
            DashboardFilters::applyDateFilter($query, $filters, 'created_at');

            return $query->count();
        } catch (Exception $e) {
            Log::error('Error fetching total user count', ['exception' => $e]);
            return 0;
        }
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function getPendingLeadCount(array $filters = []): int
    {
        try {
            $pendingLeads = $this->leadRepository->getPendingLeads(1, $filters);
            return $pendingLeads->total();
        } catch (Exception $e) {
            Log::error('Error fetching pending lead count', ['exception' => $e]);
            return 0;
        }
    }

    /**
     * Sales dashboard charts: organisation metrics + lead pipeline.
     *
     * @param array<string, mixed> $filters
     * @throws Exception
     */
    public function getSalesChartMetrics(array $filters = []): array
    {
        try {
            $user = Auth::user();
            $charts = $this->getChartMetrics($filters);

            $leadQuery = Lead::query()
                ->accessibleToUser($user)
                ->whereNull('deleted_at');
            DashboardFilters::applyLeadDashboardFilters($leadQuery, $filters, 'leads');

            $briefQuery = Brief::query()
                ->accessibleToUser($user)
                ->whereNull('deleted_at')
                ->whereRaw('briefs.status != 15');
            DashboardFilters::applyBriefDashboardFilters($briefQuery, $filters, 'briefs');

            $byOrganisation = array_map(static function (array $row) {
                return [
                    'organisation_id' => $row['organisation_id'],
                    'organisation_name' => $row['organisation_name'],
                    'total_leads' => $row['total_leads'],
                    'briefs' => $row['briefs'],
                    'brief_budget' => $row['brief_budget'],
                ];
            }, $charts['by_organisation']);

            return [
                'by_organisation' => $byOrganisation,
                'totals' => [
                    'total_leads' => $charts['totals']['total_leads'],
                    'briefs' => $charts['totals']['briefs'],
                    'brief_budget' => $charts['totals']['brief_budget'],
                ],
                'pipeline' => [
                    'new_leads' => (int) (clone $leadQuery)->count(),
                    'follow_up' => (int) (clone $leadQuery)->whereHas('callStatusRelation', function ($query) {
                        $query->where('slug', 'follow-up');
                    })->count(),
                    'meeting_scheduled' => (int) (clone $leadQuery)->whereHas('callStatusRelation', function ($query) {
                        $query->where('slug', 'meeting-schedule');
                    })->count(),
                    'briefs' => (int) (clone $briefQuery)->count(),
                ],
            ];
        } catch (Exception $e) {
            Log::error('Error fetching sales dashboard chart metrics', ['exception' => $e]);
            throw new Exception('Unable to fetch sales dashboard chart metrics');
        }
    }

    /**
     * Planner dashboard charts: organisation brief metrics + brief status breakdown.
     *
     * @param array<string, mixed> $filters
     * @throws Exception
     */
    public function getPlannerChartMetrics(array $filters = []): array
    {
        try {
            $user = Auth::user();
            $charts = $this->getChartMetrics($filters);
            $plannerRows = $this->getPlannerOrganisationMetrics($filters);
            $plannerByOrgId = collect($plannerRows)->keyBy('organisation_id');

            $briefQuery = Brief::query()
                ->accessibleToUser($user)
                ->whereNull('deleted_at')
                ->whereRaw('briefs.status != 15');
            DashboardFilters::applyBriefDashboardFilters($briefQuery, $filters, 'briefs');

            $byOrganisation = array_map(static function (array $row) use ($plannerByOrgId) {
                $planner = $plannerByOrgId->get($row['organisation_id'], []);

                return [
                    'organisation_id' => $row['organisation_id'],
                    'organisation_name' => $row['organisation_name'],
                    'briefs' => $row['briefs'],
                    'brief_budget' => $row['brief_budget'],
                    'assigned_plans' => (int) ($planner['assigned_plans'] ?? 0),
                    'avg_assignment_days' => (float) ($planner['avg_assignment_days'] ?? 0),
                ];
            }, $charts['by_organisation']);

            $overallAvgAssignmentDays = $this->calculateOverallAssignmentDays($user, $filters);

            return [
                'by_organisation' => $byOrganisation,
                'totals' => [
                    'briefs' => $charts['totals']['briefs'],
                    'brief_budget' => $charts['totals']['brief_budget'],
                    'assigned_plans' => (int) array_sum(array_column($byOrganisation, 'assigned_plans')),
                    'avg_assignment_days' => $overallAvgAssignmentDays,
                ],
                'brief_status' => [
                    'active_briefs' => (int) (clone $briefQuery)->whereDate('submission_date', '>=', now())->count(),
                    'closed_briefs' => (int) (clone $briefQuery)->whereHas('briefStatus', function ($query) {
                        $query->where('slug', 'closed');
                    })->count(),
                    'overdue_briefs' => (int) (clone $briefQuery)->where('submission_date', '<', now())->count(),
                ],
            ];
        } catch (Exception $e) {
            Log::error('Error fetching planner dashboard chart metrics', ['exception' => $e]);
            throw new Exception('Unable to fetch planner dashboard chart metrics');
        }
    }

    /**
     * @param array<string, mixed> $filters
     * @return list<array<string, mixed>>
     */
    private function getPlannerOrganisationMetrics(array $filters): array
    {
        $user = Auth::user();

        if (!$user) {
            return [];
        }

        if (
            empty($filters['organisation_ids'])
            && !UserAccessScope::isSuperAdmin($user)
            && empty(UserAccessScope::getAccessibleOrganisationIds($user))
        ) {
            return [$this->buildOrganisationPlannerRow($user, 0, 'My Data', $filters)];
        }

        $organisationsQuery = Organisation::query()->orderBy('name');
        if (!empty($filters['organisation_ids'])) {
            $organisationsQuery->whereIn('id', $filters['organisation_ids']);
        } elseif (!UserAccessScope::isSuperAdmin($user)) {
            $accessibleOrgIds = UserAccessScope::getAccessibleOrganisationIds($user);
            if (!empty($accessibleOrgIds)) {
                $organisationsQuery->whereIn('id', $accessibleOrgIds);
            }
        }

        $rows = [];
        foreach ($organisationsQuery->get(['id', 'name']) as $organisation) {
            $organisationFilter = array_merge($filters, [
                'organisation_ids' => [(int) $organisation->id],
            ]);
            $rows[] = $this->buildOrganisationPlannerRow(
                $user,
                (int) $organisation->id,
                (string) $organisation->name,
                $organisationFilter
            );
        }

        if ($rows === [] && !UserAccessScope::isSuperAdmin($user)) {
            return [$this->buildOrganisationPlannerRow($user, 0, 'My Data', $filters)];
        }

        return $rows;
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    private function buildOrganisationPlannerRow($user, int $organisationId, string $organisationName, array $filters): array
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
        $avgAssignmentDays = self::calculateAverageAssignmentToSubmissionDays($plannerQuery);

        return [
            'organisation_id' => $organisationId,
            'organisation_name' => $organisationName,
            'assigned_plans' => $assignedPlans,
            'avg_assignment_days' => $avgAssignmentDays ? round((float) $avgAssignmentDays, 1) : 0,
        ];
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function calculateOverallAssignmentDays($user, array $filters): float
    {
        $plannerQuery = Planner::query()
            ->whereNull('planners.deleted_at')
            ->whereHas('brief', function ($query) use ($user, $filters) {
                $query->accessibleToUser($user)
                    ->whereNull('briefs.deleted_at')
                    ->whereRaw('briefs.status != 15');
                DashboardFilters::applyBriefDashboardFilters($query, $filters, 'briefs');
            });

        return self::calculateAverageAssignmentToSubmissionDays($plannerQuery);
    }

    /**
     * Average days from plan assignment (planner created) to plan submission.
     *
     * @param \Illuminate\Database\Eloquent\Builder $plannerQuery
     */
    private static function calculateAverageAssignmentToSubmissionDays($plannerQuery): float
    {
        $submittedQuery = PlannerMetrics::applySubmittedPlansScope(clone $plannerQuery);
        $avgDays = $submittedQuery
            ->selectRaw('AVG(' . PlannerMetrics::assignmentToSubmissionDaysSql() . ') as avg_days')
            ->value('avg_days');

        return $avgDays ? round((float) $avgDays, 1) : 0;
    }
}
