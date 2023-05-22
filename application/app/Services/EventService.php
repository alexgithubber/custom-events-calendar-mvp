<?php

namespace App\Services;

use App\DTOs\EventDTO;
use Illuminate\Support\Arr;
use App\DTOs\WeatherForecastDTO;
use Illuminate\Support\Facades\DB;
use App\DTOs\Contracts\DTOInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\RecordsNotFoundException;
use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Contracts\InviteesRepositoryInterface;
use App\Services\Contracts\WeatherForecastServiceInterface;

class EventService
{
    private EventRepositoryInterface $eventRepository;
    private InviteesRepositoryInterface $inviteesRepository;
    private WeatherForecastServiceInterface $weatherForecastService;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        InviteesRepositoryInterface $inviteesRepository,
        WeatherForecastServiceInterface $weatherForecastService
    ) {
        $this->eventRepository = $eventRepository;
        $this->inviteesRepository = $inviteesRepository;
        $this->weatherForecastService = $weatherForecastService;
    }

    public function getById(int $id): EventDTO
    {
        $eventDatabaseRecord = $this->eventRepository->findByIdWithInvitees($id);

        if (empty($eventDatabaseRecord)) {
            throw new RecordsNotFoundException();
        }

        $weatherForecast = $this->getWeatherForecast($eventDatabaseRecord['location'], $eventDatabaseRecord['date']);
        $fullEventData = [...$eventDatabaseRecord, 'weather_forecast' => $weatherForecast];

        return EventDTO::fromArray($fullEventData);
    }

    public function getAll(?array $conditions = []): LengthAwarePaginator
    {
        if (!empty($conditions['from']) && !empty($conditions['to'])) {
            $paginatedEvents = $this->eventRepository->fetchEventsBetween($conditions['from'], $conditions['to']);
        } else {
            $paginatedEvents = $this->eventRepository->fetchAllEventsPaginated();
        }

        return $paginatedEvents->through(function ($eventModel) {
            $weatherForecast = $this->getWeatherForecast($eventModel['location'], $eventModel['date']);
            $fullEventData = [...$eventModel->toArray(), 'weather_forecast' => $weatherForecast];
            return EventDTO::fromArray($fullEventData);
        });
    }

    public function fetchEventLocationsBetween(string $from, string $to): array
    {
        $resultSet = $this->eventRepository->fetchEventLocationsBetween($from, $to);

        $eventLocations = [];
        $eventLocations = $this->getDistinctDatesGroupedByLocation($resultSet, $eventLocations);

        $this->weatherForecastService->fillLocationsWithWeatherForecasts($eventLocations);

        return $eventLocations;
    }

    public function create(EventDTO $eventDTO): EventDTO
    {
        try {
            DB::beginTransaction();

            $createEventData = $eventDTO->extract();
            $invitees = Arr::pull($createEventData, 'invitees');

            $eventDatabaseRecord = $this->eventRepository->insert($createEventData);

            $this->insertInvitees($invitees, $eventDatabaseRecord['id']);

            //TODO: Send emails to invitees

            DB::commit();

            $eventDatabaseRecord['invitees'] = $eventDTO->invitees;
            return EventDTO::fromArray($eventDatabaseRecord);
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

            $eventDatabaseRecord = $this->eventRepository->findByIdWithInvitees($id);
            $this->eventRepository->update($id, $updateEventData);

            DB::commit();

            return $eventDTO::fromArray(array_merge($eventDatabaseRecord, $updateEventData));
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

    protected function getWeatherForecast(string $location, string $date): ?DTOInterface
    {
        $eventWeatherForecast = $this->weatherForecastService->getForecastByLocationAndDate($location, $date);

        if (!empty($eventWeatherForecast) && is_array($eventWeatherForecast)) {
            return WeatherForecastDTO::fromArray($eventWeatherForecast);
        }

        return null;
    }

    /**
     * Returns a filled array grouped by location and containing distinct event dates (repetitive dates are ignored)
     * Reason: avoid redundant requests later on
     */
    protected function getDistinctDatesGroupedByLocation(array $resultSet, array $eventLocations): array
    {
        foreach ($resultSet as $eventModel) {
            if (!isset($eventLocations[$eventModel->location])) {
                $eventLocations[$eventModel->location]['dates'] = [];
            }

            $eventDate = (new \DateTime($eventModel->date))->format('Y-m-d');
            $locationAndDateExists =
                Arr::where($eventLocations[$eventModel->location]['dates'], function ($event) use ($eventDate) {
                    return $event['event_date'] === $eventDate;
                });

            if (empty($locationAndDateExists)) {
                $eventLocations[$eventModel->location]['dates'][] = [
                    'event_date' => $eventDate,
                ];
            }
        }

        return $eventLocations;
    }
}
