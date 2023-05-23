<?php

namespace Tests\Feature;

use App\Models\EventModel;
use App\Models\User;
use Tests\TestCase;

class EventServiceIntegrationTest extends TestCase
{
    public function testStore()
    {
        $payload = [
            'date' => '2023-05-30 12:00',
            'location' => 'Sao Paulo,SP',
            'invitees' => [
                'teste1@example1.com',
                'teste2@example2.com',
            ],
        ];

        $user = User::factory()->create();
        $this->actingAs($user, $guard = 'sanctum');

        $response = $this->postJson('/api/events', $payload)
            ->assertStatus(201)
            ->assertJson([
                'status' => 'created',
                'event' => [
                    "location" => $payload['location'],
                    "date" => $payload['date'],
                    'invitees' => $payload['invitees'],
                ],
            ])->decodeResponseJson();

        $this->assertDatabaseHas('events', [
            'user_id' => $user->id,
            'date' => $payload['date'],
            'location' => $payload['location'],
        ]);

        $decodedResponse = json_decode($response->json, true);
        $eventId = $decodedResponse['event']['id'];

        foreach ($payload['invitees'] as $invitee) {
            $this->assertDatabaseHas('event_invitees', [
                'event_id' => $eventId,
                'email' => $invitee,
            ]);
        }
    }

    public function testUpdate()
    {
        $payload = [
            'location' => 'Barcelona,ES',
        ];

        $user = User::factory()->create();
        $this->actingAs($user, $guard = 'sanctum');

        $event = EventModel::factory()->create([
            'user_id' => $user->id,
            'date' => '2023-05-30 12:00',
            'location' => 'Madrid,ES',
        ]);

        $this->patchJson('/api/events/' . $event->id, $payload)
            ->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'event' => [
                    "location" => $payload['location'],
                    "date" => '2023-05-30 12:00',
                ],
            ])->assertJsonStructure([
                'status',
                'event' => [
                    'location',
                    'date',
                    'invitees' => [],
                ],
            ]);

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'location' => $payload['location'],
        ]);
    }
}
