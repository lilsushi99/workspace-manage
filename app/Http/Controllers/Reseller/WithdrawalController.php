<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use App\Services\Finance\WalletLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WithdrawalController extends Controller
{
    protected WalletLedgerService $ledger;

    public function __construct(WalletLedgerService $ledger)
    {
        $this->ledger = $ledger;
    }

    public function wallet(Request $request)
    {
        $tenant = $request->user()->tenant;
        if (!$tenant) {
            return redirect()->route('onboarding.index');
        }

        $wallet = Wallet::firstOrCreate(
            ['tenant_id' => $tenant->id],
            ['currency' => 'USD', 'balance' => 0, 'available_balance' => 0, 'pending_balance' => 0]
        );

        $transactions = WalletTransaction::where('tenant_id', $tenant->id)
            ->orderBy('id', 'desc')
            ->paginate(15);

        $withdrawals = Withdrawal::where('tenant_id', $tenant->id)
            ->orderBy('id', 'desc')
            ->get();

        return view('reseller.wallet.index', compact('wallet', 'transactions', 'withdrawals'));
    }

    public function create(Request $request)
    {
        $tenant = $request->user()->tenant;
        if (!$tenant) {
            return redirect()->route('onboarding.index');
        }

        $wallet = Wallet::firstOrCreate(['tenant_id' => $tenant->id]);
        $bankAccounts = BankAccount::where('tenant_id', $tenant->id)->get();
        $defaultBank = $bankAccounts->firstWhere('is_default', true) ?: $bankAccounts->first();

        return view('reseller.wallet.withdraw', compact('wallet', 'bankAccounts', 'defaultBank'));
    }

    public function store(Request $request)
    {
        $tenant = $request->user()->tenant;
        if (!$tenant) {
            return redirect()->route('onboarding.index');
        }

        $request->validate([
            'amount' => ['required', 'numeric', 'min:5'],
            'bank_account_id' => ['required', 'exists:bank_accounts,id'],
        ]);

        $amount = (float) $request->amount;

        try {
            DB::transaction(function () use ($tenant, $request, $amount) {
                $wallet = Wallet::where('tenant_id', $tenant->id)->lockForUpdate()->firstOrFail();

                if ($amount > $wallet->available_balance) {
                    throw new \InvalidArgumentException("Insufficient available balance. Your available balance is \${$wallet->available_balance}.");
                }

                $bank = BankAccount::where('id', $request->bank_account_id)
                    ->where('tenant_id', $tenant->id)
                    ->firstOrFail();

                $withdrawal = Withdrawal::create([
                    'tenant_id' => $tenant->id,
                    'wallet_id' => $wallet->id,
                    'bank_id' => $bank->id,
                    'reference' => 'WTH-' . strtoupper(Str::random(8)),
                    'amount' => $amount,
                    'fee' => 0.0000,
                    'net_amount' => $amount,
                    'account_number' => $bank->account_number,
                    'account_name' => $bank->account_name,
                    'status' => 'pending',
                    'requested_at' => now(),
                ]);

                $this->ledger->recordWithdrawalReservation($wallet, $withdrawal);
            });
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['amount' => $e->getMessage()]);
        }

        return redirect()->route('reseller.wallet.index')->with('success', 'Withdrawal request submitted successfully.');
    }
}
