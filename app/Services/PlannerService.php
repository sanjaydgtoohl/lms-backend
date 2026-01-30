<?php

namespace App\Services;

use App\Models\Planner;
use App\Repositories\PlannerRepository;
use App\Traits\HandlesFileUploads;
use DomainException;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
     * @throws DomainException
     */
    public function getPlannersByFilters(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        try {
            return $this->plannerRepository->getAllPlanners($perPage, $filters);
        } catch (QueryException $e) {
            Log::error('Database error fetching planners by filters', ['exception' => $e, 'filters' => $filters]);
            throw new DomainException('Database error while fetching planners.');
        } catch (Exception $e) {
            Log::error('Unexpected error fetching planners by filters', ['exception' => $e, 'filters' => $filters]);
            throw new DomainException('Unexpected error while fetching planners.');
        }
    }

    /**
     * Get planner by ID.
     *
     * @param int $id
     * @return Planner|null
     * @throws DomainException
     */
    public function getPlannerById(int $id): ?Planner
    {
        try {
            return $this->plannerRepository->getPlannerById($id);
        } catch (QueryException $e) {
            Log::error('Database error fetching planner', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Database error while fetching planner.');
        } catch (Exception $e) {
            Log::error('Unexpected error fetching planner', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Unexpected error while fetching planner.');
        }
    }

    /**
     * Get planners by brief ID.
     *
     * @param int $briefId
     * @param int $perPage
     * @param string|null $status
     * @return LengthAwarePaginator
     * @throws DomainException
     */
    public function getPlannersByBriefId(int $briefId, int $perPage = 10, ?string $status = null): LengthAwarePaginator
    {
        try {
            return $this->plannerRepository->getPlannersByBriefId($briefId, $perPage, $status);
        } catch (QueryException $e) {
            Log::error('Database error fetching planners by brief', ['briefId' => $briefId, 'exception' => $e]);
            throw new DomainException('Database error while fetching planners for this brief.');
        } catch (Exception $e) {
            Log::error('Unexpected error fetching planners by brief', ['briefId' => $briefId, 'exception' => $e]);
            throw new DomainException('Unexpected error while fetching planners for this brief.');
        }
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
        try {
            return DB::transaction(function () use ($data, $createdBy) {
                try {
                    $data['created_by'] = $createdBy;
                    $data['status'] = $data['status'] ?? '1';
                    $data['uuid'] = Str::uuid();

                    // Handle submitted plan files
                    if (isset($data['submitted_plan']) && is_array($data['submitted_plan'])) {
                        try {
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
                        } catch (Exception $e) {
                            Log::error('Error uploading submitted plan files', ['exception' => $e]);
                            throw new DomainException('Failed to upload submitted plan files.');
                        }
                    } else {
                        $data['submitted_plan'] = null;
                    }

                    // Handle backup plan file
                    if (isset($data['backup_plan']) && $data['backup_plan'] instanceof UploadedFile) {
                        try {
                            $uploadedFile = $this->uploadFile(
                                $data['backup_plan'],
                                'document',
                                'planners/backup-plans'
                            );
                            // Store only the path in database
                            $data['backup_plan'] = $uploadedFile['path'];
                        } catch (Exception $e) {
                            Log::error('Error uploading backup plan file', ['exception' => $e]);
                            throw new DomainException('Failed to upload backup plan file.');
                        }
                    } else {
                        $data['backup_plan'] = null;
                    }

                    return $this->plannerRepository->createPlanner($data);
                } catch (QueryException $e) {
                    Log::error('Database error creating planner', ['exception' => $e, 'data' => $data]);
                    throw new DomainException('Database error while creating planner.');
                }
            });
        } catch (DomainException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Unexpected error creating planner', ['exception' => $e]);
            throw new DomainException('Unexpected error while creating planner.');
        }
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
        try {
            return DB::transaction(function () use ($id, $data) {
                try {
                    $planner = $this->plannerRepository->getPlannerById($id);

                    if (!$planner) {
                        Log::warning('Planner not found for update', ['id' => $id]);
                        return null;
                    }

                    // Handle submitted plan files
                    if (isset($data['submitted_plan']) && is_array($data['submitted_plan'])) {
                        try {
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
                        } catch (Exception $e) {
                            Log::error('Error uploading submitted plan files during update', ['id' => $id, 'exception' => $e]);
                            throw new DomainException('Failed to upload submitted plan files.');
                        }
                    } elseif (!isset($data['submitted_plan'])) {
                        unset($data['submitted_plan']);
                    }

                    // Handle backup plan file
                    if (isset($data['backup_plan'])) {
                        if ($data['backup_plan'] instanceof UploadedFile) {
                            try {
                                $uploadedFile = $this->uploadFile(
                                    $data['backup_plan'],
                                    'document',
                                    'planners/backup-plans'
                                );
                                $data['backup_plan'] = $uploadedFile['path'];
                            } catch (Exception $e) {
                                Log::error('Error uploading backup plan file during update', ['id' => $id, 'exception' => $e]);
                                throw new DomainException('Failed to upload backup plan file.');
                            }
                        }
                    } else {
                        unset($data['backup_plan']);
                    }

                    return $this->plannerRepository->updatePlanner($id, $data);
                } catch (QueryException $e) {
                    Log::error('Database error updating planner', ['id' => $id, 'exception' => $e]);
                    throw new DomainException('Database error while updating planner.');
                }
            });
        } catch (DomainException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Unexpected error updating planner', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Unexpected error while updating planner.');
        }
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
        try {
            return DB::transaction(function () use ($id) {
                try {
                    $planner = $this->plannerRepository->getPlannerById($id);

                    if (!$planner) {
                        Log::warning('Planner not found for deletion', ['id' => $id]);
                        return false;
                    }

                    // Delete files if needed (optional - based on your storage strategy)
                    // $this->deleteFiles($planner);

                    return $this->plannerRepository->deletePlanner($id);
                } catch (QueryException $e) {
                    Log::error('Database error deleting planner', ['id' => $id, 'exception' => $e]);
                    throw new DomainException('Database error while deleting planner.');
                }
            });
        } catch (DomainException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Unexpected error deleting planner', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Unexpected error while deleting planner.');
        }
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
        try {
            return DB::transaction(function () use ($id, $files) {
                try {
                    $planner = $this->plannerRepository->getPlannerById($id);

                    if (!$planner) {
                        Log::warning('Planner not found for adding files', ['id' => $id]);
                        return null;
                    }

                    try {
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
                    } catch (Exception $e) {
                        Log::error('Error uploading submitted plan files', ['id' => $id, 'exception' => $e]);
                        throw new DomainException('Failed to upload submitted plan files.');
                    }
                } catch (QueryException $e) {
                    Log::error('Database error adding submitted plan files', ['id' => $id, 'exception' => $e]);
                    throw new DomainException('Database error while adding submitted plan files.');
                }
            });
        } catch (DomainException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Unexpected error adding submitted plan files', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Unexpected error while adding submitted plan files.');
        }
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
        try {
            return DB::transaction(function () use ($id, $file) {
                try {
                    $planner = $this->plannerRepository->getPlannerById($id);

                    if (!$planner) {
                        Log::warning('Planner not found for uploading backup plan', ['id' => $id]);
                        return null;
                    }

                    try {
                        $uploadedFile = $this->uploadFile(
                            $file,
                            'document',
                            'planners/backup-plans'
                        );

                        $planner->update(['backup_plan' => $uploadedFile['path']]);

                        return $planner->refresh()->load(['brief', 'creator']);
                    } catch (Exception $e) {
                        Log::error('Error uploading backup plan file', ['id' => $id, 'exception' => $e]);
                        throw new DomainException('Failed to upload backup plan file.');
                    }
                } catch (QueryException $e) {
                    Log::error('Database error uploading backup plan', ['id' => $id, 'exception' => $e]);
                    throw new DomainException('Database error while uploading backup plan file.');
                }
            });
        } catch (DomainException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Unexpected error uploading backup plan file', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Unexpected error while uploading backup plan file.');
        }
    }

    /**
     * Get active planners.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     * @throws DomainException
     */
    public function getActivePlanners(int $perPage = 10): LengthAwarePaginator
    {
        try {
            return $this->plannerRepository->getActivePlanners($perPage);
        } catch (QueryException $e) {
            Log::error('Database error fetching active planners', ['exception' => $e]);
            throw new DomainException('Database error while fetching active planners.');
        } catch (Exception $e) {
            Log::error('Unexpected error fetching active planners', ['exception' => $e]);
            throw new DomainException('Unexpected error while fetching active planners.');
        }
    }

    /**
     * Count planners by brief.
     *
     * @param int $briefId
     * @return int
     * @throws DomainException
     */
    public function countPlannersByBrief(int $briefId): int
    {
        try {
            return $this->plannerRepository->countByBriefId($briefId);
        } catch (QueryException $e) {
            Log::error('Database error counting planners by brief', ['briefId' => $briefId, 'exception' => $e]);
            throw new DomainException('Database error while counting planners.');
        } catch (Exception $e) {
            Log::error('Unexpected error counting planners by brief', ['briefId' => $briefId, 'exception' => $e]);
            throw new DomainException('Unexpected error while counting planners.');
        }
    }

    /**
     * Update planner status.
     *
     * @param int $id
     * @param int $plannerStatusId
     * @return Planner|null
     * @throws Throwable
     */
    public function updatePlannerStatus(int $id, int $plannerStatusId): ?Planner
    {
        try {
            return DB::transaction(function () use ($id, $plannerStatusId) {
                try {
                    $planner = $this->plannerRepository->getPlannerById($id);

                    if (!$planner) {
                        Log::warning('Planner not found for status update', ['id' => $id]);
                        return null;
                    }

                    $planner->update(['planner_status_id' => $plannerStatusId]);

                    return $planner->refresh()->load(['brief', 'creator', 'plannerStatus']);
                } catch (QueryException $e) {
                    Log::error('Database error updating planner status', ['id' => $id, 'statusId' => $plannerStatusId, 'exception' => $e]);
                    throw new DomainException('Database error while updating planner status.');
                }
            });
        } catch (DomainException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Unexpected error updating planner status', ['id' => $id, 'exception' => $e]);
            throw new DomainException('Unexpected error while updating planner status.');
        }
    }
}

