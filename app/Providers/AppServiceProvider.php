<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Services\RoleService;
use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Services\ResponseService;
use App\Services\PermissionService;
use App\Contracts\Repositories\PermissionRepositoryInterface;

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
        
    }
    public function boot()
    {
        Schema::defaultStringLength(191);
    }
}
