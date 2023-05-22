<?php

namespace App\Services\Contracts;

use GuzzleHttp\Psr7\Request;

interface WeatherForecastServiceInterface
{
    public function getForecastByLocationAndDate(string $location, string $date): array|null;

    public function getGeocodingByLocation(string $location, ?int $delay): array;

    public function buildGeocodingRequest(
        string $city,
        string $countryCode,
        int $count,
        string $language,
        string $format
    ): Request;

    public function buildForecastRequest(mixed $latitude, mixed $longitude, string $date): Request;
}
