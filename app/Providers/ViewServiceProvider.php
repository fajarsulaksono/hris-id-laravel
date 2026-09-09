<?php

namespace App\Providers;

use App\Models\Employee\Employee;
use App\Support\Security;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('layouts.app', function ($view): void {
            $segments = request()->segments();
            $menuPrefix = ($segments[0] ?? null) === 'admin' && isset($segments[1])
                ? $segments[1]
                : null;

            $view->with('activeMenuPrefix', $menuPrefix);
            $view->with('roleTheme', $this->roleTheme());
        });
    }

    protected function roleTheme(): ?string
    {
        $user = auth()->user();

        if (! $user instanceof Employee) {
            return null;
        }

        $role = app(Security::class)->userRole($user);

        return $role === null ? null : strtolower(str_replace('_', '-', $role));
    }
}
