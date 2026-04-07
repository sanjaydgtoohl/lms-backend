<?php

namespace App\Repositories;

use App\Contracts\Repositories\NotificationRepositoryInterface;
use App\Models\Notification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NotificationRepository extends BaseRepository implements NotificationRepositoryInterface
{
    /**
     * Return the class name of the model that will be used by the base repository.
     *
     * @return string
     */
    protected function getModelClass(): string
    {
        return Notification::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getNotificationsForNotifiable(string $notifiableType, $notifiableId, int $perPage = 10): LengthAwarePaginator
    {
        return Notification::where('notifiable_type', $notifiableType)
            ->where('notifiable_id', $notifiableId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());
    }

    /**
     * {@inheritdoc}
     */
    public function markAsReadForNotifiable(string $notifiableType, $notifiableId, $notificationId): bool
    {
        $notification = Notification::where('id', $notificationId)
            ->where('notifiable_type', $notifiableType)
            ->where('notifiable_id', $notifiableId)
            ->first();

        if (! $notification) {
            return false;
        }

        $notification->read_at = now();
        return (bool) $notification->save();
    }

    /**
     * {@inheritdoc}
     */
    public function markAllAsReadForNotifiable(string $notifiableType, $notifiableId): int
    {
        return Notification::where('notifiable_type', $notifiableType)
            ->where('notifiable_id', $notifiableId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnreadCountForNotifiable(string $notifiableType, $notifiableId): int
    {
        return Notification::where('notifiable_type', $notifiableType)
            ->where('notifiable_id', $notifiableId)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * {@inheritdoc}
     */
    public function findById($id): ?Notification
    {
        return Notification::find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getUnreadNotificationsForNotifiable(string $notifiableType, $notifiableId, int $perPage = 10)
    {
        return Notification::where('notifiable_type', $notifiableType)
            ->where('notifiable_id', $notifiableId)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends(request()->query());
    }

    /**
     * {@inheritdoc}
     */
    public function getLatestNotificationsForNotifiable(string $notifiableType, $notifiableId, int $limit = 5)
    {
        return Notification::where('notifiable_type', $notifiableType)
            ->where('notifiable_id', $notifiableId)
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteByIdForNotifiable(string $notifiableType, $notifiableId, $id): bool
    {
        $notification = Notification::where('id', $id)
            ->where('notifiable_type', $notifiableType)
            ->where('notifiable_id', $notifiableId)
            ->first();

        if (! $notification) {
            return false;
        }
        return (bool) $notification->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function clearAllForNotifiable(string $notifiableType, $notifiableId): int
    {
        return Notification::where('notifiable_type', $notifiableType)
            ->where('notifiable_id', $notifiableId)
            ->delete();
    }
}
