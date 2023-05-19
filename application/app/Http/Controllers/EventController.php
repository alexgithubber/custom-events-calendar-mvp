<?php

namespace App\Http\Controllers;

use App\DTOs\EventDTO;
use App\Services\EventService;
use App\Http\Resources\EventResource;
use App\Http\Requests\EventCreateRequest;
use App\Http\Requests\EventUpdateRequest;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class EventController extends Controller
{
    private EventService $eventService;

    public function __construct(EventService $eventService) //TODO: trocar pra inerface depois?
    {
        $this->eventService = $eventService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
//        die("index");
    }

    /**
     * Show the form for creating a new resource.
     * Nota: método desnecessário por conta do Route::apiResource (o método devolve uma página HTML)
     */
//    public function create()
//    {
//        //
//    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EventCreateRequest $request)
    {
        try {
            $validatedInput = $request->validated();

            $userId = 1; //TODO: buscar o id do usuário (seja aqui ou num middleware)

            $eventDTO = new EventDTO(
                $userId,
                $validatedInput['date'],
                $validatedInput['location'],
                $validatedInput['invitees']
            );

            $persistedEventDTO = $this->eventService->create($eventDTO);

            return response()->json([
                'status' => 'created',
                'event' => new EventResource($persistedEventDTO),
            ], ResponseAlias::HTTP_CREATED);
        } catch (\Throwable $exception) {
//            print($exception->getMessage());die;
            return response()->json([
                'message' => "Failed to create event",
                'error_code' => $exception->getCode(),
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //die("show");
    }

    /**
     * Show the form for editing the specified resource.
     * Nota: método desnecessário por conta do Route::apiResource (o método devolve uma página HTML)
     */
//    public function edit(string $id)
//    {
//        //
//    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EventUpdateRequest $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
