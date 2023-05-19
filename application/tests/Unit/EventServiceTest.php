<?php

namespace Tests\Unit;

use Mockery;
use DateTime;
use Tests\TestCase;
use App\DTOs\EventDTO;
use Mockery\MockInterface;
use App\Services\EventService;
use App\Repositories\Eloquent\EventRepository;
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
        Mockery::close();
        parent::tearDown();
    }

    public function testCreate()
    {
        $eventDTO = new EventDTO(
            10,
            DateTime::createFromFormat('Y-m-d H:i:s', '2023-06-01 10:00:00'),
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
                'created_at' => new DateTime(),
                'invitees' => [],
            ];

            $mock->shouldReceive('insert')
                ->once()
                ->andReturn($returnValues);
        });

        $persistedEventDto = app(EventService::class)->create($eventDTO);

        $this->assertInstanceOf(EventDTO::class, $persistedEventDto);
//        $this->assertEquals($eventDTO->toArray(), $persistedEventDto->toArray());
//        $this->assertContains($eventDTO->toArray(), $persistedEventDto->toArray());
    }
}
