<?php

namespace App\Repositories\Eloquent;

use App\Models\EventModel;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Contracts\EventRepositoryInterface;

class EventRepository implements EventRepositoryInterface
{
    const DEFAULT_PER_PAGE = 15;

    public function insert(array $data): array
    {
        return EventModel::create($data)->toArray();
    }

    public function update(int $id, array $data): bool
    {
        return EventModel::where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return EventModel::destroy($id);
    }

    protected function whereIn(string $field, array $values): Collection
    {
        return EventModel::whereIn($field, $values)->get();
    }

    public function findByIdWithInvitees(int $id): array
    {
        $queryResult = EventModel::where('id', '=', $id)
            ->with('invitees:id,event_id,email')
            ->first();

        return !empty($queryResult) ? $queryResult->toArray() : [];
    }

    public function fetchAllEventsPaginated(): LengthAwarePaginator
    {
        return EventModel::where('user_id', $this->getUserId())
            ->oldest('date')
            ->with('invitees:id,event_id,email')
            ->paginate(self::DEFAULT_PER_PAGE);
    }

    public function fetchEventsBetween(string $from, string $to): LengthAwarePaginator
    {
        $results = EventModel::whereBetween('date', [$from, $to])
            ->where('user_id', $this->getUserId())
            ->oldest('date')
            ->with('invitees:id,event_id,email')
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
        return EventModel::whereBetween('date', [$from, $to])
            ->where('user_id', $this->getUserId())
            ->oldest('date')
            ->get()
            ->all();
    }

    protected function getUserId()
    {
        return auth('sanctum')->user()->id;
    }
}
