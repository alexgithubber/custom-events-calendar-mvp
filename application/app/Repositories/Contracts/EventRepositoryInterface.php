<?php

namespace App\Repositories\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;

interface EventRepositoryInterface
{
    public function insert(array $data): array;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;

    public function findByIdWithInvitees(int $id): array;

    public function fetchAllEventsPaginated(): LengthAwarePaginator;

    public function fetchEventsBetween(string $from, string $to): LengthAwarePaginator;

    public function fetchEventLocationsBetween(string $from, string $to): array;
}
