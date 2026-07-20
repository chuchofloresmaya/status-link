<?php

namespace App\Http\Controllers\App;

use App\Domain\NotarialProfiles\Services\NotarialProfileService;
use App\Http\Controllers\Controller;
use App\Models\NotarialProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class NotarialProfileController extends Controller
{
    public function __construct(private NotarialProfileService $service) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', NotarialProfile::class);

        return view('app.notarial-profiles.index', ['profiles' => $request->user()->notary->notarialProfiles()->latest()->paginate(15)]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', NotarialProfile::class);

        return view('app.notarial-profiles.form', ['profile' => new NotarialProfile]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', NotarialProfile::class);
        $this->service->storeForNotary($request->user()->notary, $this->validated($request));

        return to_route('app.notarial-profiles.index')->with('success', 'Perfil notarial creado correctamente.');
    }

    public function edit(Request $request, NotarialProfile $profile): View
    {
        Gate::authorize('update', $profile);

        return view('app.notarial-profiles.form', compact('profile'));
    }

    public function update(Request $request, NotarialProfile $profile): RedirectResponse
    {
        Gate::authorize('update', $profile);
        $this->service->updateProfile($profile, $this->validated($request));

        return to_route('app.notarial-profiles.index')->with('success', 'Perfil notarial actualizado.');
    }

    public function setDefault(NotarialProfile $profile): RedirectResponse
    {
        Gate::authorize('update', $profile);
        $this->service->setDefault($profile);

        return back()->with('success', 'Perfil predeterminado actualizado.');
    }

    public function toggleActive(NotarialProfile $profile): RedirectResponse
    {
        Gate::authorize('update', $profile);
        $this->service->toggleActive($profile);

        return back()->with('success', 'Estado del perfil actualizado.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'notary_number' => ['nullable', 'string', 'max:255'], 'notary_name' => ['nullable', 'string', 'max:255'], 'notary_title' => ['nullable', 'string', 'max:255'], 'rfc' => ['nullable', 'string', 'max:255'], 'email' => ['nullable', 'email', 'max:255'], 'phone' => ['nullable', 'string', 'max:255'], 'address' => ['nullable', 'string'], 'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'], 'settings' => ['nullable', 'json'], 'is_default' => ['nullable', 'boolean'], 'is_active' => ['nullable', 'boolean']]);
        $data['settings'] = isset($data['settings']) ? json_decode($data['settings'], true) : null;
        $data['is_default'] = $request->boolean('is_default');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
