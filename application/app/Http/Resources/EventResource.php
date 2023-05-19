<?php

namespace App\Http\Resources;

use App\DTOs\EventDTO;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
{
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
        return [
            'id' => $this->eventDTO->id,
//            'creator' =>$this->eventDTO->userName,
            'location' => $this->eventDTO->location,
            'date' => $this->eventDTO->date,
            'invitees' => $this->eventDTO->invitees,
            'created_at' => (new \DateTime($this->eventDTO->created_at))->format('d-m-Y H:i:s'),
        ];
    }
}
