<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionChart;
use App\Models\TransactionHistory;
use Illuminate\Http\Request;

class TransactionChartController extends Controller
{
    public function updateChart()
    {
        $transaction = Transaction::whereDate('created_at', now()->format('Y-m-d'))->where('status', 'paid');
        $totalTransactions = $transaction->count();
        $totalIncome = $transaction->sum('amount');
        $transactionChart = TransactionChart::whereDate('created_at', now()->format('Y-m-d'))->first();
        if ($transactionChart != null) {
            $transactionChart->total_transactions = $totalTransactions;
            $transactionChart->total_income  = $totalIncome;
            $transactionChart->save();
        } else {
            TransactionChart::create([
                'total_transactions' => $totalTransactions,
                'total_income' => $totalIncome,
            ]);
        }
    }

    public function getDailyChart()
    {
        $transactionChart = TransactionChart::whereDate('created_at', now()->format('Y-m-d'))->first();
        if ($transactionChart == null) {
            return response([
                'status' => 'failed',
                'message' => 'No transaction chart found',
            ]);
        }
        return response([
            'status' => 'success',
            'message' => 'Transaction chart retrieved successfully',
            'total_income' => $transactionChart->total_income,
            'data' => $transactionChart,
        ]);
    }

    public function getWeeklyChart()
    {
        $transactionChart = TransactionChart::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->get();
        if ($transactionChart == null) {
            return response([
                'status' => 'failed',
                'message' => 'No transaction chart found',
            ]);
        }
        return response([
            'status' => 'success',
            'message' => 'Transaction chart retrieved successfully',
            'total_income' => $transactionChart->sum('total_income'),
            'data' => $transactionChart,
        ]);
    }

    public function getMonthlyChart()
    {
        $transactionChart = TransactionChart::whereMonth('created_at', now()->month)->get();
        if ($transactionChart == null) {
            return response([
                'status' => 'failed',
                'message' => 'No transaction chart found',
            ]);
        }
        return response([
            'status' => 'success',
            'message' => 'Transaction chart retrieved successfully',
            'total_income' => $transactionChart->sum('total_income'),
            'data' => $transactionChart,
        ]);
    }

}
