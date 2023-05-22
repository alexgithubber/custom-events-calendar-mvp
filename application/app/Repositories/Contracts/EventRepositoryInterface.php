<?php

namespace App\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;

interface EventRepositoryInterface
{
    public function findByIdWithInvitees(int $id): array;

    public function fetchAllEventsPaginated(): LengthAwarePaginator;

    public function fetchEventsBetween(string $from, string $to): LengthAwarePaginator;

    public function fetchEventLocationsBetween(string $from, string $to): array;
}
