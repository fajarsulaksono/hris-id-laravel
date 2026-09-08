<?php

namespace Database\Seeders;

use App\Domain\Salary\Service\PayrollProcessor as PayrollProcessorService;
use App\Domain\Tax\Service\TaxProcessor as TaxProcessorService;
use App\Enums\ContractType;
use App\Enums\Gender;
use App\Enums\IdentityType;
use App\Enums\MaritalStatus;
use App\Enums\ReasonType;
use App\Enums\RiskRatio;
use App\Enums\TaxGroup;
use App\Models\Attendance\Attendance;
use App\Models\Attendance\Leave;
use App\Models\Attendance\Overtime;
use App\Models\Attendance\Shiftment;
use App\Models\Attendance\Workshift;
use App\Models\Company\Company;
use App\Models\Company\Department;
use App\Models\Company\JobLevel;
use App\Models\Company\JobTitle;
use App\Models\Employee\Employee;
use App\Models\Employee\EmployeeAddress;
use App\Models\Employee\Placement;
use App\Models\Master\City;
use App\Models\Master\Holiday;
use App\Models\Master\Reason;
use App\Models\Master\Region;
use App\Models\Payroll\PayrollPeriod;
use App\Models\Payroll\SalaryAllowance;
use App\Models\Payroll\SalaryBenefit;
use App\Models\Payroll\SalaryComponent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Data dummy siklus penuh untuk demo:
 * - ~50 karyawan fiktif lintas perusahaan/departemen/level + penempatan, alamat,
 *   benefit gaji, jadwal kerja;
 * - absensi + lembur + cuti untuk beberapa bulan terakhir;
 * - payroll/BPJS/PPH21 pada periode yang sama (periode ditutup via TaxProcessor),
 *   sehingga dashboard, modul absensi/lembur/cuti, slip, dan rekap berisi data.
 *
 * Idempotent: jika sudah ada dummy (kode karyawan E0001) tidak akan mengerjakan ulang.
 * Jumlah karyawan & bulan diatur lewat config hris.seed_dummy / env HRIS_SEED_DUMMY_*.
 */
class DummyDataSeeder extends Seeder
{
    /** Kode dummy pertama dipakai sebagai penanda sudah pernah di-seed. */
    private const FIRST_DUMMY_CODE = 'E0001';

    /** Kode-nomor dummy = E0001..E9999; kode demo (EMP001, HR001, dst) tidak ditabrak. */
    private const DEMO_POSITIONS = [
        'EMP001' => ['company' => 'C001', 'department' => 'DV', 'title' => 'DV01'],
        'HR001' => ['company' => 'C001', 'department' => 'HR', 'title' => 'HM01'],
        'HR002' => ['company' => 'C001', 'department' => 'HR', 'title' => 'HM02'],
        'HR003' => ['company' => 'C001', 'department' => 'HR', 'title' => 'HM03'],
        'HR004' => ['company' => 'C001', 'department' => 'HR', 'title' => 'HM05'],
        'DIR001' => ['company' => 'C001', 'department' => 'HR', 'title' => 'HM04'],
        'TOP001' => ['company' => 'C001', 'department' => 'HR', 'title' => 'HM06'],
        'SA001' => ['company' => 'C001', 'department' => 'IT', 'title' => 'IT04'],
    ];

    /** Rantai atasan eksplisit untuk user demo (sisanya diselesaikan otomatis). */
    private const DEMO_SUPERVISORS = [
        'HR001' => 'HR002',
        'HR002' => 'HR003',
        'HR003' => 'HR004',
        'HR004' => 'DIR001',
        'DIR001' => 'TOP001',
        'TOP001' => 'SA001',
        'SA001' => null,
    ];

    /** Posisi/level untuk karyawan dummy, format: kode => [nama, level]. */
    private const EXTRA_TITLES = [
        'FN01' => ['FINANCE STAFF', 'ST'],
        'FN02' => ['FINANCE SUPERVISOR', 'SP'],
        'FN03' => ['FINANCE MANAGER', 'MG'],
        'IT01' => ['IT STAFF', 'ST'],
        'IT02' => ['IT SUPERVISOR', 'SP'],
        'IT03' => ['IT MANAGER', 'MG'],
        'IT04' => ['DIREKTUR IT', 'DR'],
        'DV01' => ['SOFTWARE ENGINEER', 'ST'],
        'DV02' => ['SENIOR SOFTWARE ENGINEER', 'SP'],
        'DV03' => ['ENGINEERING MANAGER', 'MG'],
        'HM05' => ['HR GENERAL MANAGER', 'MG'],
        'HM06' => ['DIREKTUR UTAMA', 'DR'],
    ];

