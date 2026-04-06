<?php

namespace App\Listeners;

use App\Events\LeadCallStatusAddedEvent;
use App\Services\NotificationService;
use App\Models\Lead;
use App\Models\Status;
use App\Models\Priority;

class CreateLeadCallStatusNotification
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function handle(LeadCallStatusAddedEvent $event)
    {
        $lead = Lead::find($event->getLeadId());
        if (!$lead) {
            return;
        }

        $callStatus = \App\Models\CallStatus::find($event->getCallStatusId());
        $prevCallStatus = $event->getPreviousCallStatusId() ? \App\Models\CallStatus::find($event->getPreviousCallStatusId()) : null;
        $status = $lead->lead_status ? Status::find($lead->lead_status) : null;
        $priority = $lead->priority_id ? Priority::find($lead->priority_id) : null;
        $updater = $event->getUpdatedByUserId() ? \App\Models\User::find($event->getUpdatedByUserId()) : null;
        $timestamp = $event->getTimestamp();

        $notifiedUserIds = [];

        // Notify assigned user if not the updater
        if ($lead->current_assign_user && $lead->current_assign_user != $event->getUpdatedByUserId()) {
            $this->notificationService->createNotificationForNotifiable(
                \App\Models\User::class,
                $lead->current_assign_user,
                'lead_call_status_added',
                [
                    'title' => 'Call Status Updated',
                    'lead_id' => $lead->id,
                    'previous_status' => $prevCallStatus ? $prevCallStatus->name : null,
                    'new_status' => $callStatus ? $callStatus->name : null,
                    'updated_by' => $updater ? $updater->name : null,
                    'updated_by_id' => $updater ? $updater->id : null,
                    'timestamp' => $timestamp,
                    'message' => 'Lead #' . $lead->id . ' call status changed from "' . ($prevCallStatus ? $prevCallStatus->name : 'N/A') . '" to "' . ($callStatus ? $callStatus->name : 'Unknown') . '" by ' . ($updater ? $updater->name : 'Unknown') . ' at ' . ($timestamp ? $timestamp : now()) . '.',
                    'name' => $lead->name,
                    'status_name' => $status ? $status->name : null,
                    'priority_name' => $priority ? $priority->name : null
                ]
            );
            $notifiedUserIds[] = $lead->current_assign_user;
        }

        // Notify updater if not already notified
        if ($event->getUpdatedByUserId() && !in_array($event->getUpdatedByUserId(), $notifiedUserIds)) {
            $this->notificationService->createNotificationForNotifiable(
                \App\Models\User::class,
                $event->getUpdatedByUserId(),
                'lead_call_status_added',
                [
                    'title' => 'You updated a lead call status',
                    'lead_id' => $lead->id,
                    'previous_status' => $prevCallStatus ? $prevCallStatus->name : null,
                    'new_status' => $callStatus ? $callStatus->name : null,
                    'updated_by' => $updater ? $updater->name : null,
                    'updated_by_id' => $updater ? $updater->id : null,
                    'timestamp' => $timestamp,
                    'message' => 'You changed lead #' . $lead->id . ' call status from "' . ($prevCallStatus ? $prevCallStatus->name : 'N/A') . '" to "' . ($callStatus ? $callStatus->name : 'Unknown') . '" at ' . ($timestamp ? $timestamp : now()) . '.',
                    'name' => $lead->name,
                    'status_name' => $status ? $status->name : null,
                    'priority_name' => $priority ? $priority->name : null
                ]
            );
            $notifiedUserIds[] = $event->getUpdatedByUserId();
        }

        // Optionally: Add logic here to notify other stakeholders (e.g., admins/managers) if needed
    }
}