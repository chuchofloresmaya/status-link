<?php

namespace App\Http\Controllers\App;

use App\Domain\Plans\Services\FeatureGateService;
use App\Domain\Users\Services\UserTenantService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NotaryUserController extends Controller
{
    public function __construct(private UserTenantService $tenants, private FeatureGateService $features) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class);

        return view('app.users.index', ['users' => User::with('roles')->where('notary_id', $request->user()->notary_id)->latest()->paginate(15)]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', User::class);
        $this->assertCapacity($request);

        return view('app.users.form', ['managedUser' => new User]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', User::class);
        $this->assertCapacity($request);
        $data = $this->validated($request);
        $data['notary_id'] = $request->user()->notary_id;
        $user = User::create($data);
        $user->syncRoles('notary_user');

        return to_route('app.users.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(Request $request, User $user): View
    {
        $this->authorizeTarget($request, $user);

        return view('app.users.form', ['managedUser' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeTarget($request, $user);
        $data = $this->validated($request, $user);
        if (empty($data['password'])) {
            unset($data['password']);
        } $user->update($data);

        return to_route('app.users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        $this->authorizeTarget($request, $user);
        abort_if($request->user()->is($user), 422, 'No puedes desactivar tu propia cuenta.');
        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', 'Estado del usuario actualizado.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)], 'password' => [$user ? 'nullable' : 'required', 'nullable', 'string', 'min:8'], 'is_active' => ['nullable', 'boolean']]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function authorizeTarget(Request $request, User $user): void
    {
        Gate::authorize('update', $user);
        abort_unless($this->tenants->canManageUser($request->user(), $user), 403);
    }

    private function assertCapacity(Request $request): void
    {
        $limit = $this->features->limit($request->user()->notary, 'users');
        if ($limit !== null && $request->user()->notary->users()->count() >= (int) $limit) {
            throw ValidationException::withMessages(['users' => 'La notaría alcanzó el límite de usuarios de su plan.']);
        }
    }
}
