<?php

namespace App\Services\Finance;

use App\Models\Order;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletLedgerService
{
    public function recordOrderProfit(Order $order, mixed $payment = null): ?WalletTransaction
    {
        return $this->recordOrderEarning($order);
    }

    public function recordOrderEarning(Order $order): ?WalletTransaction
    {
        if ($order->profit_amount <= 0) {
            return null;
        }

        return DB::transaction(function () use ($order) {
            $wallet = Wallet::firstOrCreate(
                ['tenant_id' => $order->tenant_id],
                ['currency' => 'USD', 'balance' => 0, 'available_balance' => 0, 'pending_balance' => 0]
            );

            $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

            // Idempotency check: duplicate payment confirmations or webhooks
            $existing = WalletTransaction::where('wallet_id', $wallet->id)
                ->where('order_id', $order->id)
                ->where('type', 'sale')
                ->first();

            if ($existing) {
                return $existing;
            }

            $amount = $order->profit_amount;
            $balanceBefore = $wallet->balance;
            $balanceAfter = $balanceBefore + $amount;

            $wallet->update([
                'balance' => $balanceAfter,
                'available_balance' => $wallet->available_balance + $amount,
            ]);

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'tenant_id' => $order->tenant_id,
                'reference' => 'TXN-ORD-' . $order->id . '-' . Str::random(4),
                'type' => 'sale',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'direction' => 'credit',
                'status' => 'completed',
                'order_id' => $order->id,
                'description' => "Profit credited for Order #{$order->order_number}",
                'created_at' => now(),
            ]);
        });
    }

    public function recordWithdrawalReservation(Wallet $wallet, Withdrawal $withdrawal): WalletTransaction
    {
        return DB::transaction(function () use ($wallet, $withdrawal) {
            $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

            $amount = $withdrawal->amount;
            $balanceBefore = $wallet->balance;
            $balanceAfter = $balanceBefore - $amount;

            $wallet->update([
                'balance' => $balanceAfter,
                'available_balance' => $wallet->available_balance - $amount,
                'pending_balance' => $wallet->pending_balance + $amount,
            ]);

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'tenant_id' => $wallet->tenant_id,
                'reference' => 'TXN-WTH-' . $withdrawal->id . '-' . Str::random(4),
                'type' => 'withdrawal',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'direction' => 'debit',
                'status' => 'completed',
                'withdrawal_id' => $withdrawal->id,
                'description' => "Funds reserved for Withdrawal #{$withdrawal->reference}",
                'created_at' => now(),
            ]);
        });
    }

    public function recordWithdrawalReversal(Withdrawal $withdrawal, string $reason): WalletTransaction
    {
        return DB::transaction(function () use ($withdrawal, $reason) {
            $wallet = Wallet::where('id', $withdrawal->wallet_id)->lockForUpdate()->first();

            $amount = $withdrawal->amount;
            $balanceBefore = $wallet->balance;
            $balanceAfter = $balanceBefore + $amount;

            $wallet->update([
                'balance' => $balanceAfter,
                'available_balance' => $wallet->available_balance + $amount,
                'pending_balance' => max(0, $wallet->pending_balance - $amount),
            ]);

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'tenant_id' => $wallet->tenant_id,
                'reference' => 'TXN-REV-' . $withdrawal->id . '-' . Str::random(4),
                'type' => 'withdrawal_reversal',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'direction' => 'credit',
                'status' => 'completed',
                'withdrawal_id' => $withdrawal->id,
                'description' => "Reversal for rejected/failed Withdrawal #{$withdrawal->reference}: {$reason}",
                'created_at' => now(),
            ]);
        });
    }
}
