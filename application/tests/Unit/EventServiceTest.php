<?php

declare(strict_types=1);

namespace Tests\Unit;

use DateTime;
use Tests\TestCase;
use App\DTOs\EventDTO;
use App\Models\EventModel;
use Mockery\MockInterface;
use App\Services\EventService;
use App\DTOs\WeatherForecastDTO;
use App\Services\WeatherForecastService;
use App\Repositories\Eloquent\EventRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Eloquent\InviteesRepository;

class EventServiceTest extends TestCase
{
    public function testGetById()
    {
        $repositoryResponse = [
            'id' => '1',
            'user_id' => '10',
            'location' => 'Berlin',
            'date' => '2023-06-01 10:00:00',
            'created_at' => (new DateTime())->format('Y-m-d H:i:s'),
            'invitees' => [],
        ];

        $weatherServiceResponse = [
            'description' => 'Clear sky',
            'temperature' => [
                'min' => 10,
                'max' => 21,
            ],
            'precipitation_chance' => 15,
        ];

        $this->mock(EventRepository::class, function (MockInterface $mock) use ($repositoryResponse) {
            $mock->shouldReceive('findByIdWithInvitees')
                ->once()
                ->andReturn($repositoryResponse);
        });

        $this->mock(WeatherForecastService::class, function (MockInterface $mock) use ($weatherServiceResponse) {
            $mock->shouldReceive('getForecastByLocationAndDate')
                ->once()
                ->andReturn($weatherServiceResponse);
        });

        $eventDto = app(EventService::class)->getById(1);

        $this->assertInstanceOf(EventDTO::class, $eventDto);
        $this->assertInstanceOf(WeatherForecastDTO::class, $eventDto->weatherForecastDTO);

        $this->assertEquals($repositoryResponse['id'], $eventDto->id);
        $this->assertEquals($repositoryResponse['user_id'], $eventDto->userId);
        $this->assertEquals($repositoryResponse['location'], $eventDto->location);
        $this->assertEquals($repositoryResponse['date'], $eventDto->date);

        $weatherForecastDto = $eventDto->weatherForecastDTO;
        $this->assertEquals($weatherServiceResponse['description'], $weatherForecastDto->description);
        $this->assertEquals($weatherServiceResponse['temperature']['min'], $weatherForecastDto->temperature['min']);
        $this->assertEquals($weatherServiceResponse['temperature']['max'], $weatherForecastDto->temperature['max']);
        $this->assertEquals($weatherServiceResponse['precipitation_chance'], $weatherForecastDto->precipitationChance);
    }

    public function testGetAll()
    {
        $event1 = [
            'id' => '1',
            'user_id' => '10',
            'location' => 'Berlin',
            'date' => '2023-06-01 10:00:00',
            'created_at' => (new DateTime())->format('Y-m-d H:i:s'),
            'invitees' => [],
        ];

        $event2 = [
            'id' => '2',
            'user_id' => '11',
            'location' => 'London',
            'date' => '2023-06-01 10:00:00',
            'created_at' => (new DateTime())->format('Y-m-d H:i:s'),
            'invitees' => [],
        ];

        $eventModel1 = new EventModel($event1);
        $eventModel2 = new EventModel($event2);
        $eventRepositoryResponse = collect([$eventModel1, $eventModel2]);

        $weatherServiceResponses[] = [
            'description' => 'Clear sky',
            'temperature' => [
                'min' => 10,
                'max' => 21,
            ],
            'precipitation_chance' => 15,
        ];

        $weatherServiceResponses[] = [
            'description' => 'Mainly clear',
            'temperature' => [
                'min' => 15,
                'max' => 27,
            ],
            'precipitation_chance' => 3,
        ];

        $this->mock(EventRepository::class, function (MockInterface $mock) use ($eventRepositoryResponse) {
            $response = new LengthAwarePaginator($eventRepositoryResponse, '2', '10');
            $mock->shouldReceive('fetchAllEventsPaginated')
                ->once()
                ->andReturn($response);
        });

        $this->mock(WeatherForecastService::class, function (MockInterface $mock) use ($weatherServiceResponses) {
            $mock->shouldReceive('getForecastByLocationAndDate')
                ->andReturn($weatherServiceResponses[0])
                ->andReturn($weatherServiceResponses[1]);
        });

        $eventServiceResponse = app(EventService::class)->getAll();

        $this->assertInstanceOf(LengthAwarePaginator::class, $eventServiceResponse);

        foreach ($eventServiceResponse as $eventReturned){
            $this->assertInstanceOf(EventDTO::class, $eventReturned);
            $this->assertInstanceOf(WeatherForecastDTO::class, $eventReturned->weatherForecastDTO);
        }
    }

    public function testCreate()
    {
        $eventDTO = new EventDTO(
            10,
            '2023-06-01 10:00:00',
            'Berlin,DE',
            [
                'user@test.com',
                'example@test.com',
            ]
        );

        $this->mock(EventRepository::class, function (MockInterface $mock) {
            $returnValues = [
                'id' => '1',
                'user_id' => '10',
                'location' => 'Berlin,DE',
                'date' => '2023-06-01 10:00:00',
                'created_at' => (new DateTime())->format('Y-m-d H:i:s'),
                'invitees' => [
                    'user@test.com',
                    'example@test.com',
                ],
            ];

            $mock->shouldReceive('insert')
                ->once()
                ->andReturn($returnValues);
        });

        $this->mock(InviteesRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('insertMany')
                ->once()
                ->andReturnTrue();
        });

        $returnedDTO = app(EventService::class)->create($eventDTO);

        $this->assertInstanceOf(EventDTO::class, $returnedDTO);
        $this->assertEquals($eventDTO->userId, $returnedDTO->userId);
        $this->assertEquals($eventDTO->date, $returnedDTO->date);
        $this->assertEquals($eventDTO->location, $returnedDTO->location);
    }

    public function testUpdate()
    {
        $updatePayload = [
            'id' => 10,
            'user_id' => 1,
            'location' => 'Osasco,BR',
        ];

        $eventDTO = EventDTO::fromUpdateRequest($updatePayload);

        $this->mock(EventRepository::class, function (MockInterface $mock) {
            $returnValues = [
                'id' => 10,
                'user_id' => 1,
                'location' => 'Barueri,BR',
                'date' => '2023-06-01 10:00:00',
                'created_at' => '2023-06-01 10:00:00',
                'invitees' => [
                    [
                        'id' => 1,
                        'event_id' => 10,
                        'email' => 'example@test.com',
                        'created_at' => '2023-05-23 10:00:00',
                    ],
                ],
            ];

            $mock->shouldReceive('findByIdWithInvitees')
                ->once()
                ->andReturn($returnValues);

            $mock->shouldReceive('update')
                ->once()
                ->andReturnTrue();
        });

        $returnedDTO = app(EventService::class)->update($eventDTO);

        $this->assertInstanceOf(EventDTO::class, $returnedDTO);
        $this->assertEquals($eventDTO->id, $returnedDTO->id);
        $this->assertEquals($eventDTO->userId, $returnedDTO->userId);
        $this->assertEquals($eventDTO->location, $returnedDTO->location);
        $this->assertNotEmpty($returnedDTO->date);
        $this->assertNotEmpty($returnedDTO->invitees);
    }
}
