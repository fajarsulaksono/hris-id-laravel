<?php

namespace App\Providers;

use App\Models\Employee\Employee;
use App\Models\Employee\EmployeeAddress;
use App\Models\Employee\Mutation;
use App\Models\Employee\Placement;
use App\Observers\EmployeeAddressObserver;
use App\Observers\EmployeeObserver;
use App\Observers\MutationObserver;
use App\Observers\PlacementObserver;
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
