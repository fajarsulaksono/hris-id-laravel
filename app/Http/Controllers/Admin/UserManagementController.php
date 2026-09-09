<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee\Employee;
use App\Support\DataTableServer;
use App\Support\Security;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function __construct(protected Security $security) {}

    public function index(): View
    {
        $columns = $this->columns();

        return view('admin.user.index', [
            'columnsJson' => json_encode($this->jsColumns($columns)),
            'dataUrl' => route('admin.users.data'),
        ]);
    }

    public function data(): JsonResponse
    {
        $columns = $this->columns();

        $query = Employee::query()
            ->whereNull('resign_date')
            ->orderBy('code');

        return response()->json(
            app(DataTableServer::class)->response($query, $columns, ['code', 'full_name', 'username', 'email'])
        );
    }

    public function edit(Employee $employee): View
    {
        abort_if($this->rankOf($employee) > $this->security->userRank(auth()->user()), 403);

        return view('admin.user.edit', [
            'employee' => $employee,
            'roles' => $this->assignableRoles(),
        ]);
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        abort_if($this->rankOf($employee) > $this->security->userRank(auth()->user()), 403);

        $role = $request->string('role')->toString();
        $assignable = $this->assignableRoles();

        if (! in_array($role, $assignable, true)) {
            throw ValidationException::withMessages(['role' => 'Role tidak dapat diberikan.']);
        }

        if ($employee->hasRole('SUPER_ADMIN') && $role !== 'SUPER_ADMIN'
            && Employee::role('SUPER_ADMIN')->where('id', '!=', $employee->getKey())->doesntExist()) {
            throw ValidationException::withMessages(['role' => 'Tidak dapat menghapus SUPER_ADMIN terakhir.']);
        }

        $data = $request->validate([
            'username' => ['required', 'string', 'max:64', Rule::unique('employees', 'username')->ignore($employee->getKey())],
            'email' => ['required', 'email', Rule::unique('employees', 'email')->ignore($employee->getKey())],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $payload = [
            'username' => $data['username'],
            'email' => $data['email'],
        ];

        if (! empty($data['password'])) {
            $payload['password'] = $data['password'];
        }

        $employee->update($payload);
        $employee->syncRoles([$role]);

        return redirect()->route('admin.users.index')
            ->with('success', "Akun {$employee->full_name} berhasil diperbarui.");
    }

    protected function rankOf(Employee $employee): int
    {
        return $this->security->userRank($employee) ?? 0;
    }

    protected function assignableRoles(): array
    {
        $myRank = $this->security->userRank(auth()->user()) ?? 0;

        return collect($this->security->ranks())
            ->filter(fn (int $rank) => $rank <= $myRank)
            ->keys()
            ->all();
    }

    protected function columns(): array
    {
        return [
            ['data' => 'code', 'title' => 'Kode', 'orderable' => true, 'searchable' => true],
            ['data' => 'full_name', 'title' => 'Nama', 'orderable' => true, 'searchable' => true],
            ['data' => 'username', 'title' => 'Username', 'orderable' => true, 'searchable' => true],
            ['data' => 'email', 'title' => 'Email', 'orderable' => true, 'searchable' => true],
            [
                'data' => 'roles_text',
                'title' => 'Role',
                'orderable' => false,
                'searchable' => false,
                'render' => fn (Employee $employee) => view('admin.user.partials.roles-badge', ['roles' => $employee->getRoleNames()])->render(),
            ],
            [
                'data' => 'actions',
                'title' => 'Aksi',
                'orderable' => false,
                'searchable' => false,
                'render' => fn (Employee $employee) => view('admin.user.partials.actions', ['employee' => $employee])->render(),
            ],
        ];
    }

    protected function jsColumns(array $columns): array
    {
        return collect($columns)->map(
            fn (array $column) => array_intersect_key($column, array_flip(['data', 'title', 'orderable', 'searchable']))
        )->all();
    }
}
