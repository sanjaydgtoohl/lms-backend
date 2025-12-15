<?php

namespace App\Services;

use App\Models\User;
use App\Contracts\Repositories\LeadRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Log;

class DashboardService
{
    /**
     * The lead repository instance
     *
     * @var LeadRepositoryInterface
     */
    protected LeadRepositoryInterface $leadRepository;

    /**
     * Constructor
     *
     * @param LeadRepositoryInterface $leadRepository
     */
    public function __construct(LeadRepositoryInterface $leadRepository)
    {
        $this->leadRepository = $leadRepository;
    }

    /**
     * Get dashboard data with total user count, pending leads count, team performance, and open alerts
     *
     * @return array
     * @throws Exception
     */
    public function getDashboardData(): array
    {
        try {
            return [
                'total_user_count' => $this->getTotalUserCount(),
                'pending_lead_count' => $this->getPendingLeadCount(),
                'team_performance' => null,
                'open_alerts' => null,
            ];
        } catch (Exception $e) {
            Log::error('Error fetching dashboard data', ['exception' => $e]);
            throw new Exception('Unable to fetch dashboard data');
        }
    }

    /**
     * Get total count of users
     *
     * @return int
     */
    public function getTotalUserCount(): int
    {
        try {
            return User::whereNull('deleted_at')->count();
        } catch (Exception $e) {
            Log::error('Error fetching total user count', ['exception' => $e]);
            return 0;
        }
    }

    /**
     * Get pending lead count using the pending API data
     *
     * @return int
     */
    public function getPendingLeadCount(): int
    {
        try {
            // Use the same logic as the pending API endpoint
            $pendingLeads = $this->leadRepository->getPendingLeads(1);
            return $pendingLeads->total();
        } catch (Exception $e) {
            Log::error('Error fetching pending lead count', ['exception' => $e]);
            return 0;
        }
    }
}
