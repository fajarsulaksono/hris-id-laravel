<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Support\DataTableServer;
use App\Support\MasterModules;
use App\Support\StringUtil;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MasterDataController extends BaseController
{
    public function index(Request $request): View
    {
        $module = $this->module($request);

        return view('admin.crud.index', [
            'module' => $module,
            'columnsJson' => $this->columnsJson($this->tableColumns($module)),
            'dataUrl' => route($this->routeName($request, 'data')),
            'trashUrl' => route($this->routeName($request, 'trash')),
            'createUrl' => route($this->routeName($request, 'create')),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $module = $this->module($request);
        $model = $module['model'];

        $columns = $this->tableColumns($module);
        $searchable = $module['searchable']
            ?: collect($module['fields'])
                ->whereIn('type', ['text', 'email'])
                ->pluck('name')
                ->all();

        $query = $model::query();

        if (! request()->filled('order.0.column') && isset($module['order'])) {
            $query->orderBy($module['order'][0], $module['order'][1] ?? 'desc');
        }

        return response()->json(
            app(DataTableServer::class)->response($query, $columns, $searchable)
        );
    }

    public function trash(Request $request): View
    {
        $module = $this->module($request);

        return view('admin.crud.trash', [
            'module' => $module,
            'columnsJson' => $this->columnsJson($this->tableColumns($module)),
            'dataUrl' => route($this->routeName($request, 'data')),
            'trashUrl' => route($this->routeName($request, 'trash')),
            'indexUrl' => route($this->routeName($request, 'index')),
        ]);
    }

    public function create(Request $request): View
    {
        $module = $this->module($request);

        return view('admin.crud.form', [
            'module' => $module,
            'model' => null,
            'formAction' => route($this->routeName($request, 'store')),
            'formMethod' => 'POST',
            'backUrl' => route($this->routeName($request, 'index')),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $module = $this->module($request);

        $data = $this->validated($module, $request, null);
        $data = $this->transform($module, $data);

        ($module['model'])::create($data);

        return redirect()->route($this->routeName($request, 'index'))
            ->with('success', "{$module['title']} berhasil ditambahkan.");
    }

    public function show(Request $request, string $id): View
    {
        $module = $this->module($request);

        return view('admin.crud.show', [
            'module' => $module,
            'model' => $this->resolve($module, $id),
            'backUrl' => route($this->routeName($request, 'index')),
        ]);
    }

public function edit(Request $request, string $id): View
    {
        $module = $this->module($request);
        $model = $this->resolve($module, $id);
        $base = $this->routeName($request, '');

        return view('admin.crud.form', [
            'module' => $module,
            'model' => $model,
            'formAction' => route($base.'update', [$model->getKey()]),
            'formMethod' => 'PUT',
            'backUrl' => route($this->routeName($request, 'index')),
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $module = $this->module($request);

        $model = $this->resolve($module, $id);

        $model->update($this->transform($module, $this->validated($module, $request, $model)));

        return redirect()->route($this->routeName($request, 'index'))
            ->with('success', "{$module['title']} berhasil diperbarui.");
    }

    public function destroy(Request $request, string $id): RedirectResponse
    {
        $module = $this->module($request);

        $this->resolve($module, $id)->delete();

        return redirect()->route($this->routeName($request, 'index'))
            ->with('success', "{$module['title']} dipindahkan ke sampah.");
    }

    public function restore(Request $request, string $id): RedirectResponse
    {
        $module = $this->module($request);

        ($module['model'])::withTrashed()->findOrFail($id)->restore();

        return redirect()->back()->with('success', "{$module['title']} berhasil dipulihkan.");
    }

    public function forceDestroy(Request $request, string $id): RedirectResponse
    {
        $module = $this->module($request);

        ($module['model'])::withTrashed()->findOrFail($id)->forceDelete();

        return redirect()->back()->with('success', "{$module['title']} dihapus permanen.");
    }

    protected function module(Request $request): array
    {
        $module = MasterModules::get($request->route('moduleKey'));

        abort_unless(is_array($module), 404);

        return $module;
    }

    protected function tableColumns(array $module): array
    {
        $columns = $module['columns'] ?: collect($module['fields'])
            ->whereIn('type', ['text', 'email'])
            ->map(fn (array $field) => ['data' => $field['name'], 'title' => $field['label']])
            ->all();

        $columns[] = [
            'data' => 'actions',
            'title' => 'Aksi',
            'orderable' => false,
            'searchable' => false,
            'render' => fn (Model $row) => view('components.crud.table-actions', [
                'module' => $module,
                'row' => $row,
                'trashed' => request()->boolean('trashed'),
            ])->render(),
        ];

        return $columns;
    }

    protected function columnsJson(array $columns): string
    {
        return json_encode(collect($columns)->map(
            fn (array $column) => array_intersect_key($column, array_flip(['data', 'title', 'orderable', 'searchable']))
        )->values()->all());
    }

    protected function routeName(Request $request, string $action): string
    {
        return "admin.{$request->route('menuKey')}.{$request->route('moduleKey')}.{$action}";
    }

    protected function resolve(array $module, string $id): Model
    {
        return $this->findOrFail($module['model'], $id);
    }

    protected function validated(array $module, Request $request, ?Model $model): array
    {
        return $request->validate($this->rules($module, $model));
    }

    protected function rules(array $module, ?Model $model): array
    {
        $rules = [];

        foreach ($module['fields'] as $field) {
            $name = $field['name'];
            $fieldRules = [];

            if (($field['required'] ?? false)) {
                $fieldRules[] = 'required';
            } elseif (($field['type'] ?? 'text') !== 'checkbox') {
                $fieldRules[] = 'nullable';
            }

            if (($field['type'] ?? 'text') === 'date') {
                $fieldRules[] = 'date';
            }
            if (($field['type'] ?? 'text') === 'email') {
                $fieldRules[] = 'email';
            }
            if (isset($field['max'])) {
                $fieldRules[] = "max:{$field['max']}";
            }
            if (isset($field['options'])) {
                $fieldRules[] = Rule::in(array_column($field['options'], 'value'));
            }
            if (! empty($field['model'])) {
                $fieldRules[] = 'exists:'.(new $field['model']())->getTable().',id';
            }
            if (! empty($field['unique'])) {
                $fieldRules[] = $this->uniqueRule($module, $field, $model);
            }

            if ($fieldRules) {
                $rules[$name] = $fieldRules;
            }
        }

        return $rules;
    }

    protected function uniqueRule(array $module, array $field, ?Model $model): \Illuminate\Contracts\Validation\Rule|Rule|string
    {
        $table = (new $module['model']())->getTable();

        if (is_array($field['unique'])) {
            [$table, $columns] = $field['unique'];

            $rule = Rule::unique($table, $field['name'])->whereNull('deleted_at');

            foreach ($columns as $column) {
                if ($column !== $field['name']) {
                    $rule->where($column, request($column));
                }
            }
        } else {
            $rule = Rule::unique($table, $field['name'])->whereNull('deleted_at');
        }

        if ($model !== null) {
            $rule->ignore($model->getKey());
        }

        return $rule;
    }

    protected function transform(array $module, array $data): array
    {
        foreach ($module['fields'] as $field) {
            $name = $field['name'];
            $type = $field['type'] ?? 'text';

            if ($type === 'checkbox') {
                $data[$name] = array_key_exists($name, $data) ? $data[$name] : 0;
            }

            if ($type === 'taglist') {
                $tags = [];
                foreach (explode(',', (string) ($data[$name] ?? '')) as $tag) {
                    $tag = StringUtil::uppercase(trim($tag));
                    if ($tag !== '' && ! in_array($tag, $tags, true)) {
                        $tags[] = $tag;
                    }
                }
                $data[$name] = $tags;
            }

            if (($field['upcase'] ?? false) && isset($data[$name])) {
                $data[$name] = StringUtil::uppercase((string) $data[$name]);
            }
        }

        return $data;
    }
}