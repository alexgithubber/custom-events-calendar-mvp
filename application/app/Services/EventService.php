<?php

namespace App\Services;

use App\DTOs\EventDTO;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Repositories\Eloquent\EventRepository;
use App\Repositories\Contracts\CrudRepositoryInterface;

class EventService
{
    private EventRepository $eventRepository;

    public function __construct(CrudRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param EventDTO $eventDTO
     * @return EventDTO
     * @throws \Throwable
     */
    public function create(EventDTO $eventDTO): EventDTO
    {
        try {
            DB::beginTransaction();

            $eventPersistanceData = $eventDTO->toPersistance();
            $invitees = Arr::pull($eventPersistanceData, 'invitees');

            $persistedEventData = $this->eventRepository->insert($eventPersistanceData);

            //TODO: Send emails to invitees

            DB::commit();

            $persistedEventData['invitees'] = $eventDTO->invitees; //todo: gambiarra temporária

            return EventDTO::fromArray($persistedEventData);
        } catch (\Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function update(int $id)
    {
        //TODO
    }

    public function delete(int $id)
    {
        //TODO
    }
}
