<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use App\Services\WeatherForecastService;

class WeatherForecastServiceTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function testCorrectlyGettingForecastByLocationAndDate()
    {
        $geoCodingResponse = '{
                "results": [
                    {
                        "id": 3448439,
                        "name": "São Paulo",
                        "latitude": -23.5475,
                        "longitude": -46.63611,
                        "elevation": 769.0,
                        "feature_code": "PPLA",
                        "country_code": "BR",
                        "admin1_id": 3448433,
                        "timezone": "America/Sao_Paulo",
                        "population": 10021295,
                        "country_id": 3469034,
                        "country": "Brazil",
                        "admin1": "São Paulo"
                    }
                ],
                "generationtime_ms": 0.6990433
                }';

        $forecastResponse = '{
                    "latitude": -23.5,
                    "longitude": -46.5,
                    "generationtime_ms": 0.2950429916381836,
                    "utc_offset_seconds": -10800,
                    "timezone": "America/Sao_Paulo",
                    "timezone_abbreviation": "-03",
                    "elevation": 745.0,
                    "daily_units": {
                        "time": "iso8601",
                        "weathercode": "wmo code",
                        "temperature_2m_max": "°C",
                        "temperature_2m_min": "°C",
                        "precipitation_probability_max": "%"
                    },
                    "daily": {
                        "time": [
                            "2023-06-06"
                        ],
                        "weathercode": [
                            0
                        ],
                        "temperature_2m_max": [
                            22.9
                        ],
                        "temperature_2m_min": [
                            19.6
                        ],
                        "precipitation_probability_max": [
                            null
                        ]
                    }
                }';

        $guzzleMock = new MockHandler([
            new Response(200, [], $geoCodingResponse),
            new Response(200, [], $forecastResponse),
        ]);

        $handler = HandlerStack::create($guzzleMock);
        $client = new Client(['handler' => $handler]);
//        $client = new Client();

        $weatherForecastService = new WeatherForecastService($client);

        $location = 'Sao Paulo,BR';
        $date = '2023-06-06';
        $result = $weatherForecastService->getForecastByLocationAndDate($location, $date);

        $expected = [
            'description' => WeatherForecastService::WEATHER_CODES_FROM_TO[0],
            'temperature' => [
                'min' => 19.6,
                'max' => 22.9,
            ],
            'precipitation_chance' => 0,
        ];

        $this->assertIsArray($result);
        $this->assertEquals($expected, $result);
    }

    public function testCorrectlyGettingGeocodingByLocation()
    {
        $response = '{
                "results": [
                    {
                        "id": 3448439,
                        "name": "São Paulo",
                        "latitude": -23.5475,
                        "longitude": -46.63611,
                        "elevation": 769.0,
                        "feature_code": "PPLA",
                        "country_code": "BR",
                        "admin1_id": 3448433,
                        "timezone": "America/Sao_Paulo",
                        "population": 10021295,
                        "country_id": 3469034,
                        "country": "Brazil",
                        "admin1": "São Paulo"
                    }
                ],
                "generationtime_ms": 0.6990433
                }';

        $guzzleMock = new MockHandler([
            new Response(200, [], $response),
        ]);

        $handler = HandlerStack::create($guzzleMock);
        $client = new Client(['handler' => $handler]);
//        $client = new Client();

        $weatherForecastService = new WeatherForecastService($client);

        $location = 'Sao Paulo,BR';
        $result = $weatherForecastService->getGeocodingByLocation($location);

        $expected = ['-23.5475', '-46.63611'];

        $this->assertIsArray($result);
        $this->assertEquals($expected, $result);
    }
}
