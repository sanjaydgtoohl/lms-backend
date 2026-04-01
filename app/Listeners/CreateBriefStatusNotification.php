<?php

namespace App\Listeners;

use App\Events\BriefStatusChangedEvent;
use App\Services\NotificationService;
use App\Models\Brief;
use App\Models\User;

class CreateBriefStatusNotification
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function handle(BriefStatusChangedEvent $event)
    {
        $brief = Brief::find($event->briefId);
        if (!$brief) {
            return;
        }

        $notifiedUserIds = [];

        // Notify assigned user if not the updater
        if ($brief->assign_user_id && $brief->assign_user_id != $event->updatedByUserId) {
            $this->notificationService->createNotificationForNotifiable(
                User::class,
                $brief->assign_user_id,
                'brief_status_changed',
                [
                    'title' => 'Brief Status Updated',
                    'brief_id' => $brief->id,
                    'brief_name' => $brief->name,
                    'new_status' => $event->newStatusName,
                    'updated_by' => $event->updatedByUserName,
                    'updated_by_id' => $event->updatedByUserId,
                    'message' => 'Brief #' . $brief->id . ' ("' . $brief->name . '") status changed from "' . ($event->previousStatusName ?? 'N/A') . '" to "' . ($event->newStatusName ?? 'Unknown') . '" by ' . ($event->updatedByUserName ?? 'Unknown') . ' at ' . ($event->timestamp ?? now()) . '.',
                ]
            );
            $notifiedUserIds[] = $brief->assign_user_id;
        }

        // Notify updater if not already notified
        if ($event->updatedByUserId && !in_array($event->updatedByUserId, $notifiedUserIds)) {
            $this->notificationService->createNotificationForNotifiable(
                User::class,
                $event->updatedByUserId,
                'brief_status_changed',
                [
                    'title' => 'You updated a brief status',
                    'brief_id' => $brief->id,
                    'brief_name' => $brief->name,
                    'previous_status' => $event->previousStatusName,
                    'new_status' => $event->newStatusName,
                    'updated_by' => $event->updatedByUserName,
                    'updated_by_id' => $event->updatedByUserId,
                    'timestamp' => $event->timestamp,
                    'message' => 'You changed brief #' . $brief->id . ' ("' . $brief->name . '") status from "' . ($event->previousStatusName ?? 'N/A') . '" to "' . ($event->newStatusName ?? 'Unknown') . '" at ' . ($event->timestamp ?? now()) . '.',
                ]
            );
            $notifiedUserIds[] = $event->updatedByUserId;
        }

        // Optionally: Add logic here to notify other stakeholders (e.g., admins/managers) if needed
    }
}
