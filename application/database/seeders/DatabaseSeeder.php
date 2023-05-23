<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Models\EventModel;
use Illuminate\Database\Seeder;
use App\Models\EventInviteeModel;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        try {
            DB::beginTransaction();

            User::factory(1)->create();
            $generatedEvents = EventModel::factory(5)->create()->toArray();

            foreach ($generatedEvents as $generatedEvent) {
                EventInviteeModel::factory(3)->create(['event_id' => $generatedEvent['id']]);
            }

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }
    }
}
