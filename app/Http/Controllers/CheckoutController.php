<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Store;
use App\Models\TenantService;
use App\Models\User;
use App\Services\Payments\Contracts\PaymentGatewayInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    protected PaymentGatewayInterface $gateway;

    public function __construct(PaymentGatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    public function showCheckout(Request $request, string $username)
    {
        $request->validate([
            'tenant_service_id' => ['required', 'exists:tenant_services,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'target' => ['required', 'string', 'max:1000'],
        ]);

        $user = User::where('username', $username)->first();
        $tenant = $user && $user->tenant ? $user->tenant : Store::where('slug', $username)->firstOrFail()->tenant;
        $store = $tenant->stores()->firstOrFail();

        $tenantService = TenantService::where('id', $request->tenant_service_id)
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->with('service')
            ->firstOrFail();

        $quantity = (int) $request->quantity;
        $service = $tenantService->service;

        if ($quantity < $service->min_quantity || $quantity > $service->max_quantity) {
            return back()->withErrors(['quantity' => "Quantity must be between {$service->min_quantity} and " . number_format($service->max_quantity)]);
        }

        // Server-side calculation
        $unitPrice = $tenantService->selling_price;
        $totalPrice = round(($unitPrice / 1000) * $quantity, 4);

        return view('storefront.checkout', [
            'tenant' => $tenant,
            'store' => $store,
            'tenantService' => $tenantService,
            'service' => $service,
            'quantity' => $quantity,
            'target' => $request->target,
            'totalPrice' => $totalPrice,
            'username' => $username,
        ]);
    }

    public function processCheckout(Request $request, string $username)
    {
        $request->validate([
            'tenant_service_id' => ['required', 'exists:tenant_services,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'target' => ['required', 'string', 'max:1000'],
            'email' => ['required', 'email', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $user = User::where('username', $username)->first();
        $tenant = $user && $user->tenant ? $user->tenant : Store::where('slug', $username)->firstOrFail()->tenant;
        $store = $tenant->stores()->firstOrFail();

        $tenantService = TenantService::where('id', $request->tenant_service_id)
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->with('service')
            ->firstOrFail();

        $service = $tenantService->service;
        $quantity = (int) $request->quantity;

        if ($quantity < $service->min_quantity || $quantity > $service->max_quantity) {
            return back()->withErrors(['quantity' => "Quantity out of bounds."]);
        }

        // SERVER-SIDE PRICE RECALCULATION
        $customerUnitPrice = $tenantService->selling_price;
        $customerTotalPrice = round(($customerUnitPrice / 1000) * $quantity, 4);
        $providerUnitCost = $service->base_price;
        $providerTotalCost = round(($providerUnitCost / 1000) * $quantity, 4);
        $profitAmount = round($customerTotalPrice - $providerTotalCost, 4);

        $paymentUrl = '';

        DB::transaction(function () use ($tenant, $store, $service, $request, $quantity, $customerUnitPrice, $customerTotalPrice, $providerUnitCost, $providerTotalCost, $profitAmount, $username, &$paymentUrl) {
            // Tenant-scoped customer lookup or creation
            $customer = Customer::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'email' => $request->email,
                ],
                [
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'phone' => $request->phone,
                    'password' => bcrypt(Str::random(16)),
                    'status' => 'active',
                ]
            );

            // Generate order reference
            $orderNumber = 'DOWA-' . strtoupper(Str::random(6));

            $order = Order::create([
                'tenant_id' => $tenant->id,
                'customer_id' => $customer->id,
                'store_id' => $store->id,
                'service_id' => $service->id,
                'provider_id' => $service->provider_id,
                'order_number' => $orderNumber,
                'target' => $request->target,
                'quantity' => $quantity,
                'provider_unit_cost' => $providerUnitCost,
                'provider_total_cost' => $providerTotalCost,
                'customer_unit_price' => $customerUnitPrice,
                'customer_total_price' => $customerTotalPrice,
                'profit_amount' => $profitAmount,
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ]);

            // Create Payment record
            $paymentReference = 'PAY-' . strtoupper(Str::random(10));
            $payment = Payment::create([
                'tenant_id' => $tenant->id,
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'reference' => $paymentReference,
                'gateway' => 'flutterwave',
                'amount' => $customerTotalPrice,
                'currency' => $tenant->currency ?: 'USD',
                'status' => 'pending',
            ]);

            // Initialize Gateway Payment
            $callbackUrl = route('storefront.payment-status', ['username' => $username, 'reference' => $orderNumber]);

            $initResponse = $this->gateway->initializePayment([
                'reference' => $paymentReference,
                'amount' => $customerTotalPrice,
                'currency' => $tenant->currency ?: 'USD',
                'email' => $customer->email,
                'name' => "{$customer->first_name} {$customer->last_name}",
                'phone' => $customer->phone,
                'redirect_url' => $callbackUrl,
                'title' => "{$store->name} Order #{$orderNumber}",
                'description' => "{$quantity}x {$service->name}",
                'order_id' => $order->id,
                'tenant_id' => $tenant->id,
            ]);

            $paymentUrl = $initResponse['payment_url'];
        });

        return redirect()->away($paymentUrl);
    }
}
