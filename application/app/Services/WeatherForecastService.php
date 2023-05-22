<?php

namespace App\Services;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\ClientInterface;
use App\Services\Contracts\WeatherForecastServiceInterface;

class WeatherForecastService implements WeatherForecastServiceInterface
{
    const WEATHER_CODES_FROM_TO = [
        0 => 'Clear Sky',
        1 => 'Mainly clear',
        2 => 'partly cloudy',
        3 => 'Overcast',
        45 => 'Fog',
        48 => 'Depositing rime fog',
        51 => 'Drizzle: light',
        53 => 'Drizzle: moderate',
        55 => 'Drizzle: dense',
        56 => 'Freezing Drizzle: light',
        57 => 'Freezing Drizzle: dense',
        61 => 'Rain: slight',
        63 => 'Rain: moderate ',
        65 => 'Rain: heavy',
        66 => 'Freezing Rain: light',
        67 => 'Freezing Rain: heavy',
        71 => 'Snow fall: slight',
        73 => 'Snow fall: moderate',
        75 => 'Snow fall: heavy',
        77 => 'Snow grains',
        80 => 'Rain showers: slight',
        81 => 'Rain showers: moderate',
        82 => 'Rain showers: violent',
        85 => 'Snow showers: slight',
        86 => 'Snow showers: heavy',
        95 => 'Thunderstorm: slight or moderate',
        96 => 'Thunderstorm with slight and heavy hail',
        99 => 'Thunderstorm with slight and heavy hail',
    ];

    const TIMEZONE = 'Europe%2FLondon';
    const GEOLOCATION_BASE_URI = 'https://geocoding-api.open-meteo.com/v1/search';
    const WEATHERFORECAST_BASE_URI = 'https://api.open-meteo.com/v1/forecast';
    const REQUEST_DEFAULT_TIMEOUT = 5;

    protected ClientInterface $guzzleClient;

    public function __construct(ClientInterface $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * Shouldn't throw exceptions (Events listing shouldn't depend on external services availability)
     *
     * @param string $location
     * @param string $date
     * @return array|null
     */
    public function getForecastByLocationAndDate(string $location, string $date): array|null
    {
        try {
            $coordinates = $this->getGeocodingByLocation($location);

            $date = (new \DateTime($date))->format('Y-m-d');
            $request = $this->buildForecastRequest($coordinates['latitude'], $coordinates['longitude'], $date);
            $response = $this->guzzleClient->send($request, ['timeout' => self::REQUEST_DEFAULT_TIMEOUT]);

            $contents = json_decode($response->getBody()->getContents())->daily;

            return [
                'description' => $this::WEATHER_CODES_FROM_TO[$contents->weathercode[0]] ?? 'no description available',
                'temperature' => [
                    'min' => $contents->temperature_2m_min[0],
                    'max' => $contents->temperature_2m_max[0],
                ],
                'precipitation_chance' => $contents->precipitation_probability_max[0] ?? 0,
            ];
        } catch (\Throwable $exception) {
            // logging, etc..
            // can be broken in separated catches (RequestException for example might provide further insights)
            return null;
        }
    }

    public function fillLocationsWithWeatherForecasts(array &$locations)
    {
        foreach ($locations as $location => &$locationData) {
            $coordinates = $this->getGeocodingByLocation($location, 1);

            foreach ($locationData['dates'] as &$eventDate) {
                try {
                    $request = $this->buildForecastRequest(
                        $coordinates['latitude'],
                        $coordinates['longitude'],
                        $eventDate['event_date']
                    );

                    $response = $this->guzzleClient->send($request, [
                        'timeout' => self::REQUEST_DEFAULT_TIMEOUT,
                        'delay' => 1
                    ]);

                    $contents = json_decode($response->getBody()->getContents())->daily;

                    $eventDate['weather_forecast'] = [
                        'description' => $this::WEATHER_CODES_FROM_TO[$contents->weathercode[0]] ?? 'no description available',
                        'temperature' => [
                            'min' => $contents->temperature_2m_min[0],
                            'max' => $contents->temperature_2m_max[0],
                        ],
                        'precipitation_chance' => $contents->precipitation_probability_max[0] ?? 0,
                    ];
                } catch (\Throwable $exception) {
                    $eventDate['weather_forecast'] = 'weather forecast unavailable';
                }
            }
        }
    }

    public function getGeocodingByLocation(string $location, ?int $delay = 0): array
    {
        $locationParams = explode(',', $location);

        $count = 1;
        $language = 'en';
        $format = 'json';

        $request = $this->buildGeocodingRequest($locationParams[0], $locationParams[1], $count, $language, $format);
        $response =
            $this->guzzleClient->send($request, ['timeout' => self::REQUEST_DEFAULT_TIMEOUT, 'delay' => $delay]);

        $contents = json_decode($response->getBody()->getContents())->results[0];

        return [
            'latitude' => $contents->latitude,
            'longitude' => $contents->longitude,
        ];
    }

    public function buildGeocodingRequest(
        string $city,
        string $countryCode,
        int $count,
        string $language,
        string $format
    ): Request {
        $params = "?name=$city&country_code=$countryCode&count=$count&language=$language&format=$format";

        return new Request('GET', self::GEOLOCATION_BASE_URI . $params);
    }

    public function buildForecastRequest(mixed $latitude, mixed $longitude, string $date): Request
    {
        $dailyParams = 'weathercode,temperature_2m_max,temperature_2m_min,precipitation_probability_max';

        $urlParams = "?latitude=$latitude&longitude=$longitude&daily=$dailyParams&start_date=$date&end_date=$date";
        $uri = $urlParams . "&timezone=" . self::TIMEZONE;

        return new Request('GET', self::WEATHERFORECAST_BASE_URI . $uri);
    }
}
