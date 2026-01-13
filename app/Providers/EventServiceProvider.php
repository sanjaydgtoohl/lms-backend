<?php

namespace App\Providers;

use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;
use App\Observers\ActivityLogObserver;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \App\Events\ExampleEvent::class => [
            \App\Listeners\ExampleListener::class,
        ],
        // Log successful login events to the login_logs table
        \Illuminate\Auth\Events\Login::class => [
            \App\Listeners\LogSuccessfulLogin::class,
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        // Register the ActivityLogObserver on models to track changes
        \App\Models\Agency::observe(ActivityLogObserver::class);
        \App\Models\Brand::observe(ActivityLogObserver::class);
        \App\Models\Lead::observe(ActivityLogObserver::class);
        \App\Models\Brief::observe(ActivityLogObserver::class);
        \App\Models\User::observe(ActivityLogObserver::class);
        \App\Models\Department::observe(ActivityLogObserver::class);
        \App\Models\Designation::observe(ActivityLogObserver::class);
        \App\Models\Role::observe(ActivityLogObserver::class);
        \App\Models\Permission::observe(ActivityLogObserver::class);
        \App\Models\MissCampaign::observe(ActivityLogObserver::class);
        \App\Models\Industry::observe(ActivityLogObserver::class);
        \App\Models\LeadSubSource::observe(ActivityLogObserver::class);
        \App\Models\Meeting::observe(ActivityLogObserver::class);
        \App\Models\Team::observe(ActivityLogObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
