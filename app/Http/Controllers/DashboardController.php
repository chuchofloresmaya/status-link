<?php

namespace App\Http\Controllers;

use App\Models\Notary;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        if ($user->hasRole('super_admin')) {
            return view('dashboard.super-admin', [
                'notariesCount' => Notary::count(),
                'activeNotariesCount' => Notary::where('is_active', true)->count(),
                'activeUsersCount' => User::where('is_active', true)->count(),
                'activePlansCount' => Plan::where('is_active', true)->count(),
                'activeSubscriptionsCount' => Subscription::where('status', 'active')->count(),
                'paidPaymentsCount' => Payment::where('status', 'paid')->count(),
            ]);
        }

        if ($user->hasRole('notary_admin')) {
            $notary = $user->notary()->with(['activeSubscription.plan', 'defaultNotarialProfile', 'defaultBankAccount'])->withCount([
                'users as active_users_count' => fn ($query) => $query->where('is_active', true),
                'notarialProfiles as active_notarial_profiles_count' => fn ($query) => $query->where('is_active', true),
                'bankAccounts as active_bank_accounts_count' => fn ($query) => $query->where('is_active', true),
            ])->firstOrFail();

            return view('dashboard.notary-admin', compact('notary'));
        }

        $user->load('notary.activeSubscription.plan');

        return view('dashboard.notary-user', compact('user'));
    }
}