    /** Level index dipakai menentukan hirarki atasan. */
    private const LEVEL_INDEX = [
        'GR' => 1,
        'ST' => 2,
        'SP' => 3,
        'MG' => 4,
        'DR' => 5,
    ];

    private const GENDERS = [Gender::MALE, Gender::FEMALE];

    private const BANDS = [
        'ST' => ['gp' => [5_200_000, 6_500_000], 'tj' => 250_000, 'meal' => 350_000],
        'SP' => ['gp' => [7_200_000, 9_000_000], 'tj' => 800_000, 'meal' => 500_000],
        'MG' => ['gp' => [11_500_000, 15_000_000], 'tj' => 2_000_000, 'meal' => 750_000],
        'DR' => ['gp' => [30_000_000, 45_000_000], 'tj' => 6_000_000, 'meal' => 1_000_000],
    ];

    /** @var array<string, Company> */
    private array $companies = [];

    /** @var array<string, Department> */
    private array $departments = [];

    /** @var array<string, JobLevel> */
    private array $levels = [];

    /** @var array<string, JobTitle> */
    private array $titles = [];

    /** @var array<string, Region> */
    private array $regions = [];

    /** @var array<string, City> */
    private array $cities = [];

    /** @var array<string, Reason> */
    private array $absentReasons = [];

    /** @var array<string, Employee> */
    private array $employees = [];

    private ?Reason $cutiReason = null;

    private ?Shiftment $shiftment = null;

    public function run(): void
    {
        $months = $this->anchorMonths();

        $this->seedBase();

        if (Employee::query()->where('code', self::FIRST_DUMMY_CODE)->exists()) {
            $this->output('Data dummy sudah ada. Seeder dilewati.');

            return;
        }

        DB::transaction(function () use ($months): void {
            $this->prepareMaster();
            $this->createEmployees();
            $this->organizeEmployees();
            $this->createAddresses();
            $this->createBenefits();
            $this->createAllowances($months);
            $this->createWorkshifts($months);
            $this->createAttendanceCycle($months);
            $this->processPayroll($months);
        });

        $this->output(sprintf('Seeder data dummy selesai: %d karyawan, %d bulan, %d perusahaan.',
            count($this->employees),
            count($months),
            count($this->companies)
        ));
    }

    /**
     * Base seeder yang menjadi prasyarat, semuanya idempotent.
     */
    private function seedBase(): void
    {
        $this->call([
            RoleSeeder::class,
            DemoUserSeeder::class,
            MasterDataSeeder::class,
            SalaryComponentSeeder::class,
        ]);
    }

    /**
     * Bulan yang di-seed: N bulan terakhir yang sudah berjalan penuh, urut naik.
     *
     * @return array<int, string> format Y-m
     */
    private function anchorMonths(): array
    {
        $count = (int) config('hris.seed_dummy.months', 3);
        $months = [];

        $anchor = Carbon::now()->firstOfMonth()->subMonthNoOverflow();

        for ($i = $count - 1; $i >= 0; $i--) {
            $months[] = $anchor->copy()->subMonthsNoOverflow($i)->format('Y-m');
        }

        return $months;
    }

    /**
     * Shiftment, job title tambahan, dan region/kota pelengkap (idempotent).
     */
    private function prepareMaster(): void
    {
        $this->companies = Company::query()->orderBy('code')->get()->keyBy('code')->all();
        $this->departments = Department::query()->get()->keyBy('code')->all();
        $this->levels = JobLevel::query()->get()->keyBy('code')->all();
        $this->regions = Region::query()->get()->keyBy('code')->all();
        $this->cities = City::query()->get()->keyBy('code')->all();

        foreach (self::EXTRA_TITLES as $code => [$name, $levelCode]) {
            JobTitle::firstOrCreate(['code' => $code], [
                'name' => $name,
                'job_level_id' => $this->levels[$levelCode]->getKey(),
            ]);
        }

        $this->titles = JobTitle::query()->get()->keyBy('code')->all();

        $this->addExtraCities();

        $this->shiftment = Shiftment::query()->first();

        if (! $this->shiftment) {
            $this->shiftment = Shiftment::create([
                'code' => 'REG',
                'name' => 'REGULER 08.00-17.00',
                'start_hour' => '08:00:00',
                'end_hour' => '17:00:00',
            ]);
        }

        $this->cutiReason = Reason::query()
            ->where('type', ReasonType::LEAVE)
            ->where('code', 'C')
            ->first();

        foreach (['S', 'I', 'A'] as $code) {
            $this->absentReasons[$code] = Reason::query()
                ->where('type', ReasonType::ABSENT)
                ->where('code', $code)
                ->first();
        }
    }

