<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notary;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', User::class);

        return view('admin.users.index', ['users' => User::with(['notary', 'roles'])->latest()->paginate(15)]);
    }

    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('admin.users.form', ['managedUser' => new User, 'notaries' => Notary::orderBy('name')->get(), 'roles' => ['super_admin', 'notary_admin', 'notary_user']]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', User::class);
        $data = $this->validated($request);
        $role = $data['role'];
        unset($data['role']);
        $user = User::create($data);
        $user->syncRoles($role);

        return to_route('admin.users.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user): View
    {
        Gate::authorize('update', $user);

        return view('admin.users.form', ['managedUser' => $user, 'notaries' => Notary::orderBy('name')->get(), 'roles' => ['super_admin', 'notary_admin', 'notary_user']]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);
        $data = $this->validated($request, $user);
        $role = $data['role'];
        unset($data['role']);
        if (empty($data['password'])) {
            unset($data['password']);
        } $user->update($data);
        $user->syncRoles($role);

        return to_route('admin.users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);
        abort_if($request->user()->is($user), 422, 'No puedes desactivar tu propia cuenta.');
        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', 'Estado del usuario actualizado.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)], 'password' => [$user ? 'nullable' : 'required', 'nullable', 'string', 'min:8'], 'role' => ['required', Rule::in(['super_admin', 'notary_admin', 'notary_user'])], 'notary_id' => ['nullable', 'exists:notaries,id', 'required_unless:role,super_admin'], 'is_active' => ['nullable', 'boolean']]);
        $data['notary_id'] = $data['role'] === 'super_admin' ? null : $data['notary_id'];
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
