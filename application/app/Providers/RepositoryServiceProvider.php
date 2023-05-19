<?php

namespace App\Providers;

use App\Services\EventService;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Eloquent\EventRepository;
use App\Repositories\Contracts\CrudRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->when(EventService::class)
            ->needs(CrudRepositoryInterface::class)
            ->give(function () {
                return new EventRepository();
            });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
