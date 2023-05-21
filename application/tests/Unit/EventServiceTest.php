<?php

namespace Tests\Unit;

use App\DTOs\EventDTO;
use App\Repositories\Eloquent\EventRepository;
use App\Repositories\Eloquent\InviteesRepository;
use App\Services\EventService;
use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class EventServiceTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
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
                'invitees' => [],
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
                'invitees' => [],
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