    /**
     * Tambah beberapa region/kota agar data kelahiran/alamat lebih beragam.
     */
    private function addExtraCities(): void
    {
        $semarang = Region::firstOrCreate(['code' => 'R04'], ['name' => 'JAWA TENGAH']);
        $bali = Region::firstOrCreate(['code' => 'R05'], ['name' => 'BALI']);

        $newCities = [
            'SMG01' => ['KOTA SEMARANG', $semarang],
            'SRK01' => ['KOTA SURAKARTA', $semarang],
            'DPS01' => ['KOTA DENPASAR', $bali],
        ];

        foreach ($newCities as $code => [$name, $region]) {
            City::firstOrCreate(['code' => $code], ['name' => $name, 'region_id' => $region->getKey()]);
        }

        $this->regions = Region::query()->get()->keyBy('code')->all();
        $this->cities = City::query()->get()->keyBy('code')->all();
    }

    /**
     * Buat karyawan dummy (E0001..E9999) lalu lengkapi organisasi user demo.
     */
    private function createEmployees(): void
    {
        $total = max(1, (int) config('hris.seed_dummy.employees', 50));
        $secondCount = min(max(1, intdiv($total, 5)), $total - 1);
        $firstCount = $total - $secondCount;

        $roster = [
            ...$this->makeRoster('C001', $firstCount),
            ...$this->makeRoster('C002', $secondCount),
        ];

        $this->loadDemoEmployees();

        $seq = 0;
        foreach ($roster as $i => [$companyCode, $levelCode, $titleCode]) {
            $seq = $i + 1;
            $code = sprintf('E%04d', $seq);
            $gender = self::GENDERS[$this->hashInt($code, 'gender') % 2];

            $employee = new Employee;
            $employee->code = $code;
            $employee->full_name = $this->randomName($gender, $code);
            $employee->username = sprintf('karyawan.%04d', $seq);
            $employee->email = sprintf('karyawan.%04d@semart.test', $seq);
            $employee->password = Hash::make((string) config('hris.default_password', '1234567890'));
            $employee->employee_status = ContractType::PERMANENT;
            $employee->gender = $gender;
            $employee->company_id = $this->companies[$companyCode]->getKey();
            $employee->department_id = $this->departments[$this->departmentOf($titleCode)]->getKey();
            $employee->job_level_id = $this->levels[$levelCode]->getKey();
            $employee->job_title_id = $this->titles[$titleCode]->getKey();
            $employee->join_date = $this->randomDate($code, 2017, 2024)->format('Y-m-d');
            $employee->date_of_birth = $this->randomDate($code, 1974, 2004)->format('Y-m-d');
            $cityCode = $this->randomCityKey($code, 'city');
            $employee->city_of_birth_id = $this->cities[$cityCode]->getKey();
            $employee->region_of_birth_id = $this->cities[$cityCode]->region_id;
            $employee->identity_number = $this->nikFor($seq);
            $employee->identity_type = IdentityType::ID_CARD;
            $employee->marital_status = $this->maritalStatus($code);
            $employee->tax_group = $this->taxGroup($employee->marital_status);
            $employee->risk_ratio = $this->riskRatio($code);
            $employee->leave_balance = 12;
            $employee->have_overtime_benefit = ($this->hashInt($code, 'ot') % 3) !== 0;
            $employee->save();

            $this->employees[$code] = $employee;
        }

        $this->output(sprintf('Karyawan dummy dibuat: %d (perusahaan %s: %d, %s: %d).', $seq, 'C001', $firstCount, 'C002', $secondCount));
    }

