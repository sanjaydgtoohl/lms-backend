<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Contracts\Repositories\IndustryRepositoryInterface;
use App\Repositories\IndustryRepository;

use App\Repositories\DesignationRepository;
use App\Contracts\Repositories\DesignationRepositoryInterface;

use App\Repositories\DepartmentRepository;
use App\Contracts\Repositories\DepartmentRepositoryInterface;

use App\Repositories\LeadSourceRepository;
use App\Contracts\Repositories\LeadSourceRepositoryInterface;

use App\Repositories\LeadSubSourceRepository;
use App\Contracts\Repositories\LeadSubSourceRepositoryInterface;

use App\Contracts\Repositories\AgencyGroupRepositoryInterface;

use App\Contracts\Repositories\AgencyTypeRepositoryInterface;

use App\Repositories\EloquentAgencyRepository;
use App\Contracts\Repositories\AgencyRepositoryInterface;

use App\Repositories\LocationRepository;
use App\Contracts\Repositories\LocationRepositoryInterface;

use App\Repositories\BrandTypeRepository;
use App\Contracts\Repositories\BrandTypeRepositoryInterface;

use App\Repositories\BrandRepository;
use App\Contracts\Repositories\BrandRepositoryInterface;

use App\Repositories\RegionRepository;
use App\Contracts\Repositories\RegionRepositoryInterface;

use App\Repositories\EloquentAgencyTypeRepository;

use App\Repositories\EloquentAgencyGroupRepository;

// Duplicate imports removed

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

        $this->app->bind(
            LeadSourceRepositoryInterface::class,
            LeadSourceRepository::class
        );

        $this->app->bind(AgencyRepositoryInterface::class, EloquentAgencyRepository::class);
        $this->app->bind(AgencyTypeRepositoryInterface::class, EloquentAgencyTypeRepository::class);
        $this->app->bind(AgencyGroupRepositoryInterface::class, EloquentAgencyGroupRepository::class);

        $this->app->bind(
            LocationRepositoryInterface::class,
            LocationRepository::class
        );

        $this->app->bind(
            BrandTypeRepositoryInterface::class,
            BrandTypeRepository::class
        );

        $this->app->bind(
            BrandRepositoryInterface::class,
            BrandRepository::class
        );

        $this->app->bind(
            RegionRepositoryInterface::class,
            RegionRepository::class
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