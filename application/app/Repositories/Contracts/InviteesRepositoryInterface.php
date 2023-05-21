<?php

namespace App\Repositories\Contracts;

interface InviteesRepositoryInterface
{
    public function insertMany(array $invitees): bool;

    public function deleteByEventId(int $eventId): bool;
}
