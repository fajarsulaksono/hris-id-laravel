<?php

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\AuthController;
use App\Support\ApiModules;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);

        foreach (ApiModules::all() as $key => $module) {
            $viewAbility = $module['ability'];
            $manageAbility = $module['write_ability'] ?? str_replace('view_', 'manage_', $viewAbility);

            Route::middleware("apiRole:{$viewAbility}")->prefix($key)->group(function () use ($key, $manageAbility) {
                Route::get('/', [ApiController::class, 'index'])->defaults('module', $key)->name("api.{$key}.index");
                Route::get('{id}', [ApiController::class, 'show'])->defaults('module', $key)->name("api.{$key}.show");

                Route::middleware("apiRole:{$manageAbility}")->group(function () use ($key) {
                    Route::post('/', [ApiController::class, 'store'])->defaults('module', $key)->name("api.{$key}.store");
                    Route::put('{id}', [ApiController::class, 'update'])->defaults('module', $key)->name("api.{$key}.update");
                    Route::delete('{id}', [ApiController::class, 'destroy'])->defaults('module', $key)->name("api.{$key}.destroy");
                });
            });
        }
    });
});
