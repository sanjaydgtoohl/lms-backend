<?php

namespace App\Listeners;

use App\Events\BriefAssignedEvent;
use App\Services\NotificationService;
use App\Models\User;
use App\Models\Brief;
use Illuminate\Support\Facades\Log;
use Exception;
use DomainException;
use Illuminate\Database\QueryException;

class CreateBriefAssignedNotification
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        
        $this->notificationService = $notificationService;
    }

    public function handle(BriefAssignedEvent $event)
    {
        try {
            // Fetch the brief with necessary relationships
            $brief = Brief::with(['assignedUser', 'brand', 'agency'])->find($event->getBriefId());

            if (!$brief) {
                Log::warning('Brief not found for notification creation', ['brief_id' => $event->getBriefId()]);
                return;
            }

            // Fetch the assigned user
            $user = User::find($event->getUserId());

            if (!$user) {
                Log::warning('User not found for notification creation', ['user_id' => $event->getUserId()]);
                return;
            }

            // Create the notification
            $this->notificationService->createNotificationForNotifiable(
                User::class,
                $event->getUserId(),
                'brief_assigned',
                [
                    'title' => 'Brief Assigned',
                    'message' => 'A new brief "' . ($brief->name ?? 'Unknown') . '" has been assigned to you.',
                    'brief_id' => $brief->id,
                    'budget' => $brief->budget ?? 0,
                    'assigned_at' => now()->format('Y-m-d H:i:s A'),
                ]
            );

            Log::info('Brief assigned notification created successfully', [
                'brief_id' => $event->getBriefId(),
                'user_id' => $event->getUserId()
            ]);

        } catch (QueryException $e) {
            Log::error('Database error creating brief assigned notification', [
                'brief_id' => $event->getBriefId(),
                'user_id' => $event->getUserId(),
                'exception' => $e->getMessage()
            ]);
        } catch (Exception $e) {
            Log::error('Unexpected error creating brief assigned notification', [
                'brief_id' => $event->getBriefId(),
                'user_id' => $event->getUserId(),
                'exception' => $e->getMessage()
            ]);
        }
    }
}