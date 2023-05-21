<?php

namespace App\Services;

use App\DTOs\EventDTO;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\RecordsNotFoundException;
use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Contracts\InviteesRepositoryInterface;

class EventService
{
    private EventRepositoryInterface $eventRepository;
    private InviteesRepositoryInterface $inviteesRepository;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        InviteesRepositoryInterface $inviteesRepository
    ) {
        $this->eventRepository = $eventRepository;
        $this->inviteesRepository = $inviteesRepository;
    }

    public function getById(int $id): EventDTO
    {
        $event = $this->eventRepository->findByIdWithInvitees($id);

        if (empty($event)) {
            throw new RecordsNotFoundException();
        }

        return EventDTO::fromArray($event);
    }

    public function getAll(?array $conditions = []): array
    {
        //TODO: interessante ter um método pra esse caso? (getAllBetween?)
        if (!empty($conditions['from']) && !empty($conditions['to'])) {
            return $this->eventRepository->fetchEventsBetween($conditions['from'], $conditions['to']);
        }

        return $this->eventRepository->fetchAllEventsPaginated()->toArray();
    }

    public function fetchEventLocationsBetween(string $from, string $to)
    {
        return $this->eventRepository->fetchEventLocationsBetween($from, $to);
    }

    public function create(EventDTO $eventDTO): EventDTO
    {
        try {
            DB::beginTransaction();

            $createEventData = $eventDTO->extract();
            $invitees = Arr::pull($createEventData, 'invitees');

            $createdEvent = $this->eventRepository->insert($createEventData);

            $this->insertInvitees($invitees, $createdEvent['id']);

            //TODO: Send emails to invitees

            DB::commit();

            $createdEvent['invitees'] = $eventDTO->invitees;
            return EventDTO::fromArray($createdEvent);
        } catch (\Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function update(EventDTO $eventDTO): EventDTO
    {
        $updateEventData = $eventDTO->extract();
        unset($updateEventData['user_id']);

        $id = Arr::pull($updateEventData, 'id');

        try {
            DB::beginTransaction();

            if (!empty($updateEventData['invitees'])) {
                $invitees = Arr::pull($updateEventData, 'invitees');
                $this->updateInvitees($invitees, $id);
            }

            $event = $this->eventRepository->findByIdWithInvitees($id);
            $this->eventRepository->update($id, $updateEventData);

            DB::commit();

            return $eventDTO::fromArray(array_merge($event, $updateEventData));
        } catch (\Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function delete(int $id): bool
    {
        $this->eventRepository->delete($id);

        return true;
    }

    protected function insertInvitees(array $invitees, int $id): void
    {
        $inviteesPersistanceData = $this->getPreparedForPersistanceInvitees($invitees, $id);
        $this->inviteesRepository->insertMany($inviteesPersistanceData);
    }

    protected function updateInvitees(array $invitees, int $id): void
    {
        $inviteesPersistanceData = $this->getPreparedForPersistanceInvitees($invitees, $id);
        $this->inviteesRepository->deleteByEventId($id);
        $this->inviteesRepository->insertMany($inviteesPersistanceData);
    }

    protected function getPreparedForPersistanceInvitees(array $invitees, int $id): array
    {
        $inviteesArr = [];
        foreach ($invitees as $invitee) {
            $inviteesArr[] = [
                'event_id' => $id,
                'email' => $invitee,
            ];
        }

        return $inviteesArr;
    }
}
