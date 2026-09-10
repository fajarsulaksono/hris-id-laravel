<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee\Employee;
use App\Support\ApiModules;
use App\Support\Security;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ApiController extends Controller
{
    public function __construct(protected Security $security) {}

    /**
     * Daftar data ter-filter + ter-paginate.
     * Filter umum: `q` (pencarian parsial), `employee_id`, `period_id`,
     * `component_id`, `company_id`, `department_id`, `year`, `month`,
     * `p` (halaman), `ep` (jumlah per halaman).
     */
    public function index(Request $request): JsonResponse
    {
        $module = $this->moduleOrAbort((string) $request->route('module'));
        $query = $this->baseQuery($module, $request->user());

        foreach ($this->filterableColumns() as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->query($filter));
            }
        }

        $q = $request->query('q');
        if ($q !== null && $module['searchable'] !== []) {
            $like = '%'.trim((string) $q).'%';
            $query->where(function (Builder $builder) use ($module, $like) {
                foreach ($module['searchable'] as $i => $field) {
                    if ($i === 0) {
                        $builder->where($field, 'like', $like);
                    } else {
                        $builder->orWhere($field, 'like', $like);
                    }
                }
            });
        }

        $perPage = min(
            max((int) ($request->query('ep') ?: config('hris.record_per_page')), 1),
            (int) config('hris.max_record_per_page')
        );

        $models = $query
            ->orderBy($module['order'][0], $module['order'][1])
            ->paginate($perPage)
            ->withQueryString();

        return $module['resource']::collection($models)->response();
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $module = $this->moduleOrAbort((string) $request->route('module'));
        $model = $this->baseQuery($module, $request->user())->findOrFail($id);

        return (new $module['resource']($model))->response();
    }

    public function store(Request $request): JsonResponse
    {
        $module = $this->moduleOrAbort((string) $request->route('module'));
        abort_unless($module['mutable'], 403);

        $data = $request->validate($module['rules']);
        $data = $this->normalizeTimeFields($data);
        $data = $this->applySelfService($module, $request, $data);
        $this->guardSelfUniqueConflict($module, $request, $data);

        $model = new $module['model'];
        $this->assign($model, $data);
        $model->save();

        return (new $module['resource']($model))->response()->setStatusCode(201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $module = $this->moduleOrAbort((string) $request->route('module'));
        abort_unless($module['mutable'], 403);

        $data = $request->validate($module['update_rules'] ?: $module['rules']);
        $data = $this->normalizeTimeFields($data);
        $data = $this->applySelfService($module, $request, $data);

        $model = $this->findOwned($module, $request, $id);
        $this->guardSelfReopen($module, $model, $data);
        $this->assign($model, $data);
        $model->save();

        return (new $module['resource']($model))->response();
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $module = $this->moduleOrAbort((string) $request->route('module'));
        abort_unless($module['mutable'], 403);

        $this->findOwned($module, $request, $id)->delete();

        return response()->json(['message' => 'Deleted.']);
    }

    /**
     * Query dasar modul: eager load + scope konteks perusahaan.
     */
    protected function baseQuery(array $module, ?Employee $user): Builder
    {
        $query = $module['model']::query();

        if ($module['with'] !== []) {
            $query->with($module['with']);
        }

        if ($module['scope'] !== null && $user !== null) {
            ($module['scope'])($query, $user);
        }

        if ($this->selfScoped($module, $user)) {
            $query->where('employee_id', $user->getKey());
        }

        return $query;
    }

    /**
     * Isi atribut model; pakai setter kustom (`setCode`, `setName`, ...) bila ada
     * agar normalisasi huruf besar konsisten dengan sisi web.
     */
    protected function assign(Model $model, array $data): void
    {
        foreach ($data as $key => $value) {
            $setter = 'set'.Str::studly($key);

            if (method_exists($model, $setter)) {
                $model->{$setter}(is_array($value) ? $value : (string) $value);
            } else {
                $model->{$key} = $value;
            }
        }
    }

    protected function moduleOrAbort(string $key): array
    {
        $module = ApiModules::get($key);

        abort_if($module === null, 404, "Module [{$key}] not found.");

        return $module;
    }

    /**
     * Modul self-service: pengguna non-privileged hanya bisa menyentuh
     * record miliknya sendiri. Yang punya ability `managed_by` (mis. admin)
     * melihat scope perusahaan penuh.
     */
    protected function selfScoped(array $module, ?Employee $user): bool
    {
        if (! ($module['self_service'] ?? false) || $user === null || $user->hasRole('SUPER_ADMIN')) {
            return false;
        }

        $managedBy = $module['managed_by'] ?? null;

        if ($managedBy !== null && $this->security->can($user, $managedBy)) {
            return false;
        }

        return true;
    }

    /**
     * Pada modul self-service, `employee_id` selalu diambil dari token,
     * bukan dari body, sehingga karyawan tak bisa memalsukan milik orang lain.
     */
    protected function applySelfService(array $module, Request $request, array $data): array
    {
        if (($module['self_service'] ?? false) && $request->user() !== null) {
            $data['employee_id'] = $request->user()->getKey();
        }

        return $data;
    }

    /**
     * Tolak duplikat pada kolom `self_unique` per karyawan (mis. absen
     * tanggal sama) untuk mencegah clock-in ganda.
     */
    protected function guardSelfUniqueConflict(array $module, Request $request, array $data): void
    {
        $column = $module['self_unique'] ?? null;

        if ($column === null || ! isset($data[$column])) {
            return;
        }

        $duplicate = $module['model']::query()
            ->whereDate($column, $data[$column])
            ->when($data['employee_id'] ?? null, fn (Builder $query, $employeeId) => $query->where('employee_id', $employeeId))
            ->exists();

        abort_if($duplicate, 422, "Duplicate [{$column}] for this employee.");
    }

    /**
     * Modul self-service: tolak pengisian ulang kolom waktu pulang saat
     * sudah tercatat (mencegah clock-out ganda dari perangkat lain).
     */
    protected function guardSelfReopen(array $module, Model $model, array $data): void
    {
        if (! ($module['self_service'] ?? false) || ! isset($data['check_out'])) {
            return;
        }

        $current = $model->getAttributes()['check_out'] ?? null;

        if ($current !== null) {
            abort(422, 'Check-out sudah dicatat.');
        }
    }

    /**
     * Normalisasi kolom waktu agar konsisten antar DB (SQLite menyimpan
     * apa adanya, MySQL menormalisasi ke HH:MM:SS). Pakai format HH:MM.
     */
    protected function normalizeTimeFields(array $data): array
    {
        foreach (['check_in', 'check_out', 'start_hour', 'end_hour'] as $field) {
            if (! empty($data[$field])) {
                $data[$field] = Carbon::parse((string) $data[$field])->format('H:i');
            }
        }

        return $data;
    }

    /**
     * Temukan model; pada modul self-service, non-privileged hanya boleh
     * mengambil record miliknya sendiri.
     */
    protected function findOwned(array $module, Request $request, string $id): Model
    {
        $query = $module['model']::query();

        if ($this->selfScoped($module, $request->user())) {
            $query->where('employee_id', $request->user()->getKey());
        }

        return $query->findOrFail($id);
    }

    /**
     * Kolom yang boleh dipakai sebagai filter `where` di semua modul.
     */
    protected function filterableColumns(): array
    {
        return [
            'employee_id',
            'period_id',
            'component_id',
            'company_id',
            'department_id',
            'year',
            'month',
            'type',
        ];
    }
}
