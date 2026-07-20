<?php

namespace App\Http\Controllers\App;

use App\Domain\BankAccounts\Services\BankAccountService;
use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BankAccountController extends Controller
{
    public function __construct(private BankAccountService $service) {}

    public function index(Request $request): View
    {
        Gate::authorize('viewAny', BankAccount::class);

        return view('app.bank-accounts.index', ['accounts' => $request->user()->notary->bankAccounts()->with('notarialProfile')->latest()->paginate(15)]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', BankAccount::class);

        return view('app.bank-accounts.form', ['bankAccount' => new BankAccount, 'profiles' => $request->user()->notary->notarialProfiles()->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', BankAccount::class);
        $notary = $request->user()->notary;
        $this->service->storeForNotary($notary, $this->validated($request, $notary->id));

        return to_route('app.bank-accounts.index')->with('success', 'Cuenta bancaria creada correctamente.');
    }

    public function edit(Request $request, BankAccount $bankAccount): View
    {
        Gate::authorize('update', $bankAccount);

        return view('app.bank-accounts.form', ['bankAccount' => $bankAccount, 'profiles' => $request->user()->notary->notarialProfiles()->orderBy('name')->get()]);
    }

    public function update(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        Gate::authorize('update', $bankAccount);
        $this->service->updateBankAccount($bankAccount, $this->validated($request, $request->user()->notary_id));

        return to_route('app.bank-accounts.index')->with('success', 'Cuenta bancaria actualizada.');
    }

    public function setDefault(BankAccount $bankAccount): RedirectResponse
    {
        Gate::authorize('update', $bankAccount);
        $this->service->setDefault($bankAccount);

        return back()->with('success', 'Cuenta predeterminada actualizada.');
    }

    public function toggleActive(BankAccount $bankAccount): RedirectResponse
    {
        Gate::authorize('update', $bankAccount);
        $this->service->toggleActive($bankAccount);

        return back()->with('success', 'Estado de la cuenta actualizado.');
    }

    private function validated(Request $request, int $notaryId): array
    {
        $data = $request->validate(['notarial_profile_id' => ['nullable', Rule::exists('notarial_profiles', 'id')->where('notary_id', $notaryId)], 'account_type' => ['required', Rule::in(['general', 'honorarios', 'impuestos', 'other'])], 'custom_account_type' => ['nullable', 'required_if:account_type,other', 'string', 'max:255'], 'bank_name' => ['required', 'string', 'max:255'], 'account_holder' => ['required', 'string', 'max:255'], 'account_number' => ['nullable', 'string', 'max:255'], 'clabe' => ['nullable', 'string', 'max:255'], 'card_number' => ['nullable', 'string', 'max:255'], 'currency' => ['required', 'string', 'max:10'], 'notes' => ['nullable', 'string'], 'is_default' => ['nullable', 'boolean'], 'is_active' => ['nullable', 'boolean']]);
        $data['account_type'] = $data['account_type'] === 'other' ? Str::of($data['custom_account_type'])->lower()->snake()->toString() : $data['account_type'];
        unset($data['custom_account_type']);
        $data['is_default'] = $request->boolean('is_default');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
