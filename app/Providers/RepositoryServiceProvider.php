<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Contracts\Repositories\IndustryRepositoryInterface;
use App\Repositories\IndustryRepository;

use App\Interfaces\DesignationRepositoryInterface;
use App\Repositories\DesignationRepository;

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