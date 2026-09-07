<?php

namespace App\Providers;

use App\Models\Employee\Employee;
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
