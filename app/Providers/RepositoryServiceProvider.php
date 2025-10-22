<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// --- Dono files ko import karein ---
use App\Contracts\Repositories\IndustryRepositoryInterface;
use App\Repositories\IndustryRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Yeh line Laravel/Lumen ko batati hai:
        // Jab bhi koi 'IndustryRepositoryInterface' maange...
        // ... toh use 'IndustryRepository' ka naya object do.
        $this->app->bind(
            IndustryRepositoryInterface::class,
            IndustryRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Is project ke liye boot() mein kuch nahi chahiye
    }
}