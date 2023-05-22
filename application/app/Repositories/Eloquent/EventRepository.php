<?php

namespace App\Repositories\Eloquent;

use App\Models\EventModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Contracts\CrudRepositoryInterface;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;

class EventRepository extends AbstractEloquentRepository implements EventRepositoryInterface, CrudRepositoryInterface
{
    const DEFAULT_PER_PAGE = 15;

    /**
     * @throws BindingResolutionException
     */
    public function makeModel(): Model
    {
        return app()->make(EventModel::class);
    }

    public function findByIdWithInvitees(int $id): array
    {
        $queryResult = $this->model->where('id', '=', $id)->with('invitees')->first();

        return !empty($queryResult) ? $queryResult->toArray() : [];
    }

    public function fetchAllEventsPaginated(): LengthAwarePaginator
    {
        return $this->model->oldest('date')->with('invitees')->paginate(self::DEFAULT_PER_PAGE);
    }

    public function fetchEventsBetween(string $from, string $to): LengthAwarePaginator
    {
        $results = $this->model->whereBetween('date', [$from, $to])
            ->oldest('date')
            ->get();

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $pagedResults = $results->slice(($currentPage - 1) * self::DEFAULT_PER_PAGE, self::DEFAULT_PER_PAGE);

        return new LengthAwarePaginator(
            $pagedResults,
            $results->count(),
            self::DEFAULT_PER_PAGE,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => [
                    'from' => $from,
                    'to' => $to,
                ],
            ]
        );
    }

    public function fetchEventLocationsBetween(string $from, string $to): array
    {
        return $this->model->whereBetween('date', [$from, $to])
            ->oldest('date')
            ->get()
            ->all();
    }
}
