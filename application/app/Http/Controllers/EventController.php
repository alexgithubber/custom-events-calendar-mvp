<?php

namespace App\Http\Controllers;

use App\DTOs\EventDTO;
use Illuminate\Http\Request;
use App\Services\EventService;
use App\Http\Resources\EventResource;
use App\Http\Requests\EventCreateRequest;
use App\Http\Requests\EventUpdateRequest;
use Illuminate\Database\RecordsNotFoundException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class EventController extends Controller
{
    private EventService $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    public function index(Request $request)
    {
        //TODO: validar aqui ou no Request pra remover os campos 'from/to' caso só venha um deles

        $events = $this->eventService->getAll($request->query());

        return response()->json([
            'events' => $events,
        ], ResponseAlias::HTTP_OK);
    }

    public function getLocations(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');

        $events = $this->eventService->fetchEventLocationsBetween($from, $to);

        return response()->json([
            'event_locations' => $events,
        ], ResponseAlias::HTTP_OK);
    }

    public function store(EventCreateRequest $request)
    {
        try {
            $validatedInput = $request->validated();

            $userId = 1; //TODO: buscar o id do usuário através do token (seja aqui ou num middleware)

            $eventDTO = new EventDTO(
                $userId,
                $validatedInput['date'],
                $validatedInput['location'],
                $validatedInput['invitees']
            );

            $createdEventDTO = $this->eventService->create($eventDTO);

            return response()->json([
                'status' => 'created',
                'event' => new EventResource($createdEventDTO),
            ], ResponseAlias::HTTP_CREATED);
        } catch (\Throwable $exception) {
//            print($exception->getMessage());die;
            return response()->json([
                'status' => 'failed',
                'message' => "Could not create event",
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(string $id)
    {
        try {
            $eventDTO = $this->eventService->getById($id);

            return response()->json([
                'event' => new EventResource($eventDTO),
            ], ResponseAlias::HTTP_OK);
        } catch (RecordsNotFoundException $notFoundException) {
            return response()->json([
                'status' => 'failed',
                'message' => "Event with id $id not found",
            ], ResponseAlias::HTTP_NOT_FOUND);
        } catch (\Throwable $exception) {
            return response()->json([
                'status' => 'failed',
                'message' => 'A fatal error occurred while getting the event',
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(EventUpdateRequest $request, string $id)
    {
        try {
            $inputFields = array_merge($request->validated(),
                ['user_id' => '1', 'id' => $id]); //todo: apenas em quanto o userid não vem no request

            $eventDTO = EventDTO::fromUpdateRequest($inputFields);
            $updatedEventDTO = $this->eventService->update($eventDTO);

            return response()->json([
                'status' => 'success',
                'event' => new EventResource($updatedEventDTO),
            ], ResponseAlias::HTTP_OK);
        } catch (RecordsNotFoundException $notFoundException) {
            return response()->json([
                'status' => 'failed',
                'message' => "Event with id $id not found",
            ], ResponseAlias::HTTP_NOT_FOUND);
        } catch (\Throwable $exception) {
            return response()->json([
                'status' => 'failed',
                'message' => 'A fatal error occurred while updating the event',
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->eventService->delete($id);

            return response()->json([
                'status' => 'success',
                'message' => "Event with id $id was deleted",
            ], ResponseAlias::HTTP_OK);
        } catch (RecordsNotFoundException $notFoundException) {
            return response()->json([
                'status' => 'failed',
                'message' => "Event with id $id not found",
            ], ResponseAlias::HTTP_NOT_FOUND);
        } catch (\Throwable $exception) {
            return response()->json([
                'status' => 'failed',
                'message' => 'A fatal error occurred while deleting the event',
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
