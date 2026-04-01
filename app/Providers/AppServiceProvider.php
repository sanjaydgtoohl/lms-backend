<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Services\RoleService;
use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Services\ResponseService;
use App\Services\PermissionService;
use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Services\UserService;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Repositories\UserParentRepositoryInterface;
use App\Repositories\NotificationRepository;
use App\Services\NotificationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Bind services
        $this->app->bind(RoleService::class, function ($app) {
            return new RoleService(
                $app->make(RoleRepositoryInterface::class),
                $app->make(ResponseService::class)
            );
        });

        // Bind PermissionService
        $this->app->bind(PermissionService::class, function ($app) {
            return new PermissionService(
                $app->make(PermissionRepositoryInterface::class),
                $app->make(ResponseService::class)
            );
        });

        // Bind UserService
        $this->app->bind(UserService::class, function ($app) {
            return new UserService(
                $app->make(UserRepositoryInterface::class),
                $app->make(UserParentRepositoryInterface::class)
            );
        });

        // Bind NotificationService
        $this->app->bind(NotificationService::class, function ($app) {
            return new NotificationService(
                $app->make(NotificationRepository::class),
                $app->make(ResponseService::class)
            );
        });
    }
    public function boot()
    {
        Schema::defaultStringLength(191);
    }
}
