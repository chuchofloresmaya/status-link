<?php

use App\Http\Controllers\Admin\NotaryController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\App\NotarySettingsController;
use App\Http\Controllers\App\NotaryUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(Auth::check() ? 'dashboard' : 'login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware(['auth', 'active.user', 'active.notary'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('admin')->name('admin.')->middleware('role:super_admin')->group(function () {
        Route::resource('notaries', NotaryController::class)->except(['show', 'destroy']);
        Route::patch('notaries/{notary}/toggle-active', [NotaryController::class, 'toggleActive'])->name('notaries.toggle-active');
        Route::resource('plans', PlanController::class)->except(['show', 'destroy']);
        Route::patch('plans/{plan}/toggle-active', [PlanController::class, 'toggleActive'])->name('plans.toggle-active');
        Route::resource('subscriptions', SubscriptionController::class)->only(['index', 'create', 'store', 'show']);
        Route::patch('subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
        Route::resource('payments', PaymentController::class)->only(['index', 'create', 'store']);
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    });

    Route::prefix('app')->name('app.')->middleware('role:notary_admin')->group(function () {
        Route::resource('users', NotaryUserController::class)->except(['show', 'destroy']);
        Route::patch('users/{user}/toggle-active', [NotaryUserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::get('settings', [NotarySettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [NotarySettingsController::class, 'update'])->name('settings.update');
    });
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');