    /**
     * @return array<int, array{0: string, 1: string, 2: string}> [company, level, title]
     */
    private function makeRoster(string $companyCode, int $count): array
    {
        $deptCodes = ['HR', 'FN', 'IT', 'DV'];
        $titleBy = [
            'HR' => ['MG' => 'HM03', 'SP' => 'HM02', 'ST' => 'HM01'],
            'FN' => ['MG' => 'FN03', 'SP' => 'FN02', 'ST' => 'FN01'],
            'IT' => ['MG' => 'IT03', 'SP' => 'IT02', 'ST' => 'IT01'],
            'DV' => ['MG' => 'DV03', 'SP' => 'DV02', 'ST' => 'DV01'],
        ];

        $roster = [];
        $idx = 0;

        foreach ($deptCodes as $dept) {
            if ($idx >= $count) {
                break;
            }
            $roster[] = [$companyCode, 'MG', $titleBy[$dept]['MG']];
            $idx++;
        }

        foreach ($deptCodes as $dept) {
            if ($idx >= $count) {
                break;
            }
            $roster[] = [$companyCode, 'SP', $titleBy[$dept]['SP']];
            $idx++;
        }

        foreach ($deptCodes as $dept) {
            if ($idx >= $count) {
                break;
            }
            $roster[] = [$companyCode, 'SP', $titleBy[$dept]['SP']];
            $idx++;
        }

        $i = 0;
        while ($idx < $count) {
            $dept = $deptCodes[$i % 4];
            $roster[] = [$companyCode, 'ST', $titleBy[$dept]['ST']];
            $idx++;
            $i++;
        }

        return $roster;
    }

    private function departmentOf(string $titleCode): string
    {
        if (str_starts_with($titleCode, 'FN')) {
            return 'FN';
        }
        if (str_starts_with($titleCode, 'IT')) {
            return 'IT';
        }
        if (str_starts_with($titleCode, 'DV')) {
            return 'DV';
        }

        return 'HR';
    }

    private function loadDemoEmployees(): void
    {
        $codes = array_keys(self::DEMO_POSITIONS);

        foreach (Employee::query()->whereIn('code', $codes)->get() as $employee) {
            $this->employees[$employee->code] = $employee;
        }
    }

    /**
     * Isi organisasi (perusahaan/departemen/jabatan) untuk karyawan dummy,
     * posisi untuk user demo, lalu atasan serta penempatan/riwayat karir.
     */
    private function organizeEmployees(): void
    {
        foreach (self::DEMO_POSITIONS as $code => $position) {
            $employee = $this->employees[$code] ?? null;

            if (! $employee) {
                continue;
            }

            $employee->company_id = $this->companies[$position['company']]->getKey();
            $employee->department_id = $this->departments[$position['department']]->getKey();
            $title = $this->titles[$position['title']];
            $employee->job_title_id = $title->getKey();
            $employee->job_level_id = $title->job_level_id;
        }

        $this->assignSupervisors();

        foreach ($this->employees as $employee) {
            $employee->save();

            Placement::firstOrCreate([
                'employee_id' => $employee->getKey(),
            ], [
                'company_id' => $employee->company_id,
                'department_id' => $employee->department_id,
                'job_level_id' => $employee->job_level_id,
                'job_title_id' => $employee->job_title_id,
                'supervisor_id' => $employee->supervisor_id,
                'active' => true,
            ]);
        }
    }

    private function assignSupervisors(): void
    {
        $byDept = [];

        foreach ($this->employees as $code => $employee) {
            $key = implode('|', [$employee->company_id, $employee->department_id]);
            $byDept[$key][] = $employee;
        }

        foreach ($this->employees as $code => $employee) {
            if (array_key_exists($code, self::DEMO_SUPERVISORS)) {
                $supervisorCode = self::DEMO_SUPERVISORS[$code];
                $employee->supervisor_id = $supervisorCode !== null ? ($this->employees[$supervisorCode]->getKey() ?? null) : null;

                continue;
            }

            $employee->supervisor_id = $this->findSupervisor($employee, $byDept);
        }
    }

    /**
     * @param  array<string, Employee[]>  $byDept
     */
    private function findSupervisor(Employee $employee, array $byDept): ?string
    {
        $level = self::LEVEL_INDEX[$employee->jobLevel?->code ?? 'GR'] ?? 1;
        $candidates = [];

        foreach ($byDept as $key => $list) {
            [$companyId, $departmentId] = explode('|', $key);

            if ($departmentId !== $employee->department_id) {
                continue;
            }

            if ($companyId === $employee->company_id) {
                foreach ($list as $peer) {
                    $peerLevel = self::LEVEL_INDEX[$peer->jobLevel?->code ?? 'GR'] ?? 1;
                    if ($peer->getKey() !== $employee->getKey() && $peerLevel > $level) {
                        $candidates[] = $peer;
                    }
                }
            }
        }

        usort($candidates, function (Employee $a, Employee $b): int {
            $levelCompare = (self::LEVEL_INDEX[$a->jobLevel?->code ?? 'GR'] ?? 1) <=> (self::LEVEL_INDEX[$b->jobLevel?->code ?? 'GR'] ?? 1);

            return $levelCompare === 0 ? strcmp((string) $a->code, (string) $b->code) : $levelCompare;
        });

        if (count($candidates) > 0) {
            return $candidates[0]->getKey();
        }

        $fallback = $this->employees['HR004'] ?? null;

        return $fallback?->getKey();
    }

