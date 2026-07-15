<?php

namespace App\Services;

use App\Contracts\Repositories\LeadRepositoryInterface;
use App\Contracts\Repositories\DashboardRepositoryInterface;
use App\Support\UserAccessScope;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardService
{
    protected LeadRepositoryInterface $leadRepository;
    protected DashboardRepositoryInterface $dashboardRepository;

    public function __construct(
        LeadRepositoryInterface $leadRepository,
        DashboardRepositoryInterface $dashboardRepository
    ) {
        $this->leadRepository = $leadRepository;
        $this->dashboardRepository = $dashboardRepository;
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
                && empty(UserAccessScope::getAccessibleOrganisationIds($user))
            ) {
                return $this->buildAggregateChartMetrics($user, $filters);
            }

            $organisations = $this->dashboardRepository->getAccessibleOrganisations($filters, $user);
            $rows = [];

            foreach ($organisations as $organisation) {
                $organisationFilter = array_merge($filters, [
                    'organisation_ids' => [(int) $organisation->id],
                ]);

                $rows[] = $this->buildOrganisationChartRow($user, $organisation->id, $organisation->name, $organisationFilter);
            }

            if ($rows === [] && empty(UserAccessScope::getAccessibleOrganisationIds($user))) {
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
        return $this->dashboardRepository->getOrganisationChartRow($filters, $user, $organisationId, $organisationName);
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function getTotalUserCount(array $filters = []): int
    {
        try {
            $user = Auth::user();
            return $this->dashboardRepository->getTotalUserCount($filters, $user);
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
            $pipelineCounts = $this->dashboardRepository->getSalesPipelineCounts($filters, $user);

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
                'pipeline' => $pipelineCounts,
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

            $briefStatusCounts = $this->dashboardRepository->getPlannerBriefStatusCounts($filters, $user);

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

            $overallAvgAssignmentDays = $this->dashboardRepository->getOverallAssignmentDays($filters, $user);

            return [
                'by_organisation' => $byOrganisation,
                'totals' => [
                    'briefs' => $charts['totals']['briefs'],
                    'brief_budget' => $charts['totals']['brief_budget'],
                    'assigned_plans' => (int) array_sum(array_column($byOrganisation, 'assigned_plans')),
                    'avg_assignment_days' => $overallAvgAssignmentDays,
                ],
                'brief_status' => $briefStatusCounts,
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
            && empty(UserAccessScope::getAccessibleOrganisationIds($user))
        ) {
            return [$this->dashboardRepository->getPlannerOrganisationRow($filters, $user, 0, 'My Data')];
        }

        $organisations = $this->dashboardRepository->getAccessibleOrganisations($filters, $user);
        $rows = [];
        foreach ($organisations as $organisation) {
            $organisationFilter = array_merge($filters, [
                'organisation_ids' => [(int) $organisation->id],
            ]);
            $rows[] = $this->dashboardRepository->getPlannerOrganisationRow(
                $organisationFilter,
                $user,
                (int) $organisation->id,
                (string) $organisation->name
            );
        }

        if ($rows === [] && empty(UserAccessScope::getAccessibleOrganisationIds($user))) {
            return [$this->dashboardRepository->getPlannerOrganisationRow($filters, $user, 0, 'My Data')];
        }

        return $rows;
    }
}
