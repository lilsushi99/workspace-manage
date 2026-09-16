<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\TenantService;
use App\Models\User;
use App\Services\Payments\PaymentVerificationService;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    public function show(Request $request, string $username)
    {
        // Resolve tenant store by username (User) or store slug (Store)
        $user = User::where('username', $username)->first();

        if ($user && $user->tenant) {
            $tenant = $user->tenant;
            $store = $tenant->stores()->first();
        } else {
            $store = Store::where('slug', $username)->with('tenant')->first();
            if ($store) {
                $tenant = $store->tenant;
            } else {
                abort(404, 'Storefront not available.');
            }
        }

        if (!$store || $tenant->status !== 'active') {
            abort(404, 'Storefront not available.');
        }

        // Fetch only enabled active services for this tenant
        $tenantServices = TenantService::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->with(['service' => function ($q) {
                $q->where('status', 'active');
            }])
            ->get()
            ->filter(fn ($ts) => $ts->service !== null);

        $groupedServices = $tenantServices->groupBy(fn ($ts) => $ts->service->platform);

        return view('storefront.show', compact('tenant', 'store', 'groupedServices'));
    }

    public function calculateSummary(Request $request, string $username)
    {
        $request->validate([
            'tenant_service_id' => ['required', 'exists:tenant_services,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'target' => ['required', 'string', 'max:1000'],
        ]);

        $user = User::where('username', $username)->first();
        $tenant = $user && $user->tenant ? $user->tenant : Store::where('slug', $username)->firstOrFail()->tenant;

        $tenantService = TenantService::where('id', $request->tenant_service_id)
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->with('service')
            ->firstOrFail();

        $service = $tenantService->service;
        $quantity = (int) $request->quantity;

        if ($quantity < $service->min_quantity || $quantity > $service->max_quantity) {
            return response()->json([
                'error' => "Quantity must be between {$service->min_quantity} and " . number_format($service->max_quantity) . "."
            ], 422);
        }

        // Server-side calculation: IGNORE ANY CLIENT PRICE!
        $unitPrice = $tenantService->selling_price;
        $totalPrice = round(($unitPrice / 1000) * $quantity, 4);

        return response()->json([
            'service_name' => $service->name,
            'quantity' => $quantity,
            'target' => $request->target,
            'unit_price_per_k' => $unitPrice,
            'total_price' => $totalPrice,
            'currency' => $tenant->currency ?: 'USD',
        ]);
    }

    public function paymentStatus(Request $request, string $username)
    {
        $user = User::where('username', $username)->first();
        $tenant = $user && $user->tenant ? $user->tenant : Store::where('slug', $username)->firstOrFail()->tenant;
        $store = $tenant->stores()->firstOrFail();

        $reference = $request->query('reference');
        $transactionId = $request->query('transaction_id') ?: $request->query('transaction_id');

        $order = Order::where('tenant_id', $tenant->id)
            ->where('order_number', $reference)
            ->with(['service', 'payments'])
            ->first();

        // Attempt server-side verification if transaction_id provided and order unpaid
        if ($order && $order->payment_status !== 'paid' && $transactionId) {
            /** @var PaymentVerificationService $verifier */
            $verifier = app(PaymentVerificationService::class);
            $verifier->verifyAndConfirmPayment($transactionId, $order->payments->first());
            $order->refresh();
        }

        return view('storefront.payment-status', [
            'tenant' => $tenant,
            'store' => $store,
            'order' => $order,
            'username' => $username,
        ]);
    }
}
