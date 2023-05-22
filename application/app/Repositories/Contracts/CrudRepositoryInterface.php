<?php

namespace App\Repositories\Contracts;

interface CrudRepositoryInterface
{
    public function findById(int $id): array;

    public function fetchByIds(array $ids): array;

    public function fetchAll(?array $where): array;

    public function insert(array $data): array;

    public function update(int $id, array $data);

    public function delete(int $id);
}
