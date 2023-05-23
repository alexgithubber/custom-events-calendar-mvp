<?php

namespace Database\Factories;

use App\Models\EventModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventModel>
 */
class EventModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locations = ['Paris,FR', 'London,UK','Buenos Aires,AR','Sao Paulo,BR','Tokyo,JP','Berlin,DE'];

        return [
            'user_id' => DB::table('users')->latest()->first()->id,
            'location' => fake()->randomElement($locations),
            'date' => fake()->dateTimeBetween('-2 week', '+2 week'),
        ];
    }
}
