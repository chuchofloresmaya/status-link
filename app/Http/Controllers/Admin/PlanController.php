<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Plan::class);

        return view('admin.plans.index', ['plans' => Plan::orderBy('display_order')->paginate(15)]);
    }

    public function create(): View
    {
        Gate::authorize('create', Plan::class);

        return view('admin.plans.form', ['plan' => new Plan]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Plan::class);
        Plan::create($this->validated($request));

        return to_route('admin.plans.index')->with('success', 'Plan creado correctamente.');
    }

    public function edit(Plan $plan): View
    {
        Gate::authorize('update', $plan);

        return view('admin.plans.form', compact('plan'));
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        Gate::authorize('update', $plan);
        $plan->update($this->validated($request, $plan));

        return to_route('admin.plans.index')->with('success', 'Plan actualizado correctamente.');
    }

    public function toggleActive(Plan $plan): RedirectResponse
    {
        Gate::authorize('update', $plan);
        $plan->update(['is_active' => ! $plan->is_active]);

        return back()->with('success', 'Estado del plan actualizado.');
    }

    private function validated(Request $request, ?Plan $plan = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('plans')->ignore($plan)],
            'category_label' => ['nullable', 'string', 'max:255'],
            'badge_label' => ['nullable', 'string', 'max:255'],
            'secondary_label' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'monthly_price' => ['nullable', 'numeric', 'min:0'],
            'promotional_price' => ['nullable', 'numeric', 'min:0'],
            'promotional_months' => ['nullable', 'integer', 'min:1'],
            'promotional_equivalent_monthly_price' => ['nullable', 'numeric', 'min:0'],
            'billing_period' => ['nullable', 'string', 'max:50'],
            'requires_quote' => ['nullable', 'boolean'],
            'display_order' => ['required', 'integer', 'min:0'],
            'marketing_features' => ['required', 'json'],
            'features' => ['required', 'json'],
            'limits' => ['required', 'json'],
            'is_highlighted' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['marketing_features'] = json_decode($data['marketing_features'], true);
        $data['features'] = json_decode($data['features'], true);
        $data['limits'] = json_decode($data['limits'], true);
        if (! is_array($data['marketing_features']) || ! is_array($data['features']) || ! is_array($data['limits'])) {
            throw ValidationException::withMessages(['features' => 'Los valores JSON deben ser arreglos u objetos válidos.']);
        }
        $data['price'] = $data['monthly_price'] ?? 0;
        $data['billing_period'] = $data['billing_period'] ?? 'monthly';
        $data['requires_quote'] = $request->boolean('requires_quote');
        $data['is_highlighted'] = $request->boolean('is_highlighted');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
