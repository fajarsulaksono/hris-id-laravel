<?php

namespace App\Providers;

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
        });
    }
}
