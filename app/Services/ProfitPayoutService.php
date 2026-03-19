<?php

namespace App\Services;

use App\Models\Bank;
use App\Models\Profit;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProfitPayoutService
{
    public function markAsPaid(Profit $profit, int $bankId, int $paidBy, ?string $detail = null): Profit
    {
        return DB::transaction(function () use ($profit, $bankId, $paidBy, $detail): Profit {
            $lockedProfit = Profit::query()
                ->with('user')
                ->lockForUpdate()
                ->findOrFail($profit->id);

            if ($lockedProfit->state !== 'pending') {
                throw ValidationException::withMessages([
                    'profit' => __('messages.admin.profits.errors.already_paid'),
                ]);
            }

            $bank = Bank::query()->lockForUpdate()->findOrFail($bankId);

            $amount = (float) $lockedProfit->amount;
            $amountPrevious = (float) $bank->amount;
            $amountNow = $amountPrevious - $amount;

            if ($amountNow < 0) {
                throw ValidationException::withMessages([
                    'bank_id' => __('messages.admin.profits.errors.insufficient_funds'),
                ]);
            }

            $transaction = Transaction::query()->create([
                'bank_id' => $bank->id,
                'type' => 'expense',
                'amount_previous' => $amountPrevious,
                'amount' => $amount,
                'amount_now' => $amountNow,
                'detail' => $detail ?: __('messages.admin.profits.expense_detail', [
                    'profit_id' => $lockedProfit->id,
                    'user' => $lockedProfit->user?->name,
                ]),
                'is_annulled' => false,
                'created_at' => now(),
            ]);

            $bank->amount = $amountNow;
            $bank->save();

            $lockedProfit->state = 'made';
            $lockedProfit->paid_by = $paidBy;
            $lockedProfit->paid_at = now();
            $lockedProfit->transaction_id = $transaction->id;
            $lockedProfit->detail = $detail ?: $lockedProfit->detail;
            $lockedProfit->save();

            if ($lockedProfit->user instanceof User) {
                $current = (float) $lockedProfit->user->commission_balance;
                $lockedProfit->user->commission_balance = max(0, round($current - $amount, 2));
                $lockedProfit->user->save();
            }

            return $lockedProfit->fresh(['user', 'userBank', 'transaction.bank', 'payer']);
        });
    }
}
