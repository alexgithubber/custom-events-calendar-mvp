<?php

namespace App\Repositories\Eloquent;

use App\Models\EventModel;
use App\Repositories\Contracts\CrudRepositoryInterface;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;

class EventRepository extends AbstractEloquentRepository implements EventRepositoryInterface, CrudRepositoryInterface
{
    /**
     * @throws BindingResolutionException
     */
    public function makeModel(): Model
    {
        return app()->make(EventModel::class);
    }

    public function findByIdWithInvitees(int $id): array
    {
        return $this->model->where('id', '=', $id)->with('invitees')->first()->toArray();
    }

    public function fetchAllEventsPaginated(): mixed
    {
        return $this->model->oldest('date')->with('invitees')->paginate(10);
    }

    //TODO: devolver paginado
    public function fetchEventsBetween(string $from, string $to)
    {
        return $this->model->whereBetween('date', [$from, $to])
            ->oldest('date')
            ->with('invitees')
            ->get()
            ->all();
    }

    //TODO: devolver paginado
    public function fetchEventLocationsBetween(string $from, string $to)
    {
        return $this->model->whereBetween('date', [$from, $to])
            ->oldest('date')
            ->get()
            ->unique('location')
            ->pluck('location');
    }
}
