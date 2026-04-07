<?php

namespace App\Services;

use App\Contracts\Repositories\NotificationRepositoryInterface;
use App\Models\Notification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    protected NotificationRepositoryInterface $repository;
    protected ResponseService $responseService;

    public function __construct(
        NotificationRepositoryInterface $repository,
        ResponseService $responseService
    ) {
        $this->repository = $repository;
        $this->responseService = $responseService;
    }

    /**
     * Fetch notifications for the given notifiable.
     *
     * @param string $type
     * @param mixed $id
     * @param int $perPage
     * @return LengthAwarePaginator<Notification>
     */
    public function getNotificationsForNotifiable(string $type, $id, int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->getNotificationsForNotifiable($type, $id, $perPage);
    }

    /**
     * Convenience helper for currently authenticated user.
     */
    public function getNotificationsForCurrentUser(int $perPage = 10): LengthAwarePaginator
    {
        $user = Auth::user();
        return $this->getNotificationsForNotifiable(get_class($user), $user->id, $perPage);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead($id): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return $this->repository->markAsReadForNotifiable(get_class($user), $user->id, $id);
    }

    /**
     * Delete a notification by its primary key.
     */
    public function deleteNotification($id): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return $this->repository->deleteByIdForNotifiable(get_class($user), $user->id, $id);
    }

    /**
     * Mark all notifications as read for specified notifiable.
     */
    public function markAllAsReadForNotifiable(string $type, $id): int
    {
        return $this->repository->markAllAsReadForNotifiable($type, $id);
    }

    /**
     * Mark all notifications as read for current user.
     */
    public function markAllAsReadForCurrentUser(): int
    {
        $user = Auth::user();
        return $this->markAllAsReadForNotifiable(get_class($user), $user->id);
    }

    /**
     * Get unread count for given notifiable.
     */
    public function getUnreadCountForNotifiable(string $type, $id): int
    {
        return $this->repository->getUnreadCountForNotifiable($type, $id);
    }

    /**
     * Get unread count for current user.
     */
    public function getUnreadCountForCurrentUser(): int
    {
        $user = Auth::user();
        return $this->getUnreadCountForNotifiable(get_class($user), $user->id);
    }

    /**
     * Find notification by primary key for the current user.
     */
    public function findById($id): ?Notification
    {
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        return $this->repository->findById(get_class($user), $user->id, $id);
    }

    /**
     * Create a notification record for the given notifiable.
     *
     * @param string $notifiableType
     * @param int|string $notifiableId
     * @param string $type
     * @param array $data
     * @return Notification
     */
    public function createNotificationForNotifiable(string $notifiableType, $notifiableId, string $type, array $data): Notification
    {
        $notification = $this->repository->create([
            'type' => $type,
            'notifiable_type' => $notifiableType,
            'notifiable_id' => $notifiableId,
            'data' => $data,
        ]);

        return $notification;
    }

    /**
     * Convenience helper to send a notification to an Eloquent model instance.
     */
    public function sendToNotifiable($notifiable, string $type, array $data): Notification
    {
        $class = get_class($notifiable);
        $id = $notifiable->id;
        // createNotificationForNotifiable dispatches event already
        return $this->createNotificationForNotifiable($class, $id, $type, $data);
    }

    /**
     * Send a notification to multiple notifiables.
     *
     * @param iterable $notifiables  Collection or array of models
     * @param string $type
     * @param array $data
     * @return \Illuminate\Support\Collection<Notification>
     */
    public function sendToMany(iterable $notifiables, string $type, array $data)
    {
        $created = collect();
        foreach ($notifiables as $notifiable) {
            $created->push($this->sendToNotifiable($notifiable, $type, $data));
        }
        return $created;
    }

    /**
     * Get unread notifications for the given notifiable.
     */
    public function getUnreadNotificationsForNotifiable(string $type, $id, int $perPage = 10)
    {
        return $this->repository->getUnreadNotificationsForNotifiable($type, $id, $perPage);
    }

    /**
     * Convenience helper for currently authenticated user.
     */
    public function getUnreadNotificationsForCurrentUser(int $perPage = 10)
    {
        $user = Auth::user();
        return $this->getUnreadNotificationsForNotifiable(get_class($user), $user->id, $perPage);
    }

    /**
     * Get latest notifications (limit) for the given notifiable.
     */
    public function getLatestNotificationsForNotifiable(string $type, $id, int $limit = 5)
    {
        return $this->repository->getLatestNotificationsForNotifiable($type, $id, $limit);
    }

    /**
     * Convenience helper for currently authenticated user.
     */
    public function getLatestNotificationsForCurrentUser(int $limit = 5)
    {
        $user = Auth::user();
        return $this->getLatestNotificationsForNotifiable(get_class($user), $user->id, $limit);
    }

    /**
     * Clear (delete) all notifications for a given notifiable.
     *
     * Returns number of records deleted.
     */
    public function clearAllNotificationsForNotifiable(string $type, $id): int
    {
        return $this->repository->clearAllForNotifiable($type, $id);
    }

    /**
     * Clear (delete) all notifications for the current user.
     */
    public function clearAllNotificationsForCurrentUser(): int
    {
        $user = Auth::user();
        return $this->clearAllNotificationsForNotifiable(get_class($user), $user->id);
    }
}
