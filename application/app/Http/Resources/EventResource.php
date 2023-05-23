<?php

namespace App\Http\Resources;

use App\DTOs\EventDTO;
use App\DTOs\WeatherForecastDTO;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Request as RequestAlias;

class EventResource extends JsonResource
{
    /**
     * @param EventDTO $eventDTO
     */
    public function __construct(private readonly EventDTO $eventDTO)
    {
        parent::__construct($eventDTO);
    }

    /**
     * @param $request
     * @return array
     * @throws \Exception
     */
    public function toArray($request): array
    {
        $response = [
            'id' => $this->eventDTO->id,
            'location' => $this->eventDTO->location,
            'date' => (new \DateTime($this->eventDTO->date))->format('Y-m-d H:i'),
            'invitees' => $this->eventDTO->invitees,
            'created_at' => (new \DateTime($this->eventDTO->createdAt))->format('Y-m-d H:i:s'),
        ];

        if ($request->getMethod() === RequestAlias::METHOD_GET) {
            $response['weather_forecast'] = 'weather forecast unavailable';

            if (!empty($this->eventDTO->weatherForecastDTO && $this->eventDTO->weatherForecastDTO instanceof WeatherForecastDTO)) {
                $response['weather_forecast'] = $this->eventDTO->weatherForecastDTO->toArray();
            }
        }

        return $response;
    }
}
