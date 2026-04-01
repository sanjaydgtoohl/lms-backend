<?php

namespace App\Contracts\Repositories;

use App\Models\Notification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NotificationRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get a paginated list of notifications for a given notifiable (user, agency, etc.).
     *
     * @param string $notifiableType
     * @param int|string $notifiableId
     * @param int $perPage
     * @return LengthAwarePaginator<Notification>
     */
    public function getNotificationsForNotifiable(string $notifiableType, $notifiableId, int $perPage = 10): LengthAwarePaginator;

    /**
     * Mark a single notification as read.
     *
     * @param int|string $notificationId
     * @return bool
     */
    public function markAsRead($notificationId): bool;

    /**
     * Mark all notifications belonging to a notifiable as read.
     *
     * @param string $notifiableType
     * @param int|string $notifiableId
     * @return int  Number of records updated
     */
    public function markAllAsReadForNotifiable(string $notifiableType, $notifiableId): int;

    /**
     * Get unread notification count for a notifiable.
     *
     * @param string $notifiableType
     * @param int|string $notifiableId
     * @return int
     */
    public function getUnreadCountForNotifiable(string $notifiableType, $notifiableId): int;

    /**
     * Find a notification by its primary key.
     *
     * @param int|string $id
     * @return Notification|null
     */
    public function findById($id): ?Notification;

    /**
     * Get unread notifications for a given notifiable (paginated).
     *
     * @param string $notifiableType
     * @param int|string $notifiableId
     * @param int $perPage
     * @return LengthAwarePaginator<Notification>
     */
    public function getUnreadNotificationsForNotifiable(string $notifiableType, $notifiableId, int $perPage = 10);

    /**
     * Get latest notifications for a given notifiable (limited collection).
     *
     * @param string $notifiableType
     * @param int|string $notifiableId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection<Notification>
     */
    public function getLatestNotificationsForNotifiable(string $notifiableType, $notifiableId, int $limit = 5);

    /**
     * Delete a notification by its primary key.
     *
     * @param int|string $id
     * @return bool
     */
    public function deleteById($id): bool;

    /**
     * Delete all notifications belonging to a notifiable.
     *
     * @param string $notifiableType
     * @param int|string $notifiableId
     * @return int  Number of records removed
     */
    public function clearAllForNotifiable(string $notifiableType, $notifiableId): int;
}
