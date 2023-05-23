<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Eloquent\EventRepository;
use App\Repositories\Eloquent\InviteesRepository;
use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Contracts\InviteesRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bindIf(EventRepositoryInterface::class, EventRepository::class);
        $this->app->bindIf(InviteesRepositoryInterface::class, InviteesRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
