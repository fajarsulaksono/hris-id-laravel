<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

class DataTableServer
{
    /**
     * Membaca parameter DataTables 2.x dari request dan menghasilkan respons JSON.
     *
     * @param  array  $columns  [['data'=>..., 'title'=>..., 'searchable'=>bool, 'orderable'=>bool], ...]
     * @param  array  $searchable  atribut yang ikut dicari saat global search
     */
    public function response(Builder $query, array $columns, array $searchable = []): array
    {
        $start = (int) request()->input('start', 0);
        $length = (int) request()->input('length', 15);
        $draw = (int) request()->input('draw', 1);
        $search = trim((string) request()->input('search.value', ''));
        $trashed = request()->boolean('trashed');

        $length = $length < 0 ? 0 : min($length, (int) config('hris.max_record_per_page', 99));

        if ($trashed) {
            $query->onlyTrashed();
        }

        $recordsTotal = (clone $query)->count();

        if ($search !== '' && $searchable) {
            $query->where(function (Builder $q) use ($search, $searchable) {
                foreach ($searchable as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        $order = request()->input('order.0', []);
        if (isset($order['column']) && isset($columns[$order['column']]['data'])
            && ($columns[$order['column']]['orderable'] ?? true)) {
            $query->orderBy($columns[$order['column']]['data'], $order['dir'] ?? 'asc');
        }

        $recordsFiltered = (clone $query)->count();

        $rows = $length > 0 ? $query->offset($start)->limit($length)->get() : $query->get();

        $data = [];
        foreach ($rows as $row) {
            $item = [];
            foreach ($columns as $index => $column) {
                $render = $column['render'] ?? null;

                if ($render instanceof \Closure) {
                    $item[$column['data']] = $render($row);
                    continue;
                }

                if ($render === 'bool') {
                    $item[$column['data']] = $row->{$column['data']} ? 'Ya' : 'Tidak';
                    continue;
                }

                $item[$column['data']] = $column['data'] === 'DT_RowId'
                    ? $row->getKey()
                    : data_get($row, $column['data']);
            }
            $data[] = $item;
        }

        return [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ];
    }
}