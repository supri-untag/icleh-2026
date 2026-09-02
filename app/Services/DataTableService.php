<?php

namespace App\Services;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DataTableService
{
    /**
     * @param  array<int, string>  $searchableColumns
     * @param  array<int, string>  $orderableColumns
     */
    public function response(
        Request $request,
        Builder $query,
        array $searchableColumns,
        array $orderableColumns,
        Closure $rowMapper,
    ): JsonResponse {
        $recordsTotal = (clone $query)->count();
        $search = trim((string) data_get($request->input('search', []), 'value', ''));

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($searchableColumns, $search): void {
                foreach ($searchableColumns as $column) {
                    $builder->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        $recordsFiltered = (clone $query)->count();
        $orderIndex = (int) data_get($request->input('order', []), '0.column', 0);
        $orderDirection = strtolower((string) data_get($request->input('order', []), '0.dir', 'desc')) === 'asc'
            ? 'asc'
            : 'desc';
        $orderColumn = $orderableColumns[$orderIndex] ?? $orderableColumns[0] ?? 'id';

        $start = max(0, (int) $request->integer('start', 0));
        $length = (int) $request->integer('length', 10);
        $length = $length < 1 ? 10 : min($length, 100);

        $rows = $query
            ->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get()
            ->map(fn ($row): array => $rowMapper($row))
            ->values();

        return response()->json([
            'draw' => (int) $request->integer('draw', 0),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ]);
    }
}
