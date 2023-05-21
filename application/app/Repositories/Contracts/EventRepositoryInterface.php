<?php

namespace App\Repositories\Contracts;

interface EventRepositoryInterface
{
    public function findByIdWithInvitees(int $id): array;

    public function fetchAllEventsPaginated(): mixed;

    public function fetchEventsBetween(string $from, string $to);

    public function fetchEventLocationsBetween(string $from, string $to);
}
