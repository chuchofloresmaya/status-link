<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Subscriptions\Services\SubscriptionService;
use App\Http\Controllers\Controller;
use App\Models\Notary;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(private SubscriptionService $service) {}

    public function index(): View
    {
        Gate::authorize('viewAny', Subscription::class);

        return view('admin.subscriptions.index', ['subscriptions' => Subscription::with(['notary', 'plan'])->latest()->paginate(15)]);
    }

    public function create(): View
    {
        Gate::authorize('create', Subscription::class);

        return view('admin.subscriptions.create', ['notaries' => Notary::orderBy('name')->get(), 'plans' => Plan::where('is_active', true)->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Subscription::class);
        $data = $request->validate(['notary_id' => ['required', 'exists:notaries,id'], 'plan_id' => ['required', 'exists:plans,id'], 'status' => ['required', 'in:active'], 'starts_at' => ['required', 'date'], 'ends_at' => ['nullable', 'date', 'after:starts_at'], 'trial_ends_at' => ['nullable', 'date', 'after_or_equal:starts_at']]);
        $this->service->activateSubscription(Notary::findOrFail($data['notary_id']), Plan::findOrFail($data['plan_id']), $data);

        return to_route('admin.subscriptions.index')->with('success', 'Suscripción activada correctamente.');
    }

    public function show(Subscription $subscription): View
    {
        Gate::authorize('view', $subscription);

        return view('admin.subscriptions.show', ['subscription' => $subscription->load(['notary', 'plan', 'payments'])]);
    }

    public function cancel(Subscription $subscription): RedirectResponse
    {
        Gate::authorize('update', $subscription);
        $this->service->cancelSubscription($subscription);

        return back()->with('success', 'Suscripción cancelada correctamente.');
    }
}
