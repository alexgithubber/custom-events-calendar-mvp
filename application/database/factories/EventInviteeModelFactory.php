<?php

namespace Database\Factories;

use App\Models\EventInviteeModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventInviteeModel>
 */
class EventInviteeModelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'email' => fake()->safeEmail(),
        ];
    }
}