    /**
     * Satu alamat default per karyawan.
     */
    private function createAddresses(): void
    {
        foreach ($this->employees as $code => $employee) {
            if (EmployeeAddress::query()->where('employee_id', $employee->getKey())->exists()) {
                continue;
            }

            $cityCode = $this->randomCityKey($code.'addr', 'city');
            $city = $this->cities[$cityCode];
            $region = $this->regions[$city->region_id] ?? null;

            $address = EmployeeAddress::create([
                'employee_id' => $employee->getKey(),
                'address' => sprintf('JL. CONTOH NO. %d, KEL. DEMO', $this->hashInt($code.'addr', 'no') % 90 + 1),
                'region_id' => $region?->getKey() ?? $city->region_id,
                'city_id' => $city->getKey(),
                'postal_code' => sprintf('%05d', $this->hashInt($code, 'postal') % 90000 + 10000),
                'phone_number' => sprintf('08%09d', $this->hashInt($code, 'phone') % 1000000000),
                'fax_number' => null,
                'default_address' => true,
            ]);

            $employee->address_id = $address->getKey();
            $employee->save();
        }
    }

    /**
     * Benefit tetap (GAJI POKOK + TUNJANGAN JABATAN) per karyawan.
     */
    private function createBenefits(): void
    {
        foreach ($this->employees as $code => $employee) {
            $level = $employee->jobLevel?->code ?? 'ST';
            $band = self::BANDS[$level] ?? self::BANDS['ST'];

            $this->addBenefit($employee, 'GP', $this->randomInRange($band['gp'][0], $band['gp'][1], $code.'gp'));
            $this->addBenefit($employee, 'TJ', $band['tj']);
        }
    }

    private function addBenefit(Employee $employee, string $componentCode, int $value): void
    {
        $component = SalaryComponent::query()->where('code', $componentCode)->first();

        if (! $component) {
            return;
        }

        SalaryBenefit::firstOrCreate([
            'employee_id' => $employee->getKey(),
            'component_id' => $component->getKey(),
        ], [
            'benefit_value' => $value,
        ]);
    }

    /**
     * Tunjangan bulanan (uang makan untuk semua, potongan lain untuk sebagian).
     *
     * @param  array<int, string>  $months
     */
    private function createAllowances(array $months): void
    {
        $meal = SalaryComponent::query()->where('code', 'UM')->first();
        $otherDeduction = SalaryComponent::query()->where('code', 'PL')->first();

        foreach ($this->employees as $code => $employee) {
            $level = $employee->jobLevel?->code ?? 'ST';
            $band = self::BANDS[$level] ?? self::BANDS['ST'];

            foreach ($months as $month) {
                $this->addAllowance($employee, $meal, $month, $band['meal']);
            }

            if ($otherDeduction && $this->hashInt($code, 'pl') % 4 === 0) {
                foreach ($months as $month) {
                    $this->addAllowance($employee, $otherDeduction, $month, $this->randomInRange(100_000, 250_000, $code.'pl'));
                }
            }
        }
    }

    private function addAllowance(Employee $employee, ?SalaryComponent $component, string $month, int $value): void
    {
        if (! $component) {
            return;
        }

        [$year, $monthNumber] = explode('-', $month);

        SalaryAllowance::firstOrCreate([
            'employee_id' => $employee->getKey(),
            'component_id' => $component->getKey(),
            'year' => (int) $year,
            'month' => (int) $monthNumber,
        ], [
            'benefit_value' => $value,
        ]);
    }

    /**
     * Jadwal kerja reguler per karyawan untuk rentang bulan yang di-seed.
     *
     * @param  array<int, string>  $months
     */
    private function createWorkshifts(array $months): void
    {
        $start = Carbon::createFromFormat('Y-m-d', $months[0].'-01');
        $end = Carbon::createFromFormat('Y-m-d', $months[count($months) - 1].'-01')->endOfMonth();

        foreach ($this->employees as $code => $employee) {
            if (Workshift::query()->where('employee_id', $employee->getKey())->exists()) {
                continue;
            }

            Workshift::create([
                'employee_id' => $employee->getKey(),
                'shiftment_id' => $this->shiftment->getKey(),
                'description' => 'JADWAL REGULER',
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
            ]);
        }
    }

