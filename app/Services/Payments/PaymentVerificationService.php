<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Services\Payments\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentVerificationService
{
    protected PaymentGatewayInterface $gateway;

    public function __construct(PaymentGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    public function verifyAndConfirmPayment(string $transactionId, ?Payment $payment = null): bool
    {
        $verifiedData = $this->gateway->verifyTransaction($transactionId);

        if (($verifiedData['status'] ?? '') !== 'successful') {
            Log::warning("Transaction verification status is not successful", ['id' => $transactionId, 'data' => $verifiedData]);
            return false;
        }

        if (!$payment) {
            $reference = $verifiedData['reference'] ?? '';
            $payment = Payment::where('reference', $reference)
                ->orWhere('provider_reference', $transactionId)
                ->first();
        }

        if (!$payment) {
            Log::error("Payment record not found during verification", ['tx' => $transactionId]);
            return false;
        }

        return DB::transaction(function () use ($payment, $verifiedData, $transactionId) {
            $payment = Payment::where('id', $payment->id)->lockForUpdate()->first();
            $order = Order::where('id', $payment->order_id)->lockForUpdate()->first();

            // Idempotency check: if already successful, skip
            if ($payment->status === 'successful' && $order->payment_status === 'paid') {
                return true;
            }

            // Verify amount & currency
            if (abs($payment->amount - $verifiedData['amount']) > 0.001) {
                Log::error("Payment amount mismatch", [
                    'expected' => $payment->amount,
                    'actual' => $verifiedData['amount']
                ]);
                $payment->update(['status' => 'failed', 'metadata' => array_merge($payment->metadata ?: [], ['error' => 'Amount mismatch'])]);
                return false;
            }

            if (strtoupper($payment->currency) !== strtoupper($verifiedData['currency'])) {
                Log::error("Payment currency mismatch", [
                    'expected' => $payment->currency,
                    'actual' => $verifiedData['currency']
                ]);
                $payment->update(['status' => 'failed', 'metadata' => array_merge($payment->metadata ?: [], ['error' => 'Currency mismatch'])]);
                return false;
            }

            // Atomic Confirmation
            $payment->update([
                'status' => 'successful',
                'provider_reference' => (string) ($verifiedData['provider_reference'] ?? $transactionId),
                'payment_method' => $verifiedData['payment_method'] ?? 'card',
                'paid_at' => now(),
                'metadata' => array_merge($payment->metadata ?: [], ['verification' => 'verified']),
            ]);

            if ($order) {
                $oldStatus = $order->status;
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'paid',
                ]);

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'old_status' => $oldStatus,
                    'new_status' => 'paid',
                    'source' => 'flutterwave_verification',
                    'message' => 'Payment verified and order marked paid.',
                ]);
            }

            return true;
        });
    }
}
