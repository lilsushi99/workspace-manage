<?php

namespace App\Http\Controllers;

use App\Services\Payments\PaymentVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    protected PaymentVerificationService $verifier;

    public function __construct(PaymentVerificationService $verifier)
    {
        $this->verifier = $verifier;
    }

    public function handleFlutterwave(Request $request)
    {
        $secretHash = config('services.flutterwave.secret_hash', env('FLUTTERWAVE_SECRET_HASH', 'dummy_flw_secret_hash'));
        $signature = $request->header('verif-hash');

        if ($signature !== $secretHash) {
            Log::warning("Flutterwave webhook invalid signature received.");
            return response()->json(['message' => 'Invalid webhook signature.'], 401);
        }

        $payload = $request->all();
        $event = $payload['event'] ?? '';

        if ($event === 'charge.completed' && isset($payload['data']['id'])) {
            $transactionId = (string) $payload['data']['id'];

            try {
                $confirmed = $this->verifier->verifyAndConfirmPayment($transactionId);

                if ($confirmed) {
                    return response()->json(['status' => 'success', 'message' => 'Payment verified and processed.']);
                }

                return response()->json(['status' => 'error', 'message' => 'Verification failed or payment mismatched.'], 400);
            } catch (\Exception $e) {
                Log::error("Webhook error processing Flutterwave event", ['error' => $e->getMessage()]);
                return response()->json(['status' => 'error', 'message' => 'Webhook processing error.'], 500);
            }
        }

        return response()->json(['status' => 'ignored', 'message' => 'Event type ignored.']);
    }
}
