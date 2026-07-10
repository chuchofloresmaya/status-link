<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Payments\Services\PaymentService;
use App\Http\Controllers\Controller;
use App\Models\Notary;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $service) {}

    public function index(): View
    {
        Gate::authorize('viewAny', Payment::class);

        return view('admin.payments.index', ['payments' => Payment::with(['notary', 'subscription.plan', 'creator'])->latest()->paginate(15)]);
    }

    public function create(): View
    {
        Gate::authorize('create', Payment::class);

        return view('admin.payments.create', ['notaries' => Notary::orderBy('name')->get(), 'subscriptions' => Subscription::with(['notary', 'plan'])->latest()->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Payment::class);
        $data = $request->validate(['notary_id' => ['required', 'exists:notaries,id'], 'subscription_id' => ['nullable', 'exists:subscriptions,id'], 'amount' => ['required', 'numeric', 'min:0.01'], 'currency' => ['required', 'string', 'size:3'], 'payment_method' => ['nullable', 'string', 'max:255'], 'reference' => ['nullable', 'string', 'max:255'], 'paid_at' => ['nullable', 'date'], 'status' => ['required', 'in:pending,paid,failed,refunded,cancelled'], 'notes' => ['nullable', 'string']]);
        $data['created_by'] = $request->user()->id;
        $this->service->registerManualPayment(Notary::findOrFail($data['notary_id']), (float) $data['amount'], $data);

        return to_route('admin.payments.index')->with('success', 'Pago manual registrado correctamente.');
    }
}
