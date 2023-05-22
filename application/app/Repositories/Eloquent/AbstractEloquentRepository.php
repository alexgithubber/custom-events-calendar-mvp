<?php

namespace App\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class AbstractEloquentRepository
{
    protected Model $model;

    public function __construct()
    {
        $this->model = $this->makeModel();
    }

    abstract public function makeModel(): Model;

    public function findById(int $id): array
    {
        $queryResult = $this->model->firstWhere('id', $id);

        return !empty($queryResult) ? $queryResult->toArray() : [];
    }

    public function fetchByIds(array $ids): array
    {
        return $this->whereIn('id', $ids)->all();
    }

    public function fetchAll(?array $where = []): array
    {
        return $this->model->where($where)->get()->all();
    }

    public function insert(array $data): array
    {
        return $this->model->create($data)->toArray();
    }

    public function update(int $id, array $data): bool
    {
        return $this->model->where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return $this->model->destroy($id);
    }

    protected function whereIn(string $field, array $values): Collection
    {
        return $this->model->whereIn($field, $values)->get();
    }
}
