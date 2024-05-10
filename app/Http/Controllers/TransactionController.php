<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestDeposite;
use App\Http\Requests\RequestWithdrawal;
use App\Models\Transactions;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function showAllDepositedTransactions(): \Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            $depositedTransactions = Transactions::where('transaction_type', 'deposit')->get();
        } catch (Exception $exception) {
            return response([
                'error' => $exception->getMessage()
            ], 500);
        }
        return response([
            'depositedTransactions' => $depositedTransactions
        ], 200);
    }

    public function deposit(RequestDeposite $request)
    {
        try {
            DB::beginTransaction();

            Transactions::create([
                'user_id' => auth()->user()->id,
                'transaction_type' => 'deposit',
                'amount' => $request->amount,
                'date' => now()
            ]);

            User::whereId(auth()->user()->id)->increment('balance', $request->amount);
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            return response([
                'error' => $exception->getMessage()
            ], 500);
        }

        return response([
            'message' => 'Deposited successfully'
        ], 200);
    }

    public function showAllWithdrawalTransactions()
    {
        try {
            $withdrawalTransactions = Transactions::where('transaction_type', 'withdrawal')->get();
        } catch (Exception $exception) {
            return response([
                'error' => $exception->getMessage()
            ], 500);
        }
        return response([
            'withdrawalTransactions' => $withdrawalTransactions
        ], 200);
    }

    public function withdrawal(RequestWithdrawal $request)
    {
        try {
            DB::beginTransaction();
            $user = User::findOrFail($request->user_id);

            $accountType = $user->account_type;
            $totalWithdrawalAmount = Transactions::where('user_id', $user->id)->where('transaction_type', 'withdrawal')->sum('amount');
            $withdrawalAmount = $request->amount;

            $freeWithdrawalConditions = $this->checkFreeWithdrawalConditions($request);

            if ($freeWithdrawalConditions['is_free']) {
                $withdrawalFee = 0;
            } else {
                $withdrawalFee = $this->calculateWithdrawalFee($accountType, $totalWithdrawalAmount, $withdrawalAmount);
            }
            $netWithdrawalAmount = $withdrawalAmount + $withdrawalFee;

            if ($user->balance < $netWithdrawalAmount) {
                return response([
                    'error' => 'Insufficient balance'
                ], 400);
            }

            Transactions::create([
                'user_id' => auth()->user()->id,
                'transaction_type' => 'withdrawal',
                'amount' => $request->amount,
                'fee' => $withdrawalFee,
                'date' => now()
            ]);

            User::whereId(auth()->user()->id)->decrement('balance', $netWithdrawalAmount);
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            return response([
                'error' => $exception->getMessage()
            ], 500);
        }

        return response([
            'message' => 'Withdrawal successful'
        ], 200);
    }

    private function checkFreeWithdrawalConditions($request): array
    {
        $today = Carbon::today();
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();

        $isFridayWithdrawal = ($today->dayOfWeek === Carbon::FRIDAY);
        $isFirst1KFree = ($request->amount <= 1000);
        $totalWithdrawnThisMonth = Transactions::where('user_id', auth()->user()->id)
            ->where('transaction_type', 'withdrawal')
            ->whereBetween('date', [$currentMonthStart, $currentMonthEnd])
            ->sum('amount');
        $isFirst5KFreeThisMonth = ($totalWithdrawnThisMonth < 5000);

        return [
            'is_free' => ($isFridayWithdrawal || $isFirst1KFree || ($request->amount <= 5000 && $isFirst5KFreeThisMonth)),
        ];
    }

    private function calculateWithdrawalFee($accountType, $totalWithdrawalAmount, $withdrawalAmount): float
    {
        if ($accountType === User::TYPE_INDIVIDUAL) {
            $withdrawalFeeRate = 0.015;
        } else {
            $withdrawalFeeRate = ($totalWithdrawalAmount > 50000) ? 0.015 : 0.025;
        }
        return $withdrawalAmount * $withdrawalFeeRate;
    }
}
