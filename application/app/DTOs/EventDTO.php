<?php

namespace App\DTOs;

use App\DTOs\Contracts\DTOInterface;

final class EventDTO implements DTOInterface
{
    /*
     * The user that registered the event
     */
    public readonly int $userId;

    /*
     * Event id
     */
    public readonly ?int $id;

    /*
     * The event date (format: Y-m-d H:i:s)
     */
    public readonly ?string $date;

    /*
     * The event location
     */
    public readonly ?string $location;

    /*
     * The event invitees
     */
    public readonly ?array $invitees;

    /*
     * The event creation date in the database
     */
    public readonly ?string $createdAt;

    /**
     * @param int $userId
     * @param string|null $date
     * @param string|null $location
     * @param array $invitees
     * @param string|null $createdAt
     * @param int|null $id
     */
    public function __construct(
        int $userId,
        string $date = null,
        string $location = null,
        array $invitees = [],
        int $id = null,
        string $createdAt = null,
    ) {
        $this->userId = $userId;
        $this->date = $date;
        $this->location = $location;
        $this->invitees = $invitees;
        $this->id = $id;
        $this->createdAt = $createdAt;
    }

    /**
     * @param array $fields
     * @return EventDTO
     */
    public static function fromCreateRequest(array $fields): EventDTO
    {
        return self::fromArray($fields);
    }

    /**
     * @param array $fields
     * @return EventDTO
     */
    public static function fromUpdateRequest(array $fields): EventDTO
    {
        return new self(
            userId: $fields['user_id'],
            date: $fields['date'] ?? null,
            location: $fields['location'] ?? null,
            invitees: $fields['invitees'] ?? [],
            id: $fields['id'],
        );
    }

    /**
     * @param array $fields
     * @return EventDTO
     */
    public static function fromArray(array $fields): EventDTO
    {
        return new self(
            userId: $fields['user_id'],
            date: $fields['date'],
            location: $fields['location'],
            invitees: $fields['invitees'] ?? [],
            id: $fields['id'] ?? null,
            createdAt: $fields['created_at'] ?? null,
        );
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'date' => $this->date,
            'location' => $this->location,
            'invitees' => $this->invitees,
            'created_at' => $this->createdAt,
        ];
    }

    /**
     * @return array
     */
    public function extract(): array
    {
        return array_filter($this->toArray());
    }
}
