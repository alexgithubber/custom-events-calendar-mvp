<?php

namespace App\DTOs;

use App\DTOs\Contracts\DTOInterface;

final class WeatherForecastDTO implements DTOInterface
{
    /*
     * The weather description based on the WMO Weather interpretation codes
     */
    public readonly string $description;

    /*
     * An array with the [min,max] temperatures for the given day
     */
    public readonly array $temperature;

    /*
     * The chance of precipitation (in percentage)
     */
    public readonly float $precipitationChance;

    public function __construct(string $description, array $temperature, float $precipitationChance)
    {
        $this->description = $description;
        $this->temperature = $temperature;
        $this->precipitationChance = $precipitationChance;
    }

    public static function fromArray(array $fields): DTOInterface
    {
        return new self(
            description: $fields['description'],
            temperature: $fields['temperature'],
            precipitationChance: $fields['precipitation_chance']
        );
    }

    public function toArray(): array
    {
        return [
            'description' => $this->description,
            'temperature' => $this->temperature,
            'precipitation_chance' => $this->precipitationChance,
        ];
    }

}
