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
use App\Domain\Overtime\OvertimeCalculator;
use App\Domain\Overtime\OvertimeCalculatorInterface;
use App\Domain\Overtime\OvertimeCalculatorService;
use App\Domain\Overtime\OvertimeChecker;
use App\Domain\Overtime\OvertimeProcessor;
use App\Models\Attendance\Attendance;
use App\Models\Attendance\Leave;
use App\Models\Attendance\Overtime;
use App\Models\Attendance\Workshift;
use App\Models\Employee\Employee;
use App\Models\Employee\EmployeeAddress;
use App\Models\Employee\Mutation;
use App\Models\Employee\Placement;
use App\Observers\AttendanceObserver;
use App\Observers\EmployeeAddressObserver;
use App\Observers\EmployeeObserver;
use App\Observers\LeaveObserver;
use App\Observers\MutationObserver;
use App\Observers\OvertimeObserver;
use App\Observers\PlacementObserver;
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