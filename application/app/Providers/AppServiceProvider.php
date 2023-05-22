<?php

namespace App\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\ServiceProvider;
use App\Services\WeatherForecastService;
use App\Services\Contracts\WeatherForecastServiceInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bindIf(WeatherForecastServiceInterface::class, WeatherForecastService::class);
        $this->app->bindIf(ClientInterface::class, Client::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