    /**
     * Absensi harian + lembur + cuti untuk rentang bulan yang di-seed.
     *
     * @param  array<int, string>  $months
     */
    private function createAttendanceCycle(array $months): void
    {
        $leavePlans = $this->makeLeavePlans($months);

        foreach ($this->employees as $code => $employee) {
            $this->output(sprintf('  - absensi %s (%s)', $employee->code, $employee->full_name));

            foreach ($months as $monthIndex => $month) {
                $workdays = $this->workdaysOf($month);
                $leavePlan = $leavePlans[$employee->getKey()] ?? null;
                $leaveDate = $leavePlan !== null && $leavePlan['month'] === $month ? $leavePlan['date'] : null;

                [$sickDate, $alpaDate] = $this->specialAbsentDays($code, $month, $workdays, $leaveDate);
                $overtimeDates = $this->overtimeDates($employee, $month, $workdays, [$sickDate, $alpaDate, $leaveDate]);

                foreach ($workdays as $workday) {
                    $date = $workday->format('Y-m-d');

                    if ($date === $leaveDate) {
                        continue;
                    }

                    if ($date === $sickDate) {
                        $this->createAttendance($employee, $date, null, null, reason: 'S');
                    } elseif ($date === $alpaDate) {
                        $this->createAttendance($employee, $date, null, null, reason: 'A');
                    } elseif (in_array($date, $overtimeDates, true)) {
                        $this->createAttendance($employee, $date, '08:00:00', '20:00:00');
                        $this->createOvertime($employee, $date);
                    } else {
                        [$checkIn, $checkOut] = $this->clockTimes($code, $date);
                        $this->createAttendance($employee, $date, $checkIn, $checkOut);
                    }
                }
            }
        }

        $this->createLeaves($leavePlans);
    }

    /**
     * Rencana cuti: hanya sebagian karyawan, satu kali per siklus seeding.
     *
     * @param  array<int, string>  $months
     * @return array<string, array{date: string, month: string}>
     */
    private function makeLeavePlans(array $months): array
    {
        $plans = [];
        $monthCount = count($months);
        $employees = array_values($this->employees);

        foreach ($employees as $index => $employee) {
            if ($index % 3 !== 0) {
                continue;
            }

            $month = $months[$index % $monthCount];
            $workdays = $this->workdaysOf($month);
            $date = $workdays[$this->hashInt((string) $employee->code, 'leave') % count($workdays)]->format('Y-m-d');

            $plans[$employee->getKey()] = ['month' => $month, 'date' => $date];
        }

        return $plans;
    }

    /**
     * @param  Carbon[]  $workdays
     * @return array{0: string|null, 1: string|null}
     */
    private function specialAbsentDays(string $code, string $month, array $workdays, ?string $leaveDate): array
    {
        $sick = null;
        $alpa = null;

        $total = count($workdays);

        if ($total > 0) {
            if ($this->hashInt($code.$month, 'sick') % 100 < 7) {
                $candidate = $workdays[$this->hashInt($code.$month, 'sickday') % $total]->format('Y-m-d');

                if ($candidate !== $leaveDate) {
                    $sick = $candidate;
                }
            }

            if ($this->hashInt($code.$month, 'alpa') % 100 < 4) {
                $candidate = $workdays[$this->hashInt($code.$month, 'alpaday') % $total]->format('Y-m-d');

                if ($candidate !== $leaveDate && $candidate !== $sick) {
                    $alpa = $candidate;
                }
            }
        }

        return [$sick, $alpa];
    }

    /**
     * @param  Carbon[]  $workdays
     * @param  array<int, string|null>  $excluded
     * @return string[]
     */
    private function overtimeDates(Employee $employee, string $month, array $workdays, array $excluded): array
    {
        $dates = [];

        if (! $employee->have_overtime_benefit) {
            return $dates;
        }

        $excluded = array_filter(array_map('strval', $excluded));
        $candidates = array_values(array_filter($workdays, fn (Carbon $day) => ! in_array($day->format('Y-m-d'), $excluded, true)));
        $total = count($candidates);

        if ($total === 0) {
            return $dates;
        }

        $wanted = 1 + ($this->hashInt((string) $employee->code.$month, 'ot') % 3);
        $startAt = $this->hashInt((string) $employee->code.$month, 'otstart') % $total;

        for ($i = 0; $i < $wanted && count($dates) < $total; $i++) {
            $candidate = $candidates[($startAt + $i) % $total]->format('Y-m-d');

            if (! in_array($candidate, $dates, true)) {
                $dates[] = $candidate;
            }
        }

        return $dates;
    }

