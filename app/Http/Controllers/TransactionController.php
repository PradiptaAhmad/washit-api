<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Transaction;
use App\Models\TransactionHistory;
use App\Services\FirebaseService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    protected $firebaseService;
    protected $chartController;
    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
        $this->chartController = new TransactionChartController();
    }
    public function invoiceStatus(Request $request)
    {
        $payment = Payment::where('external_id', $request->external_id)->first();
        if ($payment == null) {
            return response([
                'status' => 'failed',
                'message' => 'Payment not found',
            ], 404);
        }
        $payment->status = strtolower($request->status);
        $payment->save();

        $transaction = Transaction::where('order_id', $payment->order_id)->first();
        if ($transaction == null) {
            Transaction::create([
                'payment_type' => $payment->order->metode_pembayaran,
                'external_id' => $request->external_id,
                'payment_method' => $request->payment_method,
                'status' => $request->status,
                'amount' => $request->amount,
                'payment_id' => $request->payment_id,
                'payment_channel' => $request->payment_channel,
                'description' => $request->description,
                'paid_at' => Carbon::parse($request->paid_at),
                'order_id' => $payment->order_id,
            ]);
        } else {
            $transaction->update([
                'payment_type' => $payment->order->metode_pembayaran,
                'external_id' => $request->external_id,
                'payment_method' => $request->payment_method,
                'status' => $request->status,
                'amount' => $request->amount,
                'payment_id' => $request->payment_id,
                'payment_channel' => $request->payment_channel,
                'description' => $request->description,
                'paid_at' => Carbon::parse($request->paid_at),
                'order_id' => $payment->order_id,
            ]);
        }

        if (strtolower($request->status) == 'paid') {
            $this->firebaseService->sendNotification($payment->user->notification_token, 'Pembayaran Berhasil', 'Pembayaran untuk laundry ' . $payment->order->no_pemesanan . '. Telah terbayarkan', '');
            $this->chartController->updateChart();
        }

        return response([
            'status' => 'success',
            'message' => 'Payment status updated',
            'payment_status' => $payment->status,
        ], 200);
    }

    public function getTransaction(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);
        $transaction = Transaction::where('order_id', $request->id)->first();
        $transactionHistory = TransactionHistory::where('history_id', $request->id)->first();
        if ($transaction != null) {
            return response([
                'status' => 'success',
                'message' => 'Get transaction successfully',
                'transaction' => $transaction,
            ], 200);
        }
        if ($transactionHistory != null) {
            return response([
                'status' => 'success',
                'message' => 'Get transaction successfully',
                'transaction' => $transactionHistory,
            ], 200);
        }

        return response([
            'status' => 'failed',
            'message' => 'Transaction not found',
        ], 404);
    }

    public function getAllTransaction()
    {
        $transactions = Transaction::select(['id', 'payment_type', 'external_id', 'payment_method', 'status', 'amount', 'payment_id', 'payment_channel', 'description', 'paid_at']);
        $transactionHistories = TransactionHistory::select(['id', 'payment_type', 'external_id', 'payment_method', 'status', 'amount', 'payment_id', 'payment_channel', 'description', 'paid_at']);
        $mergeTransaction = $transactions->union($transactionHistories)->orderBy('id', 'desc')->get();
        return response([
            'status' => 'success',
            'message' => 'Get all transaction successfully',
            'total_amout' => $mergeTransaction->sum('amount'),
            'total_transaction' => $mergeTransaction->count(),
            'transactions' => $mergeTransaction,
        ], 200);
    }

    public function deleteTransaction(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $transaction = Transaction::where('id', $request->id)->first();
        $transactionHistory = TransactionHistory::where('id', $request->id)->first();
        if ($transaction != null) {
            $transaction->delete();
            return response([
                'status' => 'success',
                'message' => 'Transaction deleted successfully',
            ], 200);
        }
        if ($transactionHistory != null) {
            $transactionHistory->delete();
            return response([
                'status' => 'success',
                'message' => 'Transaction deleted successfully',
            ], 200);
        }
        return response([
            'status' => 'failed',
            'message' => 'Transaction not found',
        ], 404);
    }
}
