<?php

namespace App\Repositories\Eloquent;

use App\Models\EventModel;
use App\Repositories\Contracts\CrudRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;

class EventRepository extends AbstractEloquentRepository implements CrudRepositoryInterface
{
    /**
     * @throws BindingResolutionException
     */
    public function makeModel(): Model
    {
        return app()->make(EventModel::class);
    }

//    public function fetchAll(?array $where = []):array
//    {
//        return EventModel::with('invitees')->get();
//    }
}
