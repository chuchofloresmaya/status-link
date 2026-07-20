<?php

use App\Http\Controllers\Admin\BankAccountController as AdminBankAccountController;
use App\Http\Controllers\Admin\NotarialProfileController as AdminNotarialProfileController;
use App\Http\Controllers\Admin\NotaryController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\App\BankAccountController as AppBankAccountController;
use App\Http\Controllers\App\NotarialProfileController as AppNotarialProfileController;
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
        Route::get('notaries/{notary}/profiles', [AdminNotarialProfileController::class, 'index'])->name('notaries.profiles.index');
        Route::get('notaries/{notary}/profiles/create', [AdminNotarialProfileController::class, 'create'])->name('notaries.profiles.create');
        Route::post('notaries/{notary}/profiles', [AdminNotarialProfileController::class, 'store'])->name('notaries.profiles.store');
        Route::get('notarial-profiles/{profile}/edit', [AdminNotarialProfileController::class, 'edit'])->name('notarial-profiles.edit');
        Route::put('notarial-profiles/{profile}', [AdminNotarialProfileController::class, 'update'])->name('notarial-profiles.update');
        Route::patch('notarial-profiles/{profile}/set-default', [AdminNotarialProfileController::class, 'setDefault'])->name('notarial-profiles.set-default');
        Route::patch('notarial-profiles/{profile}/toggle-active', [AdminNotarialProfileController::class, 'toggleActive'])->name('notarial-profiles.toggle-active');
        Route::get('notaries/{notary}/bank-accounts', [AdminBankAccountController::class, 'index'])->name('notaries.bank-accounts.index');
        Route::get('notaries/{notary}/bank-accounts/create', [AdminBankAccountController::class, 'create'])->name('notaries.bank-accounts.create');
        Route::post('notaries/{notary}/bank-accounts', [AdminBankAccountController::class, 'store'])->name('notaries.bank-accounts.store');
        Route::get('bank-accounts/{bankAccount}/edit', [AdminBankAccountController::class, 'edit'])->name('bank-accounts.edit');
        Route::put('bank-accounts/{bankAccount}', [AdminBankAccountController::class, 'update'])->name('bank-accounts.update');
        Route::patch('bank-accounts/{bankAccount}/set-default', [AdminBankAccountController::class, 'setDefault'])->name('bank-accounts.set-default');
        Route::patch('bank-accounts/{bankAccount}/toggle-active', [AdminBankAccountController::class, 'toggleActive'])->name('bank-accounts.toggle-active');
    });

    Route::prefix('app')->name('app.')->middleware('role:notary_admin')->group(function () {
        Route::resource('users', NotaryUserController::class)->except(['show', 'destroy']);
        Route::patch('users/{user}/toggle-active', [NotaryUserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::get('settings', [NotarySettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [NotarySettingsController::class, 'update'])->name('settings.update');
        Route::resource('notarial-profiles', AppNotarialProfileController::class)->parameters(['notarial-profiles' => 'profile'])->except(['show', 'destroy']);
        Route::patch('notarial-profiles/{profile}/set-default', [AppNotarialProfileController::class, 'setDefault'])->name('notarial-profiles.set-default');
        Route::patch('notarial-profiles/{profile}/toggle-active', [AppNotarialProfileController::class, 'toggleActive'])->name('notarial-profiles.toggle-active');
        Route::resource('bank-accounts', AppBankAccountController::class)->parameters(['bank-accounts' => 'bankAccount'])->except(['show', 'destroy']);
        Route::patch('bank-accounts/{bankAccount}/set-default', [AppBankAccountController::class, 'setDefault'])->name('bank-accounts.set-default');
        Route::patch('bank-accounts/{bankAccount}/toggle-active', [AppBankAccountController::class, 'toggleActive'])->name('bank-accounts.toggle-active');
    });
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');
