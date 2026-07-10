<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class NotarySettingsController extends Controller
{
    public function edit(Request $request): View
    {
        $notary = $request->user()->notary;
        Gate::authorize('update', $notary);

        return view('app.settings.edit', compact('notary'));
    }

    public function update(Request $request): RedirectResponse
    {
        $notary = $request->user()->notary;
        Gate::authorize('update', $notary);
        $request->validate(['users_can_view_all_records' => ['nullable', 'boolean']]);
        $notary->update(['settings' => array_merge($notary->settings ?? [], ['users_can_view_all_records' => $request->boolean('users_can_view_all_records')])]);

        return back()->with('success', 'Configuración actualizada correctamente.');
    }
}
