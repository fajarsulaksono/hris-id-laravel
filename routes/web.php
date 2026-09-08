<?php

use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\Admin\OvertimeController;
use App\Http\Controllers\Api\DependencyOptionsController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Support\MasterModules;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.store');

    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('admin')->name('admin.')->middleware('employeeContext')->group(function () {
        Route::middleware('checkRole:EMPLOYEE')
            ->get('/', [DashboardController::class, 'index'])
            ->name('home');

        Route::get('api/options/{type}', [DependencyOptionsController::class, 'options'])
            ->name('api.options');

        foreach (MasterModules::all() as $key => $module) {
            $menu = $module['menu'];
            $role = MasterModules::menuRoles()[$menu];

            Route::prefix("{$menu}/{$key}")
                ->name("{$menu}.{$key}.")
                ->middleware(['checkRole:'.$role, 'companyContext'])
                ->group(function () use ($menu, $key) {
                    $d = fn ($route) => $route->defaults('menuKey', $menu)->defaults('moduleKey', $key);

                    $d(Route::get('/', [MasterDataController::class, 'index']))->name('index');
                    $d(Route::get('/data', [MasterDataController::class, 'data']))->name('data');
                    $d(Route::post('/', [MasterDataController::class, 'store']))->name('store');
                    $d(Route::get('/create', [MasterDataController::class, 'create']))->name('create');
                    $d(Route::get('/trash', [MasterDataController::class, 'trash']))->name('trash');

                    if ($key === 'attendances') {
                        $d(Route::get('/upload', [AttendanceController::class, 'uploadForm']))->name('upload');
                        $d(Route::post('/upload', [AttendanceController::class, 'storeUpload']))->name('upload.store');
                        $d(Route::get('/process', [AttendanceController::class, 'processForm']))->name('process');
                        $d(Route::post('/process', [AttendanceController::class, 'process']))->name('process.store');
                        $d(Route::get('/recap', [AttendanceController::class, 'recap']))->name('recap');
                    }

                    if ($key === 'overtimes') {
                        $d(Route::get('/upload', [OvertimeController::class, 'uploadForm']))->name('upload');
                        $d(Route::post('/upload', [OvertimeController::class, 'storeUpload']))->name('upload.store');
                        $d(Route::get('/process', [OvertimeController::class, 'processForm']))->name('process');
                        $d(Route::post('/process', [OvertimeController::class, 'process']))->name('process.store');
                    }

                    $d(Route::get('/{id}', [MasterDataController::class, 'show']))->name('show');
                    $d(Route::get('/{id}/edit', [MasterDataController::class, 'edit']))->name('edit');

                    if ($key === 'employees') {
                        $d(Route::get('/{id}/profile', [EmployeeController::class, 'showProfile']))->name('profile');
                        $d(Route::get('/{id}/promotion', [EmployeeController::class, 'promotionForm']))->name('promotion');
                        $d(Route::post('/{id}/promotion', [EmployeeController::class, 'storePromotion']))->name('promotion.store');
                    }
                    $d(Route::put('/{id}', [MasterDataController::class, 'update']))->name('update');
                    $d(Route::delete('/{id}', [MasterDataController::class, 'destroy']))->name('destroy');
                    $d(Route::post('/{id}/restore', [MasterDataController::class, 'restore']))->name('restore');
                    $d(Route::delete('/{id}/permanent', [MasterDataController::class, 'forceDestroy']))->name('force-destroy');
                });
        }
    });
});
