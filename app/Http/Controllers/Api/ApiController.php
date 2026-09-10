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
        $model = new $module['model'];
        $this->assign($model, $data);
        $model->save();

        return (new $module['resource']($model))->response()->setStatusCode(201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $module = $this->moduleOrAbort((string) $request->route('module'));
        abort_unless($module['mutable'], 403);

        $data = $request->validate($module['rules']);
        $model = $module['model']::findOrFail($id);
        $this->assign($model, $data);
        $model->save();

        return (new $module['resource']($model))->response();
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $module = $this->moduleOrAbort((string) $request->route('module'));
        abort_unless($module['mutable'], 403);

        $module['model']::findOrFail($id)->delete();

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
        ];
    }
}
