<?php

namespace App\Services;

use App\DTOs\EventDTO;
use Illuminate\Support\Arr;
use App\Mail\EventInviteeEmail;
use App\DTOs\WeatherForecastDTO;
use Illuminate\Support\Facades\DB;
use App\DTOs\Contracts\DTOInterface;
use Illuminate\Support\Facades\Mail;
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

        /**
         * Fetching and adding the weather forecast to every element of the paginated collection
         * @returns LengthAwarePaginator
         */
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

            $creationData = $eventDTO->extract();
            $invitees = Arr::pull($creationData, 'invitees');

            $eventDatabaseRecord = $this->eventRepository->insert($creationData);

            $this->insertInvitees($invitees, $eventDatabaseRecord['id']);

            $this->sendEmailsToInvited($eventDTO);

            $eventDatabaseRecord['invitees'] = $eventDTO->invitees;
            $createdEventDTO = EventDTO::fromArray($eventDatabaseRecord);

            DB::commit();

            return $createdEventDTO;
        } catch (\Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function update(EventDTO $eventDTO): EventDTO
    {
        $updatingData = $eventDTO->extract();

        $id = Arr::pull($updatingData, 'id');
        $invitees = Arr::pull($updatingData, 'invitees');

        try {
            DB::beginTransaction();

            $eventDatabaseRecord = $this->eventRepository->findByIdWithInvitees($id);

            if (empty($eventDatabaseRecord)) {
                throw new RecordsNotFoundException();
            }

            $this->eventRepository->update($id, $updatingData);

            if (!empty($invitees)) {
                $this->updateInvitees($invitees, $id);
            }

            $eventDatabaseRecord['invitees'] = array_map(function ($element) {
                return $element['email'];
            }, $eventDatabaseRecord['invitees']);

            $updatedEventDTO = $eventDTO::fromArray(array_merge($eventDatabaseRecord, $updatingData));
            $this->sendEmailsToInvited($updatedEventDTO, true);

            DB::commit();

            return $updatedEventDTO;
        } catch (\Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function delete(int $id): bool
    {
        $event = $this->eventRepository->findById($id);

        if (empty($event)) {
            throw new RecordsNotFoundException();
        }

        return $this->eventRepository->delete($id);
    }

    protected function insertInvitees(array $invitees, int $id): void
    {
        $inviteesPersistanceData = $this->getPreparedForPersistanceInvitees($invitees, $id);
        $this->inviteesRepository->insertMany($inviteesPersistanceData);
    }

    /**
     * Erases all invitees of the event and recreate them
     */
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

    protected function sendEmailsToInvited(EventDTO $eventDTO, bool $eventUpdated = false): void
    {
        $title = 'CustomizedCalendar - Event invitation';
        $mainText = 'You\'ve been invited for the event, see the details bellow:';

        if ($eventUpdated && !empty($eventDTO->invitees)) {
            $title .= ' (update!)';
            $mainText = 'The event information was updated, check the changes bellow:';
        }

        foreach ($eventDTO->invitees as $to) {
            $mailData = $this->buildMailData($title, $mainText, $eventDTO->location, $eventDTO->date);

            $message = (new EventInviteeEmail($mailData))->onQueue('emails');

            Mail::to($to)->queue($message);
        }
    }

    protected function buildMailData(string $title, string $mainText, string $location, string $date): array
    {
        return [
            'title' => $title,
            'main_text' => $mainText,
            'location' => str_replace(',', ' - ', $location),
            'event_date' => (new \DateTime($date))->format('d/m/Y H:i:s'),
        ];
    }
}
