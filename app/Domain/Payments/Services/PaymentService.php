<?php

namespace App\Domain\Payments\Services;

use App\Models\Notary;
use App\Models\Payment;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function registerManualPayment(Notary $notary, float $amount, array $data = []): Payment
    {
        if (isset($data['subscription_id']) && ! $notary->subscriptions()->whereKey($data['subscription_id'])->exists()) {
            throw ValidationException::withMessages(['subscription_id' => 'The subscription does not belong to this notary.']);
        }

        return $notary->payments()->create(array_merge(
            Arr::only($data, ['subscription_id', 'currency', 'payment_method', 'reference', 'paid_at', 'status', 'notes', 'created_by']),
            ['amount' => $amount, 'currency' => $data['currency'] ?? 'MXN', 'status' => $data['status'] ?? 'paid', 'payment_method' => $data['payment_method'] ?? 'manual']
        ));
    }
}
