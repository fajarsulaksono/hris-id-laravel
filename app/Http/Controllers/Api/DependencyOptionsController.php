<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company\CompanyDepartment;
use App\Models\Company\Department;
use App\Models\Master\City;
use App\Models\Master\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DependencyOptionsController extends Controller
{
    /**
     * Opsi terfilter untuk select dependen (kota↔propinsi, departemen↔perusahaan).
     */
    public function options(Request $request, string $type): JsonResponse
    {
        $parent = $request->input('parent_id');

        return match ($type) {
            'region' => $this->toOptions(Region::query()),
            'city' => $this->toOptions(
                City::query()->when($parent, fn ($q) => $q->where('region_id', $parent))
            ),
            'department-by-company' => $this->toOptions(
                Department::query()
                    ->when($parent, fn ($q) => $q->whereIn('id', function ($query) use ($parent) {
                        $query->select('department_id')
                            ->from((new CompanyDepartment())->getTable())
                            ->where('company_id', $parent);
                    }))
            ),
            default => abort(404),
        };
    }

    protected function toOptions($query): JsonResponse
    {
        $rows = $query->when(request()->input('q') !== null, function ($q) {
            $search = trim((string) request()->input('q'));

            $q->where(fn ($where) => $where->where('code', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%"));
        })->orderBy('name')->get();

        return response()->json($rows->map(fn ($row) => [
            'id' => $row->getKey(),
            'text' => method_exists($row, 'getDisplayAttribute') ? $row->display : $row->name,
        ]));
    }
}