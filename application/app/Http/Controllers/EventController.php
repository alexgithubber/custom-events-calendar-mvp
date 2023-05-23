<?php

namespace App\Http\Controllers;

use App\DTOs\EventDTO;
use App\Http\Requests\EventCreateRequest;
use App\Http\Requests\EventUpdateRequest;
use App\Http\Resources\EventResource;
use App\Services\EventService;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class EventController extends Controller
{
    private EventService $eventService;

    public function __construct(EventService $eventService)
    {
        $this->eventService = $eventService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $events = $this->eventService->getAll($request->query());

            return response()->json(EventResource::collection($events)->response()->getData(true),
                ResponseAlias::HTTP_OK);
        } catch (\Throwable $exception) {
            return response()->json([
                'status' => 'failed',
                'message' => "Could not get events",
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getLocations(Request $request): JsonResponse
    {
        try {
            $from = $request->query('from');
            $to = $request->query('to');

            if ($from && $to) {
                $events = $this->eventService->fetchEventLocationsBetween($from, $to);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => "The params 'from' and 'to' are required",
                ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
            }

            return response()->json($events, ResponseAlias::HTTP_OK);
        } catch (\Throwable $exception) {
            return response()->json([
                'status' => 'failed',
                'message' => "Could not get locations",
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(EventCreateRequest $request): JsonResponse
    {
        try {
            $validatedInput = $request->validated();

            $eventDTO = new EventDTO(
                auth('sanctum')->user()->id,
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
            return response()->json([
                'status' => 'failed',
                'message' => "Could not create event",
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(string $id): JsonResponse
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

    public function update(EventUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $inputFields = array_merge($request->validated(), ['id' => $id]);

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

    public function destroy(string $id): JsonResponse
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
