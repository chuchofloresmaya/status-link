<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NotaryController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Notary::class);

        return view('admin.notaries.index', ['notaries' => Notary::with('activeSubscription.plan')->withCount('users')->latest()->paginate(15)]);
    }

    public function create(): View
    {
        Gate::authorize('create', Notary::class);

        return view('admin.notaries.form', ['notary' => new Notary]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Notary::class);
        Notary::create($this->validated($request));

        return to_route('admin.notaries.index')->with('success', 'Notaría creada correctamente.');
    }

    public function edit(Notary $notary): View
    {
        Gate::authorize('update', $notary);

        return view('admin.notaries.form', compact('notary'));
    }

    public function update(Request $request, Notary $notary): RedirectResponse
    {
        Gate::authorize('update', $notary);
        $notary->update($this->validated($request, $notary));

        return to_route('admin.notaries.index')->with('success', 'Notaría actualizada correctamente.');
    }

    public function toggleActive(Notary $notary): RedirectResponse
    {
        Gate::authorize('update', $notary);
        $notary->update(['is_active' => ! $notary->is_active]);

        return back()->with('success', 'Estado de la notaría actualizado.');
    }

    private function validated(Request $request, ?Notary $notary = null): array
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'slug' => ['required', 'string', 'max:255', Rule::unique('notaries')->ignore($notary)], 'rfc' => ['nullable', 'string', 'max:255'], 'email' => ['nullable', 'email', 'max:255'], 'phone' => ['nullable', 'string', 'max:255'], 'address' => ['nullable', 'string'], 'is_active' => ['nullable', 'boolean'], 'settings' => ['nullable', 'array'], 'settings.users_can_view_all_records' => ['nullable', 'boolean']]);
        $data['is_active'] = $request->boolean('is_active');
        $data['settings'] = ['users_can_view_all_records' => $request->boolean('settings.users_can_view_all_records')];

        return $data;
    }
}
