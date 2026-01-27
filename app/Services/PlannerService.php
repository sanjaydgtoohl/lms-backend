<?php

namespace App\Services;

use App\Models\Planner;
use App\Repositories\PlannerRepository;
use App\Traits\HandlesFileUploads;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class PlannerService
{
    use HandlesFileUploads;

    /**
     * @var PlannerRepository
     */
    protected PlannerRepository $plannerRepository;

    /**
     * Create a new PlannerService instance.
     *
     * @param PlannerRepository $plannerRepository
     */
    public function __construct(PlannerRepository $plannerRepository)
    {
        $this->plannerRepository = $plannerRepository;
    }

    /**
     * Get planners by filters.
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPlannersByFilters(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return $this->plannerRepository->getAllPlanners($perPage, $filters);
    }

    /**
     * Get planner by ID.
     *
     * @param int $id
     * @return Planner|null
     */
    public function getPlannerById(int $id): ?Planner
    {
        return $this->plannerRepository->getPlannerById($id);
    }

    /**
     * Get planners by brief ID.
     *
     * @param int $briefId
     * @param int $perPage
     * @param string|null $status
     * @return LengthAwarePaginator
     */
    public function getPlannersByBriefId(int $briefId, int $perPage = 10, ?string $status = null): LengthAwarePaginator
    {
        return $this->plannerRepository->getPlannersByBriefId($briefId, $perPage, $status);
    }

    /**
     * Create a new planner.
     *
     * @param array $data
     * @param int $createdBy
     * @return Planner
     * @throws Throwable
     */
    public function createPlanner(array $data, int $createdBy): Planner
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $data['created_by'] = $createdBy;
            $data['status'] = $data['status'] ?? '1';
            $data['uuid'] = Str::uuid();

            // Handle submitted plan files
            if (isset($data['submitted_plan']) && is_array($data['submitted_plan'])) {
                $uploadedFiles = [];
                foreach ($data['submitted_plan'] as $file) {
                    if ($file instanceof UploadedFile) {
                        $fileData = $this->uploadFile(
                            $file,
                            'document',
                            'public/planners/submitted-plans'
                        );
                        // Store only the path in database
                        $uploadedFiles[] = $fileData['path'];
                    }
                }
                $data['submitted_plan'] = !empty($uploadedFiles) ? $uploadedFiles : null;
            } else {
                $data['submitted_plan'] = null;
            }

            // Handle backup plan file
            if (isset($data['backup_plan']) && $data['backup_plan'] instanceof UploadedFile) {
                $uploadedFile = $this->uploadFile(
                    $data['backup_plan'],
                    'document',
                    'public/planners/backup-plans'
                );
                // Store only the path in database
                $data['backup_plan'] = $uploadedFile['path'];
            } else {
                $data['backup_plan'] = null;
            }

            return $this->plannerRepository->createPlanner($data);
        });
    }

    /**
     * Update planner.
     *
     * @param int $id
     * @param array $data
     * @return Planner|null
     * @throws Throwable
     */
    public function updatePlanner(int $id, array $data): ?Planner
    {
        return DB::transaction(function () use ($id, $data) {
            $planner = $this->plannerRepository->getPlannerById($id);

            if (!$planner) {
                return null;
            }

            // Handle submitted plan files
            if (isset($data['submitted_plan']) && is_array($data['submitted_plan'])) {
                $uploadedFiles = [];
                foreach ($data['submitted_plan'] as $file) {
                    if ($file instanceof UploadedFile) {
                        $fileData = $this->uploadFile(
                            $file,
                            'document',
                            'planners/submitted-plans'
                        );
                        // Store only the path in database
                        $uploadedFiles[] = $fileData['path'];
                    }
                }
                $data['submitted_plan'] = !empty($uploadedFiles) ? $uploadedFiles : null;
            } elseif (!isset($data['submitted_plan'])) {
                unset($data['submitted_plan']);
            }

            // Handle backup plan file
            if (isset($data['backup_plan'])) {
                if ($data['backup_plan'] instanceof UploadedFile) {
                    $uploadedFile = $this->uploadFile(
                        $data['backup_plan'],
                        'document',
                        'planners/backup-plans'
                    );
                    $data['backup_plan'] = $uploadedFile['path'];
                }
            } else {
                unset($data['backup_plan']);
            }

            return $this->plannerRepository->updatePlanner($id, $data);
        });
    }

    /**
     * Delete planner.
     *
     * @param int $id
     * @return bool
     * @throws Throwable
     */
    public function deletePlanner(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $planner = $this->plannerRepository->getPlannerById($id);

            if (!$planner) {
                return false;
            }

            // Delete files if needed (optional - based on your storage strategy)
            // $this->deleteFiles($planner);

            return $this->plannerRepository->deletePlanner($id);
        });
    }

    /**
     * Add submitted plan files.
     *
     * @param int $id
     * @param array $files
     * @return Planner|null
     * @throws Throwable
     */
    public function addSubmittedPlanFiles(int $id, array $files): ?Planner
    {
        return DB::transaction(function () use ($id, $files) {
            $planner = $this->plannerRepository->getPlannerById($id);

            if (!$planner) {
                return null;
            }

            $uploadedFiles = [];
            foreach ($files as $file) {
                if ($file instanceof UploadedFile) {
                    $fileData = $this->uploadFile(
                        $file,
                        'document',
                        'planners/submitted-plans'
                    );
                    // Store only the path in database
                    $uploadedFiles[] = $fileData['path'];
                }
            }

            // Add to existing submitted plans
            $existingPlans = $planner->submitted_plan ?? [];
            if (!is_array($existingPlans)) {
                $existingPlans = [];
            }

            $allPlans = array_merge($existingPlans, $uploadedFiles);
            // Limit to 2 files
            $allPlans = array_slice($allPlans, 0, 2);

            $planner->update(['submitted_plan' => $allPlans]);

            return $planner->refresh()->load(['brief', 'creator']);
        });
    }

    /**
     * Upload backup plan file.
     *
     * @param int $id
     * @param UploadedFile $file
     * @return Planner|null
     * @throws Throwable
     */
    public function uploadBackupPlanFile(int $id, UploadedFile $file): ?Planner
    {
        return DB::transaction(function () use ($id, $file) {
            $planner = $this->plannerRepository->getPlannerById($id);

            if (!$planner) {
                return null;
            }

            $uploadedFile = $this->uploadFile(
                $file,
                'document',
                'planners/backup-plans'
            );

            $planner->update(['backup_plan' => $uploadedFile['path']]);

            return $planner->refresh()->load(['brief', 'creator']);
        });
    }

    /**
     * Get active planners.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getActivePlanners(int $perPage = 10): LengthAwarePaginator
    {
        return $this->plannerRepository->getActivePlanners($perPage);
    }

    /**
     * Count planners by brief.
     *
     * @param int $briefId
     * @return int
     */
    public function countPlannersByBrief(int $briefId): int
    {
        return $this->plannerRepository->countByBriefId($briefId);
    }

    /**
     * Update planner status.
     *
     * @param int $id
     * @param int $plannerStatusId
     * @return Planner|null
     */
    public function updatePlannerStatus(int $id, int $plannerStatusId): ?Planner
    {
        return DB::transaction(function () use ($id, $plannerStatusId) {
            $planner = $this->plannerRepository->getPlannerById($id);

            if (!$planner) {
                return null;
            }

            $planner->update(['planner_status_id' => $plannerStatusId]);

            return $planner->refresh()->load(['brief', 'creator', 'plannerStatus']);
        });
    }
}
