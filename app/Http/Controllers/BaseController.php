<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseController extends Controller
{
    /**
     * Pola dasar index: semua modul memakai pagination ini
     * (default dari config/hris, bisa ditimpa via ?per_page= dan dibatasi maksimum).
     */
    protected function paginate($query, ?int $perPage = null): LengthAwarePaginator
    {
        $given = $perPage ?? (int) request()->input('per_page', (int) config('hris.record_per_page', 17));
        $max = (int) config('hris.max_record_per_page', 99);

        return $query->paginate(max(1, min($given, $max)))->withQueryString();
    }

    /**
     * Memuat model berdasarkan primary key (UUID), 404 bila tidak ditemukan.
     */
    protected function findOrFail(string $model, string $key, ?array $with = null): Model
    {
        $query = $model::query();

        if ($with) {
            $query->with($with);
        }

        return $query->findOrFail($key);
    }
}