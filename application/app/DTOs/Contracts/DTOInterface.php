<?php

namespace App\DTOs\Contracts;

interface DTOInterface
{
    public static function fromArray(array $fields): DTOInterface;

    public function toArray(): array;
}
