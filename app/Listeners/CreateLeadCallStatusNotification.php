<?php

namespace App\Listeners;

use App\Events\LeadCallStatusAddedEvent;
use App\Services\NotificationService;
use App\Models\Lead;
use App\Models\Status;
use App\Models\Priority;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Database\QueryException;

class CreateLeadCallStatusNotification
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function handle(LeadCallStatusAddedEvent $event)
    {
        try {
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
            $assignedUser = $lead->current_assign_user ? \App\Models\User::find($lead->current_assign_user) : null;
            if ($assignedUser && $assignedUser->id != $event->getUpdatedByUserId()) {
                $this->notificationService->createNotificationForNotifiable(
                    \App\Models\User::class,
                    $assignedUser->id,
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
                $notifiedUserIds[] = $assignedUser->id;
            }

            // Notify updater if not already notified
            if ($updater && !in_array($updater->id, $notifiedUserIds)) {
                $this->notificationService->createNotificationForNotifiable(
                    \App\Models\User::class,
                    $updater->id,
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
                $notifiedUserIds[] = $updater->id;
            }

            // Optionally: Add logic here to notify other stakeholders (e.g., admins/managers) if needed
        } catch (QueryException $e) {
            Log::error('Database error creating lead call status notification', [
                'lead_id' => $event->getLeadId(),
                'call_status_id' => $event->getCallStatusId(),
                'updated_by_user_id' => $event->getUpdatedByUserId(),
                'exception' => $e->getMessage()
            ]);
        } catch (Exception $e) {
            Log::error('Unexpected error creating lead call status notification', [
                'lead_id' => $event->getLeadId(),
                'call_status_id' => $event->getCallStatusId(),
                'updated_by_user_id' => $event->getUpdatedByUserId(),
                'exception' => $e->getMessage()
            ]);
        }
    }
}