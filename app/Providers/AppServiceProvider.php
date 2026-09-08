<?php

namespace App\Providers;

use App\Domain\Attendance\AttendanceCalculator;
use App\Domain\Attendance\AttendanceProcessor;
use App\Domain\Attendance\AttendanceRule;
use App\Domain\Attendance\AttendanceSummaryCalculator;
use App\Domain\Attendance\HolidayChecker;
use App\Domain\Attendance\RuleInterface;
use App\Domain\Attendance\WorkdayCalculator;
use App\Domain\Attendance\WorkshiftFinder;
use App\Domain\Encryptor\Encryptor;
use App\Domain\Encryptor\KeyLoader;
use App\Domain\Overtime\OvertimeCalculator;
use App\Domain\Overtime\OvertimeCalculatorInterface;
use App\Domain\Overtime\OvertimeCalculatorService;
use App\Domain\Overtime\OvertimeChecker;
use App\Domain\Overtime\OvertimeProcessor;
use App\Domain\Salary\PayrollProcessorInterface;
use App\Domain\Salary\Processor\AttendanceProcessor as SalaryAttendanceProcessor;
use App\Domain\Salary\Processor\BpjsProcessor as SalaryBpjsProcessor;
use App\Domain\Salary\Processor\OvertimeProcessor as SalaryOvertimeProcessor;
use App\Domain\Salary\Processor\PayrollProcessor as ChainPayrollProcessor;
use App\Domain\Salary\Processor\SalaryProcessor as PayrollSalaryProcessor;
use App\Domain\Salary\Service\ChangeBenefit;
use App\Domain\Salary\Service\PayrollProcessor as SalaryPayrollProcessor;
use App\Domain\Salary\Service\StoreAsCompanyCost;
use App\Domain\Salary\Service\ValidateBenefit;
use App\Domain\Tax\FirstRateTaxCalculator;
use App\Domain\Tax\FourthRateTaxCalculator;
use App\Domain\Tax\Processor\TaxProcessor as SalaryTaxProcessor;
use App\Domain\Tax\Processor\TaxProcessorInterface;
use App\Domain\Tax\SecondRateTaxCalculator;
use App\Domain\Tax\Service\TaxProcessor as TaxProcessorService;
use App\Domain\Tax\ThirdRateTaxCalculator;
use App\Models\Attendance\Attendance;
use App\Models\Attendance\Leave;
use App\Models\Attendance\Overtime;
use App\Models\Attendance\Workshift;
use App\Models\Employee\Employee;
use App\Models\Employee\EmployeeAddress;
use App\Models\Employee\Mutation;
use App\Models\Employee\Placement;
use App\Models\Payroll\SalaryBenefitHistory;
use App\Models\Tax\TaxGroupHistory;
use App\Observers\AttendanceObserver;
use App\Observers\EmployeeAddressObserver;
use App\Observers\EmployeeObserver;
use App\Observers\LeaveObserver;
use App\Observers\MutationObserver;
use App\Observers\OvertimeObserver;
use App\Observers\PlacementObserver;
use App\Observers\SalaryBenefitHistoryObserver;
use App\Observers\TaxGroupHistoryObserver;
use App\Observers\WorkshiftObserver;
use App\Policies\EmployeePolicy;
use App\Support\Security;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Security::class);

        $this->app->singleton(KeyLoader::class, fn () => new KeyLoader(
            (string) base_path(config('hris.encryption.private_key_path')),
            (string) base_path(config('hris.encryption.public_key_path')),
            (string) config('hris.encryption.passphrase'),
        ));

        $this->app->singleton(Encryptor::class, fn () => new Encryptor(app(KeyLoader::class)));

        $this->app->singleton(HolidayChecker::class, fn () => new HolidayChecker(implode(',', (array) config('hris.offday_per_week'))));

        $this->app->singleton(WorkshiftFinder::class);

        $this->app->singleton(AttendanceCalculator::class, fn () => new AttendanceCalculator(app(WorkshiftFinder::class)));

        $this->app->singleton(WorkdayCalculator::class, fn () => new WorkdayCalculator(app(HolidayChecker::class)));

        $this->app->singleton(RuleInterface::class, fn () => new AttendanceRule([]));

        $this->app->singleton(AttendanceProcessor::class, fn () => new AttendanceProcessor(
            app(RuleInterface::class),
            app(HolidayChecker::class),
            app(WorkshiftFinder::class),
            (string) config('hris.attendance.default_absent_reason_code'),
            (int) config('hris.attendance.cut_off_date'),
        ));

        $this->app->singleton(AttendanceSummaryCalculator::class, fn () => new AttendanceSummaryCalculator(
            app(WorkdayCalculator::class),
            (int) config('hris.attendance.cut_off_date'),
        ));

        $this->app->singleton(OvertimeChecker::class);

        $this->app->singleton(OvertimeCalculator::class, fn () => new OvertimeCalculator([
            new \App\Domain\Overtime\WorkdayCalculator(),
            new \App\Domain\Overtime\HolidayCalculator(),
        ]));

        $this->app->bind(OvertimeCalculatorInterface::class, OvertimeCalculator::class);

        $this->app->singleton(OvertimeCalculatorService::class, fn () => new OvertimeCalculatorService(
            app(OvertimeChecker::class),
            app(OvertimeCalculator::class),
            app(WorkshiftFinder::class),
            (int) config('hris.workday_per_week'),
        ));

        $this->app->singleton(OvertimeProcessor::class, fn () => new OvertimeProcessor(
            (int) config('hris.attendance.cut_off_date'),
        ));

        $this->app->singleton(StoreAsCompanyCost::class);
        $this->app->singleton(ChangeBenefit::class);
        $this->app->singleton(ValidateBenefit::class);

        $this->app->singleton(SalaryOvertimeProcessor::class, fn () => new SalaryOvertimeProcessor(
            app(StoreAsCompanyCost::class),
            (string) config('hris.overtime.benefit_code'),
        ));

        $this->app->singleton(SalaryBpjsProcessor::class, fn () => new SalaryBpjsProcessor(
            app(StoreAsCompanyCost::class),
            (string) config('hris.bpjs.jkk_code'),
            (string) config('hris.bpjs.jkm_code'),
            (string) config('hris.bpjs.jht_plus_code'),
            (string) config('hris.bpjs.jht_minus_code'),
            (string) config('hris.bpjs.jht_company_code'),
            (string) config('hris.bpjs.jp_plus_code'),
            (string) config('hris.bpjs.jp_minus_code'),
            (string) config('hris.bpjs.jp_company_code'),
        ));

        $this->app->singleton(PayrollSalaryProcessor::class, fn () => new PayrollSalaryProcessor(
            app(StoreAsCompanyCost::class),
            [
                app(SalaryOvertimeProcessor::class),
                app(SalaryBpjsProcessor::class),
            ],
        ));

        $this->app->singleton(SalaryAttendanceProcessor::class, fn () => new SalaryAttendanceProcessor(
            app(AttendanceSummaryCalculator::class),
        ));

        $this->app->singleton(ChainPayrollProcessor::class, fn () => new ChainPayrollProcessor([
            app(SalaryAttendanceProcessor::class),
            app(PayrollSalaryProcessor::class),
        ]));

        $this->app->bind(PayrollProcessorInterface::class, ChainPayrollProcessor::class);

        $this->app->singleton(SalaryPayrollProcessor::class, fn () => new SalaryPayrollProcessor(
            app(PayrollProcessorInterface::class),
        ));

        $this->app->singleton(SalaryTaxProcessor::class, fn () => new SalaryTaxProcessor([
            new FirstRateTaxCalculator(),
            new SecondRateTaxCalculator(),
            new ThirdRateTaxCalculator(),
            new FourthRateTaxCalculator(),
        ]));

        $this->app->bind(TaxProcessorInterface::class, SalaryTaxProcessor::class);

        $this->app->singleton(TaxProcessorService::class, fn () => new TaxProcessorService(
            app(TaxProcessorInterface::class),
            app(StoreAsCompanyCost::class),
            (string) config('hris.tax.plus_code'),
            (string) config('hris.tax.minus_code'),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Employee::observe(EmployeeObserver::class);
        Placement::observe(PlacementObserver::class);
        Mutation::observe(MutationObserver::class);
        EmployeeAddress::observe(EmployeeAddressObserver::class);
        Attendance::observe(AttendanceObserver::class);
        Overtime::observe(OvertimeObserver::class);
        Workshift::observe(WorkshiftObserver::class);
        Leave::observe(LeaveObserver::class);
        SalaryBenefitHistory::observe(SalaryBenefitHistoryObserver::class);
        TaxGroupHistory::observe(TaxGroupHistoryObserver::class);

        Gate::policy(Employee::class, EmployeePolicy::class);

        Gate::before(function (Authenticatable $user, string $ability) {
            if (! $user instanceof Employee) {
                return null;
            }

            $security = app(Security::class);

            $minRank = $security->abilityRank($ability);

            // Ability yang dikelola hierarki role; selain itu biarkan policy default bekerja.
            if ($minRank === null) {
                return null;
            }

            return $security->userRank($user) >= $minRank;
        });
    }
}