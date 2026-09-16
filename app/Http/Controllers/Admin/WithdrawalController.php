<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Withdrawal;
use App\Services\Finance\WalletLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class WithdrawalController extends Controller
{
    protected WalletLedgerService $ledger;

    public function __construct(WalletLedgerService $ledger)
    {
        $this->ledger = $ledger;
    }

    public function index(Request $request)
    {
        Gate::authorize('access-finance');

        $query = Withdrawal::with(['tenant', 'wallet', 'bankAccount', 'processedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('reference', 'like', '%' . $request->search . '%');
        }

        $withdrawals = $query->orderBy('id', 'desc')->paginate(15);

        return view('admin.withdrawals.index', compact('withdrawals'));
    }

    public function show(Withdrawal $withdrawal)
    {
        Gate::authorize('access-finance');

        $withdrawal->load(['tenant', 'wallet', 'bankAccount', 'processedBy']);

        return view('admin.withdrawals.show', compact('withdrawal'));
    }

    public function approve(Request $request, Withdrawal $withdrawal)
    {
        Gate::authorize('access-finance');

        if ($withdrawal->status !== 'pending' && $withdrawal->status !== 'processing') {
            return back()->withErrors(['withdrawal' => 'Only pending or processing withdrawals can be approved.']);
        }

        DB::transaction(function () use ($withdrawal, $request) {
            $withdrawal = Withdrawal::where('id', $withdrawal->id)->lockForUpdate()->firstOrFail();

            $withdrawal->update([
                'status' => 'paid',
                'processed_at' => now(),
                'processed_by' => auth()->id(),
                'notes' => $request->notes,
            ]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'tenant_id' => $withdrawal->tenant_id,
                'action' => 'withdrawal_approved',
                'entity_type' => 'Withdrawal',
                'entity_id' => $withdrawal->id,
                'old_values' => ['status' => $withdrawal->getOriginal('status')],
                'new_values' => ['status' => 'paid', 'amount' => $withdrawal->amount],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return redirect()->route('admin.withdrawals.index')->with('success', "Withdrawal #{$withdrawal->reference} approved and marked paid.");
    }

    public function reject(Request $request, Withdrawal $withdrawal)
    {
        Gate::authorize('access-finance');

        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        if ($withdrawal->status !== 'pending' && $withdrawal->status !== 'processing') {
            return back()->withErrors(['withdrawal' => 'Only pending or processing withdrawals can be rejected.']);
        }

        DB::transaction(function () use ($withdrawal, $request) {
            $withdrawal = Withdrawal::where('id', $withdrawal->id)->lockForUpdate()->firstOrFail();

            $withdrawal->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'processed_at' => now(),
                'processed_by' => auth()->id(),
            ]);

            // Release reserved funds back to wallet available balance
            $this->ledger->recordWithdrawalReversal($withdrawal, $request->rejection_reason);

            AuditLog::create([
                'user_id' => auth()->id(),
                'tenant_id' => $withdrawal->tenant_id,
                'action' => 'withdrawal_rejected',
                'entity_type' => 'Withdrawal',
                'entity_id' => $withdrawal->id,
                'old_values' => ['status' => $withdrawal->getOriginal('status')],
                'new_values' => ['status' => 'rejected', 'reason' => $request->rejection_reason],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return redirect()->route('admin.withdrawals.index')->with('success', "Withdrawal #{$withdrawal->reference} rejected and reserved funds released.");
    }
}
