<?php

namespace Tests\Feature;

use App\Enums\ApprovalStatus;
use App\Enums\ReasonType;
use App\Models\Attendance\Leave;
use App\Models\Attendance\Overtime;
use App\Models\Employee\Employee;
use App\Models\Master\Reason;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApprovalTest extends TestCase
{
    use RefreshDatabase;

    private int $sequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_hrstaff_can_approve_pending_leave(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');
        $hr = $this->employee('sari.wulandari', 'HRSTAFF');
        $reason = Reason::create(['type' => ReasonType::LEAVE, 'code' => 'CTH', 'name' => 'CUTI TAHUNAN']);
        $leave = Leave::create([
            'employee_id' => $employee->getKey(),
            'leave_date' => '2026-09-15',
            'reason_id' => $reason->getKey(),
            'amount' => 2,
        ]);

        $this->actingAs($hr)->post(route('admin.approvals.leaves.approve', $leave))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('leaves', [
            'id' => $leave->getKey(),
            'status' => ApprovalStatus::APPROVED->value,
            'approved_by_id' => $hr->getKey(),
        ]);
    }

    public function test_hrstaff_can_reject_pending_leave(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');
        $hr = $this->employee('sari.wulandari', 'HRSTAFF');
        $reason = Reason::create(['type' => ReasonType::LEAVE, 'code' => 'CTH', 'name' => 'CUTI TAHUNAN']);
        $leave = Leave::create([
            'employee_id' => $employee->getKey(),
            'leave_date' => '2026-09-15',
            'reason_id' => $reason->getKey(),
            'amount' => 1,
        ]);

        $this->actingAs($hr)->post(route('admin.approvals.leaves.reject', $leave))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('leaves', [
            'id' => $leave->getKey(),
            'status' => ApprovalStatus::REJECTED->value,
            'approved_by_id' => $hr->getKey(),
        ]);
    }

    public function test_cannot_approve_already_approved_leave(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');
        $hr = $this->employee('sari.wulandari', 'HRSTAFF');
        $reason = Reason::create(['type' => ReasonType::LEAVE, 'code' => 'CTH', 'name' => 'CUTI TAHUNAN']);
        $leave = Leave::create([
            'employee_id' => $employee->getKey(),
            'leave_date' => '2026-09-15',
            'reason_id' => $reason->getKey(),
            'amount' => 1,
            'status' => ApprovalStatus::APPROVED,
            'approved_by_id' => $hr->getKey(),
        ]);

        $this->actingAs($hr)->post(route('admin.approvals.leaves.approve', $leave))
            ->assertStatus(422);
    }

    public function test_hrstaff_can_approve_pending_overtime(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');
        $hr = $this->employee('sari.wulandari', 'HRSTAFF');
        $overtime = Overtime::create([
            'employee_id' => $employee->getKey(),
            'overtime_date' => '2026-09-15',
            'start_hour' => '18:00',
            'end_hour' => '20:00',
            'raw_value' => 2.0,
            'calculated_value' => 2.0,
        ]);

        $this->actingAs($hr)->post(route('admin.approvals.overtimes.approve', $overtime))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('overtimes', [
            'id' => $overtime->getKey(),
            'status' => ApprovalStatus::APPROVED->value,
            'approved_by_id' => $hr->getKey(),
        ]);
    }

    public function test_employee_cannot_access_approvals(): void
    {
        $employee = $this->employee('budi.santoso', 'EMPLOYEE');

        $this->actingAs($employee)->get(route('admin.approvals.leaves'))->assertForbidden();
    }

    private function employee(string $username, string $role): Employee
    {
        $sequence = ++$this->sequence;

        $employee = Employee::create([
            'code' => Str::upper(str_pad((string) $sequence, 3, '0', STR_PAD_LEFT)),
            'full_name' => Str::title(str_replace('.', ' ', $username)),
            'username' => $username,
            'email' => $username.'@example.test',
            'password' => Hash::make('password123'),
            'join_date' => '2020-01-01',
            'date_of_birth' => '1990-09-25',
            'identity_number' => '31740125099000'.str_pad((string) $sequence, 2, '0', STR_PAD_LEFT),
        ]);

        $employee->assignRole($role);

        return $employee;
    }
}
