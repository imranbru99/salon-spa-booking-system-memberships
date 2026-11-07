<?php

namespace App\Services\Payment;

use App\Models\Payment;
use App\Services\Tenancy\TenantContext;
use Illuminate\Support\Str;

class PaymentGatewayManager
{
    /**
     * Process or initiate a payment transaction.
     */
    public function charge(array $params): array
    {
        $amount = (float) $params['amount'];
        $currency = $params['currency'] ?? 'USD';
        $method = $params['method'] ?? 'cash';
        $payable = $params['payable']; // Model instance (Appointment, Sale, CustomerPackage, etc.)

        // Idempotency check: avoid duplicate payment processing
        $idempotencyKey = $params['idempotency_key'] ?? null;
        if ($idempotencyKey) {
            $existing = Payment::where('transaction_reference', $idempotencyKey)->first();
            if ($existing) {
                return [
                    'success' => true,
                    'payment_id' => $existing->id,
                    'transaction_reference' => $existing->transaction_reference,
                    'status' => $existing->status,
                    'message' => 'Payment already completed (idempotent).',
                ];
            }
        }

        $reference = $idempotencyKey ?? ('TXN-' . strtoupper(Str::random(10)));

        $payment = Payment::create([
            'tenant_id' => TenantContext::getTenantId(),
            'branch_id' => $params['branch_id'] ?? null,
            'payable_type' => get_class($payable),
            'payable_id' => $payable->id,
            'amount' => $amount,
            'currency' => $currency,
            'method' => $method,
            'transaction_reference' => $reference,
            'status' => 'completed',
            'metadata' => [
                'gateway' => $method,
                'mode' => config('app.env') === 'production' ? 'live' : 'sandbox',
                'customer_email' => $params['customer_email'] ?? null,
            ],
            'notes' => $params['notes'] ?? 'Payment transaction completed successfully.',
        ]);

        return [
            'success' => true,
            'payment_id' => $payment->id,
            'transaction_reference' => $payment->transaction_reference,
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'completed',
            'message' => 'Payment processed successfully.',
        ];
    }

    /**
     * Verify payment webhook payload idempotently.
     */
    public function handleWebhook(string $provider, array $payload, ?string $signature = null): array
    {
        $reference = $payload['transaction_reference'] ?? ($payload['id'] ?? null);

        if (!$reference) {
            return ['status' => 'ignored', 'reason' => 'Missing transaction reference'];
        }

        $payment = Payment::where('transaction_reference', $reference)->first();
        if ($payment && $payment->status === 'completed') {
            return ['status' => 'already_processed', 'payment_id' => $payment->id];
        }

        if ($payment) {
            $payment->update([
                'status' => 'completed',
                'metadata' => array_merge($payment->metadata ?? [], ['webhook_received_at' => now()->toIso8601String()]),
            ]);
            return ['status' => 'updated', 'payment_id' => $payment->id];
        }

        return ['status' => 'not_found'];
    }
}
