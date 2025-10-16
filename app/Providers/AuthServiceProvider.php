<?php

namespace App\Providers;

use App\Models\User;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Bind repository interfaces to implementations
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Define gates for authorization
        Gate::define('manage-users', function (User $user) {
            return $user->hasPermission('users:create') || 
                   $user->hasPermission('users:update') || 
                   $user->hasPermission('users:delete');
        });

        Gate::define('manage-roles', function (User $user) {
            return $user->hasPermission('roles:create') || 
                   $user->hasPermission('roles:update') || 
                   $user->hasPermission('roles:delete');
        });

        Gate::define('manage-permissions', function (User $user) {
            return $user->hasPermission('permissions:create') || 
                   $user->hasPermission('permissions:update') || 
                   $user->hasPermission('permissions:delete');
        });

        Gate::define('admin-access', function (User $user) {
            return $user->hasPermission('admin:access');
        });

        // Define role-based gates
        Gate::define('is-admin', function (User $user) {
            return $user->hasRole('admin');
        });

        Gate::define('is-moderator', function (User $user) {
            return $user->hasRole('moderator');
        });

        Gate::define('is-user', function (User $user) {
            return $user->hasRole('user');
        });
    }
}
