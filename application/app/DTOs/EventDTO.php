<?php

namespace App\DTOs;

use App\DTOs\Contracts\DTOInterface;

final class EventDTO implements DTOInterface
{
    public readonly int $userId;
    public readonly string $date;
    public readonly string $location;
    public readonly array $invitees;
    public ?int $id = null;
    public ?string $created_at = null;

    public function __construct(
        int $userId,
        string $date,
        string $location,
        array $invitees,
        ?int $id = null,
        ?string $created_at = null
    ) {
        $this->userId = $userId;
        $this->date = $date;
        $this->location = $location;
        $this->invitees = $invitees;
        $this->id = $id;
        $this->created_at = $created_at;
    }

    public static function fromArray(array $fields): EventDTO
    {
        return new self(
            userId: $fields['user_id'],
            date: $fields['date'],
            location: $fields['location'],
            invitees: $fields['invitees'],
            id: $fields['id'] ?? null,
            created_at: $fields['created_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function toPersistance(): array
    {
        return [
            'user_id' => $this->userId,
            'date' => $this->date,
            'location' => $this->location,
            'invitees' => $this->invitees,
        ];
    }
}
