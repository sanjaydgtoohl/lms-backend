<?php

namespace App\Listeners;

use App\Events\LeadStatusChangedEvent;
use App\Contracts\Repositories\NotificationRepositoryInterface;
use App\Models\User;
use App\Models\Lead;
use App\Models\Status;

class CreateLeadStatusNotification
{
    protected $notificationRepository;

    public function __construct(NotificationRepositoryInterface $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function handle(LeadStatusChangedEvent $event)
    {
        $lead = Lead::find($event->leadId);

        if (!$lead || !$lead->current_assign_user) {
            return;
        }

        $status = Status::find($event->status);

        $this->notificationRepository->create([
            'type' => 'lead_status_changed',
            'notifiable_type' => User::class,
            'notifiable_id' => $lead->current_assign_user,
            'data' => [
                'title' => 'Lead Status Updated',
                'message' => 'Lead status has been updated to "' . ($status ? $status->name : 'Unknown') . '" for lead "' . $lead->name . '".',
                'name' => $lead->name,
                'status_name' => $status ? $status->name : 'Unknown'
            ]
        ]);
    }
}