    private function createAttendance(Employee $employee, string $date, ?string $checkIn, ?string $checkOut, string $reason = ''): void
    {
        if (Attendance::query()->where('employee_id', $employee->getKey())->whereDate('attendance_date', $date)->exists()) {
            return;
        }

        $attendance = new Attendance;
        $attendance->employee_id = $employee->getKey();
        $attendance->attendance_date = $date;
        $attendance->check_in = $checkIn;
        $attendance->check_out = $checkOut;

        if ($reason !== '') {
            $attendance->absent = true;
            $attendance->reason_id = $this->absentReasons[$reason]?->getKey();
        } else {
            $attendance->absent = false;
        }

        $attendance->save();
    }

    private function createOvertime(Employee $employee, string $date): void
    {
        if (Overtime::query()->where('employee_id', $employee->getKey())->whereDate('overtime_date', $date)->exists()) {
            return;
        }

        $overtime = new Overtime;
        $overtime->employee_id = $employee->getKey();
        $overtime->overtime_date = $date;
        $overtime->start_hour = '18:00:00';
        $overtime->end_hour = '20:00:00';
        $overtime->holiday = false;
        $overtime->overday = false;
        $overtime->approved_by_id = $employee->supervisor_id ?? $this->employees['DIR001']?->getKey();
        $overtime->description = 'LEMBUR PROYEK';
        $overtime->save();
    }

    /**
     * @param  array<string, array{date: string, month: string}>  $leavePlans
     */
    private function createLeaves(array $leavePlans): void
    {
        if (! $this->cutiReason) {
            return;
        }

        foreach ($leavePlans as $employeeId => $plan) {
            if (Leave::query()->where('employee_id', $employeeId)->whereDate('leave_date', $plan['date'])->exists()) {
                continue;
            }

            Leave::create([
                'employee_id' => $employeeId,
                'leave_date' => $plan['date'],
                'reason_id' => $this->cutiReason->getKey(),
                'amount' => 1,
                'description' => 'CUTI TAHUNAN',
            ]);
        }
    }

    /**
     * Jam masuk/pulang deterministik berdasarkan kode karyawan + tanggal.
     *
     * @return array{0: string, 1: string}
     */
    private function clockTimes(string $code, string $date): array
    {
        $inMinutes = 470 + ($this->hashInt($code.$date, 'in') % 35);
        $outMinutes = 1000 + ($this->hashInt($code.$date, 'out') % 35);

        return [
            sprintf('%02d:%02d:00', intdiv($inMinutes, 60), $inMinutes % 60),
            sprintf('%02d:%02d:00', intdiv($outMinutes, 60), $outMinutes % 60),
        ];
    }

    /**
     * @param  array<int, string>  $months
     */
    private function processPayroll(array $months): void
    {
        $salaryProcessor = app(PayrollProcessorService::class);
        $taxProcessor = app(TaxProcessorService::class);

        foreach ($months as $month) {
            $date = Carbon::createFromFormat('Y-m-d', $month.'-01');

            foreach ($this->companies as $company) {
                $employees = Employee::query()
                    ->where('company_id', $company->getKey())
                    ->orderBy('code')
                    ->get();

                if ($employees->isEmpty()) {
                    continue;
                }

                foreach ($employees as $employee) {
                    if ($employee->isResign()) {
                        continue;
                    }

                    $salaryProcessor->process($employee, $date);
                }

                $period = PayrollPeriod::query()
                    ->where('company_id', $company->getKey())
                    ->where('year', (int) $date->format('Y'))
                    ->where('month', (int) $date->format('n'))
                    ->first();

                if (! $period) {
                    continue;
                }

                foreach ($employees as $employee) {
                    if ($employee->isResign()) {
                        continue;
                    }

                    $taxProcessor->process($employee, $period);
                }
            }
        }
    }

    /**
     * Daftar tanggal kerja (senin-jumat tanpa hari libur nasional) pada bulan.
     *
     * @return Carbon[]
     */
    private function workdaysOf(string $month): array
    {
        $holidays = Holiday::query()
            ->whereBetween('holiday_date', [$month.'-01', $month.'-31'])
            ->get()
            ->map(fn ($holiday) => $holiday->holiday_date->format('Y-m-d'))
            ->flip();

        $offDays = array_map('intval', (array) config('hris.offday_per_week'));

        $workdays = [];
        $date = Carbon::createFromFormat('Y-m-d', $month.'-01');

        while ($date->format('Y-m') === $month) {
            if (! in_array((int) $date->format('N'), $offDays, true) && ! isset($holidays[$date->format('Y-m-d')])) {
                $workdays[] = $date->copy();
            }

            $date->addDay();
        }

        return $workdays;
    }

