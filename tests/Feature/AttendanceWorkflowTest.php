<?php

namespace Tests\Feature;

use App\Enums\ReasonType;
use App\Models\Attendance\Attendance;
use App\Models\Attendance\Overtime;
use App\Models\Attendance\Shiftment;
use App\Models\Attendance\Workshift;
use App\Models\Employee\Employee;
use App\Models\Master\Reason;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AttendanceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_guest_cannot_access_attendance_pages(): void
    {
        $this->get(route('admin.attendance.attendances.upload'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.attendance.attendances.process'))
            ->assertRedirect(route('login'));
    }

    public function test_upload_attendance_csv_creates_rows(): void
    {
        $staff = $this->staff();

        $this->actingAs($staff)
            ->post(route('admin.attendance.attendances.upload'), [
                'file' => UploadedFile::fake()->createWithContent(
                    'attendance.txt',
                    "employee_code,date,check_in,check_out,reason_code\nEMP001,05-01-2026,08:00,17:00,\n"
                ),
            ])
            ->assertRedirect(route('admin.attendance.attendances.upload'));

        $this->assertDatabaseCount('attendances', 1);

        $attendance = Attendance::whereDate('attendance_date', '2026-01-05')->first();

        $this->assertNotNull($attendance);
        $this->assertSame('08:00:00', $attendance->check_in);
        $this->assertSame('17:00:00', $attendance->check_out);
    }

    public function test_upload_rejects_non_csv_file(): void
    {
        $staff = $this->staff();

        $this->actingAs($staff)
            ->post(route('admin.attendance.attendances.upload'), [
                'file' => UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'),
            ])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_process_page_requires_period_input(): void
    {
        $staff = $this->staff();

        $this->actingAs($staff)
            ->post(route('admin.attendance.attendances.process'), [])
            ->assertSessionHasErrors(['year', 'month']);
    }

    public function test_process_backfills_absences(): void
    {
        $staff = $this->staff();

        Reason::create([
            'type' => ReasonType::ABSENT,
            'code' => 'ABS',
            'name' => 'ALFA',
        ]);

        $year = now()->format('Y');
        $month = now()->format('n');

        $this->actingAs($staff)
            ->post(route('admin.attendance.attendances.process'), [
                'year' => $year,
                'month' => $month,
            ])
            ->assertRedirect(route('admin.attendance.attendances.process'))
            ->assertSessionHas('success');

        $employee = Employee::where('username', 'sari.wulandari')->first();

        $this->assertGreaterThan(0, Attendance::where('employee_id', $employee->getKey())->where('absent', true)->count());
    }

    public function test_recap_page_renders_for_current_month(): void
    {
        $staff = $this->staff();

        $this->actingAs($staff)
            ->get(route('admin.attendance.attendances.recap', [
                'year' => now()->format('Y'),
                'month' => now()->format('n'),
            ]))
            ->assertOk();
    }

    public function test_upload_overtime_csv_creates_rows(): void
    {
        $staff = $this->staff();
        $employee = Employee::where('username', 'sari.wulandari')->first();

        $shiftment = Shiftment::create([
            'code' => 'SH1',
            'name' => 'SHIFT 1',
            'start_hour' => '08:00:00',
            'end_hour' => '17:00:00',
        ]);

        Workshift::create([
            'employee_id' => $employee->getKey(),
            'shiftment_id' => $shiftment->getKey(),
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
        ]);

        Attendance::create([
            'employee_id' => $employee->getKey(),
            'attendance_date' => '2026-01-05',
            'check_in' => '08:00:00',
            'check_out' => '21:00:00',
            'absent' => false,
        ]);

        $this->actingAs($staff)
            ->post(route('admin.overtime.overtimes.upload'), [
                'file' => UploadedFile::fake()->createWithContent(
                    'overtime.txt',
                    "employee_code,date,check_in,check_out\nEMP001,05-01-2026,18:00,20:00\n"
                ),
            ])
            ->assertRedirect(route('admin.overtime.overtimes.upload'));

        $this->assertDatabaseCount('overtimes', 1);

        $overtime = Overtime::whereDate('overtime_date', '2026-01-05')->first();

        $this->assertNotNull($overtime);
        $this->assertSame('18:00:00', $overtime->start_hour);
        $this->assertSame('20:00:00', $overtime->end_hour);
    }

    private function staff(): Employee
    {
        $employee = Employee::create([
            'code' => 'EMP001',
            'full_name' => 'Sari Wulandari',
            'username' => 'sari.wulandari',
            'email' => 'sari.wulandari@example.test',
            'password' => Hash::make('password123'),
            'join_date' => '2020-01-01',
            'date_of_birth' => '1990-09-25',
            'identity_number' => '3174012509900009',
        ]);

        $employee->assignRole('HRSTAFF');

        return $employee;
    }
}