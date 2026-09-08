<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\Employee\Employee;
use App\Models\Employee\Mutation;
use App\Support\MasterModules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeController extends BaseController
{
    /**
     * Profil lengkap karyawan: foto, data pribadi, posisi, kontrak,
     * riwayat karir (timeline) dan alamat.
     */
    public function showProfile(Request $request, string $id): View
    {
        $employee = $this->loadProfile($id);

        return view('admin.employee.profile', [
            'employee' => $employee,
            'backUrl' => $this->moduleIndexUrl($request),
        ]);
    }

    /**
     * Form promosi/demosi khusus: pilih posisi baru, simpan sebagai mutasi.
     */
    public function promotionForm(Request $request, string $id): View
    {
        $employee = Employee::with(['company', 'department', 'jobLevel', 'jobTitle', 'supervisor'])
            ->findOrFail($id);

        return view('admin.employee.promotion-form', [
            'employee' => $employee,
            'fields' => $this->promotionFields(),
            'backUrl' => $this->moduleIndexUrl($request),
        ]);
    }

    public function storePromotion(Request $request, string $id): RedirectResponse
    {
        $employee = Employee::findOrFail($id);

        $data = $request->validate([
            'type' => ['required', Rule::in(['p', 'd'])],
            'new_company_id' => ['nullable', 'exists:companies,id'],
            'new_department_id' => ['nullable', 'exists:departments,id'],
            'new_job_level_id' => ['nullable', 'exists:job_levels,id'],
            'new_job_title_id' => ['nullable', 'exists:job_titles,id'],
            'new_supervisor_id' => ['nullable', 'exists:employees,id'],
            'contract_id' => ['nullable', 'exists:contracts,id'],
        ]);

        Mutation::create(['employee_id' => $employee->getKey()] + $data);

        return redirect()->route('admin.employee.employees.profile', $employee)
            ->with('success', 'Promosi/demosi berhasil diterapkan.');
    }

    protected function loadProfile(string $id): Employee
    {
        return Employee::with([
            'company',
            'department',
            'jobLevel',
            'jobTitle',
            'supervisor',
            'contract',
            'addresses.city',
            'addresses.region',
            'careerHistories.jobTitle',
            'careerHistories.jobLevel',
            'careerHistories.company',
            'careerHistories.department',
        ])->findOrFail($id);
    }

    protected function promotionFields(): array
    {
        $mutation = MasterModules::get('mutations');
        $keep = ['type', 'new_company_id', 'new_department_id', 'new_job_level_id', 'new_job_title_id', 'new_supervisor_id', 'contract_id'];

        $fields = collect($mutation['fields'])
            ->filter(fn (array $field) => in_array($field['name'], $keep, true))
            ->values()
            ->all();

        $fields[0]['options'] = [
            ['value' => 'p', 'label' => 'PROMOSI'],
            ['value' => 'd', 'label' => 'DEMOSI'],
        ];

        return $fields;
    }

    protected function moduleIndexUrl(Request $request): string
    {
        return route('admin.'.$request->route('menuKey').'.'.$request->route('moduleKey').'.index');
    }
}