    /**
     * Nama lengkap (dummy) yang deterministik untuk sebuah kode karyawan.
     */
    private function randomName(Gender $gender, string $code): string
    {
        $male = ['AGUS', 'BAMBANG', 'BAGUS', 'BAYU', 'DEDI', 'DENNY', 'DIMAS', 'DONI', 'EKO', 'FERY', 'GALIH', 'GANJAR', 'HARI', 'HENDRA', 'IWAN', 'JOKO', 'KUKUH', 'MAHENDRA', 'NURHADI', 'RADITYA', 'REZA', 'RUDI', 'SANDI', 'TAUFIK', 'WAHYU', 'YUDI', 'ARIF', 'FADLI', 'HADI', 'BUDI'];
        $female = ['AYU', 'DEWI', 'FITRI', 'INTAN', 'LINA', 'MEGA', 'NIA', 'PUTRI', 'RATNA', 'SARI', 'TIA', 'VINA', 'WULAN', 'YULIA', 'ANGGUN', 'CICI', 'EKA', 'FEBRI', 'IKA', 'MAYA', 'NOVI', 'RANI', 'SISKA', 'TANTRI', 'UTARI', 'YANTI', 'ZAHRA', 'LESTARI', 'AULIA', 'FADHILA'];
        $last = ['PRASETYO', 'SANTOSO', 'SAPUTRA', 'WIJAYA', 'KURNIAWAN', 'NUGROHO', 'RAMADHAN', 'HIDAYAT', 'FIRMANSYAH', 'GUNAWAN', 'SETIAWAN', 'SUBEKTI', 'WIBOWO', 'PAMUNGKAS', 'HALIM', 'LESTARI', 'RAHAYU', 'KUSUMA', 'ANGGRAENI', 'PERMATASARI', 'UTAMI', 'HANDAYANI', 'WULANDARI', 'NINGSIH', 'DEWI', 'RAHAYU', 'PUTRI', 'SAPUTRI', 'MAHARANI', 'SULISTYOWATI'];

        $firstList = $gender === Gender::MALE ? $male : $female;
        $first = $firstList[$this->hashInt($code, 'first') % count($firstList)];
        $surname = $last[$this->hashInt($code, 'last') % count($last)];

        return $first.' '.$surname;
    }

    private function randomCityKey(string $code, string $salt): string
    {
        $keys = array_keys($this->cities);

        return $keys[$this->hashInt($code, $salt) % count($keys)];
    }

    private function randomDate(string $code, int $yearStart, int $yearEnd): Carbon
    {
        $year = $yearStart + ($this->hashInt($code.'year', 'd') % ($yearEnd - $yearStart + 1));
        $month = 1 + ($this->hashInt($code.'month', 'd') % 12);
        $day = 1 + ($this->hashInt($code.'day', 'd') % 28);

        return Carbon::createFromDate($year, $month, $day);
    }

    private function nikFor(int $seq): string
    {
        return (string) (3174010100000000 + ($seq * 100003));
    }

    private function maritalStatus(string $code): MaritalStatus
    {
        $roll = $this->hashInt($code, 'marital') % 100;

        if ($roll < 55) {
            return MaritalStatus::SINGLE;
        }

        if ($roll < 95) {
            return MaritalStatus::MARRIED;
        }

        return MaritalStatus::DIVORCED;
    }

    private function taxGroup(MaritalStatus $maritalStatus): TaxGroup
    {
        return $maritalStatus === MaritalStatus::MARRIED ? TaxGroup::K0 : TaxGroup::TK0;
    }

    private function riskRatio(string $code): RiskRatio
    {
        $options = [RiskRatio::RISK_VERY_LOW, RiskRatio::RISK_LOW, RiskRatio::RISK_NORMAL];
        $index = $this->hashInt($code, 'risk') % count($options);

        return $options[$index];
    }

    private function randomInRange(int $min, int $max, string $seed): int
    {
        $step = 100_000;
        $steps = max(1, intdiv($max - $min, $step));

        return $min + (($this->hashInt($seed, 'r') % $steps) * $step);
    }

    private function hashInt(string $seed, string $salt): int
    {
        return crc32($seed.'::'.$salt);
    }

    private function output(string $message): void
    {
        if (isset($this->command)) {
            $this->command->getOutput()->writeln('<info>'.$message.'</info>');
        }
    }
}
