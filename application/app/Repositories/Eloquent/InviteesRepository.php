<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\InviteesRepositoryInterface;
use Illuminate\Support\Facades\DB;

class InviteesRepository implements InviteesRepositoryInterface
{
    private string $table = 'event_invitees';

    public function insertMany(array $invitees): bool
    {
        return DB::table($this->table)->insert($invitees);
    }

    public function deleteByEventId(int $eventId): bool
    {
        return DB::table($this->table)->where('event_id', '=', $eventId)->delete();
    }
}
