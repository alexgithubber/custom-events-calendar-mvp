<?php

declare(strict_types=1);

namespace Tests\Unit;

use DateTime;
use Tests\TestCase;
use App\DTOs\EventDTO;
use Mockery\MockInterface;
use App\Services\EventService;
use App\Repositories\Eloquent\EventRepository;
use App\Repositories\Eloquent\InviteesRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EventServiceTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function testGetById()
    {
        $this->mock(EventRepository::class, function (MockInterface $mock) {
            $returnValues = [
                'id' => '1',
                'user_id' => '10',
                'location' => 'Berlin',
                'date' => '2023-06-01 10:00:00',
                'created_at' => (new DateTime())->format('Y-m-d H:i:s'),
                'invitees' => [],
                'weather_forecast' => [
                    'description' => 'Clear sky',
                    'temperature' => [
                        'min' => 10,
                        'max' => 21,
                    ],
                    'precipitation_chance' => 15,
                ],
            ];

            $mock->shouldReceive('findByIdWithInvitees')
                ->once()
                ->andReturn($returnValues);
        });

        $persistedEventDto = app(EventService::class)->getById(1);
        $this->assertInstanceOf(EventDTO::class, $persistedEventDto);
    }

    public function testGetAll()
    {
        $this->mock(EventRepository::class, function (MockInterface $mock) {
            $returnValues[] = [
                'id' => '1',
                'user_id' => '10',
                'location' => 'Berlin',
                'date' => '2023-06-01 10:00:00',
                'created_at' => (new DateTime())->format('Y-m-d H:i:s'),
                'invitees' => [],
                'weather_forecast' => [
                    'description' => 'Clear sky',
                    'temperature' => [
                        'min' => 10,
                        'max' => 21,
                    ],
                    'precipitation_chance' => 15,
                ],
            ];

            $returnValues[] = [
                'id' => '2',
                'user_id' => '11',
                'location' => 'London',
                'date' => '2023-06-01 10:00:00',
                'created_at' => (new DateTime())->format('Y-m-d H:i:s'),
                'invitees' => [],
                'weather_forecast' => [
                    'description' => 'Mainly clear',
                    'temperature' => [
                        'min' => 15,
                        'max' => 27,
                    ],
                    'precipitation_chance' => 3,
                ],
            ];

            $collection = collect($returnValues);
            $mock->shouldReceive('fetchAllEventsPaginated')
                ->once()
                ->andReturn($collection);
        });

        $result = app(EventService::class)->getAll();
        $this->assertIsArray($result);

//        $this->assertInstanceOf(EventDTO::class, $persistedEventDto);
    }

    public function testCreate()
    {
        $eventDTO = new EventDTO(
            10,
            '2023-06-01 10:00:00',
            'Sao Paulo',
            [
                'fulano@test.com',
                'beltrano@test.com',
            ]
        );

        $this->mock(EventRepository::class, function (MockInterface $mock) {
            $returnValues = [
                'id' => '1',
                'user_id' => '10',
                'location' => 'Berlin',
                'date' => '2023-06-01 10:00:00',
                'created_at' => (new DateTime())->format('Y-m-d H:i:s'),
                'invitees' => [
                    'fulano@test.com',
                    'beltrano@test.com',
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

        $persistedEventDto = app(EventService::class)->create($eventDTO);

        $this->assertInstanceOf(EventDTO::class, $persistedEventDto);
//        $this->assertEquals($eventDTO->toArray(), $persistedEventDto->toArray());
//        $this->assertContains($eventDTO->toArray(), $persistedEventDto->toArray());
    }

    public function testUpdate()
    {
        $updatePayload = [
            'id' => 10,
            'user_id' => 1,
            'location' => 'new address',
        ];

        $eventDTO = EventDTO::fromUpdateRequest($updatePayload);

        $this->mock(EventRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('update')
                ->once()
                ->andReturnTrue();

            $returnValues = [
                'id' => '1',
                'user_id' => '10',
                'location' => 'new address',
                'date' => '2023-06-01 10:00:00',
                'created_at' => '2023-06-01 10:00:00',
                'invitees' => [
                    'fulano@test.com',
                    'beltrano@test.com',
                ],
            ];

            $mock->shouldReceive('findByIdWithInvitees')
                ->once()
                ->andReturn($returnValues);
        });

        $persistedEventDto = app(EventService::class)->update($eventDTO);

        $this->assertInstanceOf(EventDTO::class, $persistedEventDto);
//        $this->assertEquals($eventDTO->toArray(), $persistedEventDto->toArray());
//        $this->assertContains($eventDTO->toArray(), $persistedEventDto->toArray());
    }
}
