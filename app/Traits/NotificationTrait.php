<?php

namespace App\Traits;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Helper methods for models that can receive notifications.
 *
 * Usage: add `use NotificationTrait;` to any Eloquent model (e.g. User).
 */
trait NotificationTrait
{
    /**
     * MorphMany relation to the notifications table.
     */
    public function notifications(): MorphMany
    {
        /** @var \Illuminate\Database\Eloquent\Model $this */
        return $this->morphMany(Notification::class, 'notifiable');
    }

    /**
     * Get the notification service instance from the container.
     */
    protected function notificationService(): NotificationService
    {
        return App::make(NotificationService::class);
    }

    /**
     * Add a notification for this model.
     *
     * @param string $type  The class or identifier of the notification
     * @param array  $data  Arbitrary payload to store
     */
    public function notify(string $type, array $data): Notification
    {
        return $this->notificationService()->createNotificationForNotifiable(
            get_class($this),
            $this->getKey(),
            $type,
            $data
        );
    }

    /**
     * Mark all of this model's unread notifications as read.
     *
     * @return int  number of records updated
     */
    public function markAllNotificationsAsRead(): int
    {
        return $this->notificationService()->markAllAsReadForNotifiable(
            get_class($this),
            $this->getKey()
        );
    }

    /**
     * Remove every notification belonging to this model.
     *
     * @return int number of rows deleted
     */
    public function clearAllNotifications(): int
    {
        return $this->notificationService()->clearAllNotificationsForNotifiable(
            get_class($this),
            $this->getKey()
        );
    }

    /**
     * Get count of unread notifications for this model.
     */
    public function unreadNotificationCount(): int
    {
        return $this->notificationService()->getUnreadCountForNotifiable(
            get_class($this),
            $this->getKey()
        );
    }

    /**
     * Get paginated unread notifications for this model.
     */
    public function unreadNotifications(int $perPage = 10)
    {
        return $this->notificationService()->getUnreadNotificationsForNotifiable(
            get_class($this),
            $this->getKey(),
            $perPage
        );
    }

    /**
     * Get a limited collection of latest notifications for this model.
     */
    public function latestNotifications(int $limit = 5)
    {
        return $this->notificationService()->getLatestNotificationsForNotifiable(
            get_class($this),
            $this->getKey(),
            $limit
        );
    }
}
