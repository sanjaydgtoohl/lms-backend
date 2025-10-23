<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Contracts\Repositories\IndustryRepositoryInterface;
use App\Repositories\IndustryRepository;

use App\Repositories\DesignationRepository;
use App\Contracts\Repositories\DesignationRepositoryInterface;

use App\Repositories\DepartmentRepository;
use App\Contracts\Repositories\DepartmentRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            IndustryRepositoryInterface::class,
            IndustryRepository::class
        );

        $this->app->bind(
            DesignationRepositoryInterface::class,
            DesignationRepository::class
        );

        $this->app->bind(
            DepartmentRepositoryInterface::class,
            DepartmentRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        
    }
}