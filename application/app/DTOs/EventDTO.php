<?php

namespace App\DTOs;

use App\DTOs\Contracts\DTOInterface;

final class EventDTO implements DTOInterface
{
    /*
     * The user that registered the event
     */
    public readonly ?int $userId;

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
     * The weather forecast for the event
     */
    public readonly ?WeatherForecastDTO $weatherForecastDTO;

    /*
     * The event creation date in the database
     */
    public readonly ?string $createdAt;

    /**
     * @param int|null $userId
     * @param string|null $date
     * @param string|null $location
     * @param array $invitees
     * @param WeatherForecastDTO|null $weatherForecastDTO
     * @param int|null $id
     * @param string|null $createdAt
     */
    public function __construct(
        int $userId = null,
        string $date = null,
        string $location = null,
        array $invitees = [],
        WeatherForecastDTO $weatherForecastDTO = null,
        int $id = null,
        string $createdAt = null,
    ) {
        $this->userId = $userId;
        $this->date = $date;
        $this->location = $location;
        $this->invitees = $invitees;
        $this->weatherForecastDTO = $weatherForecastDTO;
        $this->id = $id;
        $this->createdAt = $createdAt;
    }

    /**
     * @param array $fields
     * @return EventDTO
     */
    public static function fromUpdateRequest(array $fields): EventDTO
    {
        return new self(
            userId: $fields['user_id'] ?? null,
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
            weatherForecastDTO: $fields['weather_forecast'] ?? null,
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
            'weather_forecast' => !empty($this->weatherForecastDTO) ? $this->weatherForecastDTO->toArray() : [],
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
