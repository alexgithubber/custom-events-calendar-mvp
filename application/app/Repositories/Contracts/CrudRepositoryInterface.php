<?php

namespace App\Repositories\Contracts;

interface CrudRepositoryInterface
{
    //TODO: tomar cuidado com métodos não utilizados da interface, isso viola o ISP (clientes não devem ser forçados a depender de métodos que não utilizam)

    public function findById(int $id): array;

    public function fetchByIds(array $ids): array;

    public function fetchAll(?array $where): array;

    public function insert(array $data): array;

    public function update(int $id, array $data);

    public function delete(int $id);
}
