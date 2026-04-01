<?php

namespace Tests;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;

class NotificationTest extends TestCase
{
    protected string $token;
    protected int $userId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->make('config')->set('database.default', 'sqlite');
        $this->app->make('config')->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $this->app->make('config')->set('jwt.secret', 'testingsecret');
        $this->app->make('config')->set('jwt.required_claims', [
            'iat','exp','nbf','sub','jti'
        ]);
        putenv('JWT_SECRET=testingsecret');
        $_ENV['JWT_SECRET'] = 'testingsecret';

        // run all migrations to ensure lead and related tables exist
        $this->artisan('migrate');

        // still run notification migration explicitly in case it comes later
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_01_28_175848_create_notifications_table.php']);

        // create a user and store JWT token
        $email = 'notif_' . uniqid() . '@example.com';
        $payload = [
            'name' => 'Notif User',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
        $this->post('/api/v1/auth/register', $payload);
        $this->seeStatusCode(201);
        $body = json_decode($this->response->getContent(), true);
        $this->token = $body['data']['token'];
        $this->userId = User::first()->id;
    }

    protected function authGet(string $uri)
    {
        return $this->get($uri, ['Authorization' => "Bearer {$this->token}"]);
    }

    protected function authPost(string $uri, array $data = [])
    {
        return $this->post($uri, $data, ['Authorization' => "Bearer {$this->token}"]);
    }

    protected function authPut(string $uri, array $data = [])
    {
        return $this->put($uri, $data, ['Authorization' => "Bearer {$this->token}"]);
    }

    protected function authDelete(string $uri)
    {
        return $this->delete($uri, [], ['Authorization' => "Bearer {$this->token}"]);
    }

    public function test_unread_notifications_list_and_count()
    {
        // ensure empty initially
        $this->authGet('/api/v1/notifications/unread');
        $this->seeStatusCode(200);
        $data = json_decode($this->response->getContent(), true)['data'];
        $this->assertEmpty($data);

        // trait helpers on User should also agree
        $user = User::find($this->userId);
        $this->assertEquals(0, $user->unreadNotificationCount());
        $this->assertTrue($user->notifications()->count() === 0);

        // fake events so we can assert dispatches
        Event::fake();

        // create notifications using trait as well (should fire event)
        $user->notify('App\\Notifications\\Test', ['message' => 'first']);

        Notification::create([
            'type' => 'App\\Notifications\\Test',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->userId,
            'data' => json_encode(['message' => 'second']),
        ]);

        // manually trigger event for the manual create since it bypasses service
        event(new \App\Events\NotificationCreated(Notification::latest()->first()));

        Event::assertDispatched(\App\Events\NotificationCreated::class);

        $this->authGet('/api/v1/notifications/unread-count');
        $this->seeStatusCode(200);
        $count = json_decode($this->response->getContent(), true)['data']['unread_count'];
        $this->assertEquals(2, $count);

        $this->authGet('/api/v1/notifications/unread');
        $this->seeStatusCode(200);
        $payload = json_decode($this->response->getContent(), true);
        $this->assertArrayHasKey('data', $payload);
        $this->assertCount(2, $payload['data']);
    }

    public function test_mark_single_notification_as_read_and_delete()
    {
        // add a notification
        $notification = Notification::create([
            'type' => 'App\\Notifications\\Test',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->userId,
            'data' => json_encode(['message' => 'hello']),
        ]);

        $this->authPost("/api/v1/notifications/{$notification->id}/read");

        // trait markAllNotificationsAsRead should affect count
        $user = User::find($this->userId);
        $user->markAllNotificationsAsRead();

        $this->seeStatusCode(200);

        $this->authGet('/api/v1/notifications/unread-count');
        $body = json_decode($this->response->getContent(), true);
        $this->assertEquals(0, $body['data']['unread_count']);

        // delete the notification
        $this->authDelete("/api/v1/notifications/{$notification->id}");
        $this->seeStatusCode(200);

        // ensure not found afterwards
        $this->authDelete("/api/v1/notifications/{$notification->id}");
        $this->seeStatusCode(404);
    }

    public function test_clear_all_notifications()
    {
        $user = User::find($this->userId);
        $user->notify('App\\Notifications\\Test', ['msg' => 'a']);
        $user->notify('App\\Notifications\\Test', ['msg' => 'b']);

        $this->authDelete('/api/v1/notifications/clear-all');
        $this->seeStatusCode(200);
        $payload = json_decode($this->response->getContent(), true);
        $this->assertEquals(2, $payload['data']['deleted']);

        // trait helper should produce same result (nothing left to clear)
        $this->assertEquals(0, $user->clearAllNotifications());

        $this->authGet('/api/v1/notifications/unread-count');
        $body = json_decode($this->response->getContent(), true);
        $this->assertEquals(0, $body['data']['unread_count']);
    }

    public function test_lead_assignment_triggers_notification()
    {
        // create a new lead and assign to the same user via API
        $leadData = ['name' => 'API Test Lead', 'created_by' => $this->userId];
        // insert directly into DB
        $lead = \App\Models\Lead::create($leadData);

        $this->authPost("/api/v1/leads/{$lead->id}/assign", ['user_id' => $this->userId]);
        $this->seeStatusCode(200);

        // unread count should be 1 now
        $this->authGet('/api/v1/notifications/unread-count');
        $body = json_decode($this->response->getContent(), true);
        $this->assertEquals(1, $body['data']['unread_count']);

        // verify latest notification contents
        $this->authGet('/api/v1/notifications/latest');
        $this->seeStatusCode(200);
        $latest = json_decode($this->response->getContent(), true)['data'][0];
        $this->assertStringContainsString('assigned a new lead', $latest['data']['message']);
    }

    public function test_call_status_update_triggers_notification()
    {
        // create a lead and assign it to the current user so there is someone to notify
        $lead = \App\Models\Lead::create(['name' => 'Status Test Lead', 'created_by' => $this->userId, 'current_assign_user' => $this->userId]);

        // create a simple call status record
        $callStatus = \App\Models\CallStatus::create(["name" => "Follow Up", "slug" => "follow-up", "status" => 1]);

        // perform the API call to add call status
        $this->authPut("/api/v1/leads/{$lead->id}/call-status", ['call_status_id' => $callStatus->id]);
        $this->seeStatusCode(200);

        // unread count should now be 1 (notification from call status update)
        $this->authGet('/api/v1/notifications/unread-count');
        $body = json_decode($this->response->getContent(), true);
        $this->assertEquals(1, $body['data']['unread_count']);

        // verify the notification message contains the lead name and status name
        $this->authGet('/api/v1/notifications/latest');
        $this->seeStatusCode(200);
        $latest = json_decode($this->response->getContent(), true)['data'][0];
        $this->assertStringContainsString('Status Test Lead', $latest['data']['message']);
        $this->assertStringContainsString('Follow Up', $latest['data']['message']);
    }
}
