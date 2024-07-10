<?php

namespace App\Http\Controllers;

require_once base_path('/vendor/autoload.php');

use App\Http\Requests\PaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice;
use Xendit\Invoice\Invoice as InvoiceInvoice;

Configuration::setXenditKey(env('XENDIT_SECRET_KEY'));


class PaymentController extends Controller
{
    protected $invoiceApi;
    public function __construct(InvoiceApi $invoiceApi)
    {
        $this->invoiceApi = $invoiceApi;
    }

    public function createPayment(PaymentRequest $request)
    {
        $request->validated();
        $user = auth()->user();
        $order = Order::where('id', $request->order_id)->first();
        $external_id = (string) date('YmdHis');
        $description = 'Membayar Laundry ' . $order->laundry->nama_laundry . ' ' . $user->username;
        $amount = $order->total_harga;

        $transaction = Payment::where('order_id', $order->id)->first();
        if($transaction->status == 'paid')
        {
            return response([
                'status' => 'failed',
                'message' => 'Payment already paid',
            ], 400);
        }
        if ($transaction != null && $transaction->status == 'pending') {
            return response([
                'status' => 'success',
                'message' => 'Payment created successfully',
                'checkout_link' => $transaction->checkout_link,
            ], 201);
        }
        $options = [
            'external_id' => $external_id,
            'description' => $description,
            'amount' => $amount,
            'currency' => 'IDR',
        ];
        $response = $this->invoiceApi->createInvoice($options);

        $payment = new Payment();
        $payment->status = 'pending';
        $payment->invoice_id = $response['id'];
        $payment->checkout_link = $response['invoice_url'];
        $payment->external_id = $external_id;
        $payment->user_id = $user->id;
        $payment->order_id = $order->id;
        $payment->save();

        return response([
            'status' => 'success',
            'message' => 'Payment created successfully',
            'checkout_link' => $response['invoice_url'],
        ], 201);
    }

    public function expirePayment($id)
    {
        $payment = Payment::where('order_id', $id)->first();
        if ($payment == null) {
            return response([
                'status' => 'failed',
                'message' => 'Payment not found',
            ], 404);
        }
        if($payment->status == 'expired') {
            return response([
                'status' => 'failed',
                'message' => 'Payment already expired',
            ], 400);
        }
        $this->invoiceApi->expireInvoice($payment->invoice_id);
        $payment->status = 'expired';
        $payment->save();

        return response([
            'status' => 'success',
            'message' => 'Payment expired',
        ], 200);
    }

    public function updatePaymentStatus(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
        ]);
        $payment = Payment::where('order_id', $request->order_id)->first();
        if ($payment == null) {
            return response([
                'status' => 'failed',
                'message' => 'Payment not found',
            ], 404);
        }
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode(env("XENDIT_SECRET_KEY") . ':'),
        ])->get('https://api.xendit.co/v2/invoices/' . $payment->invoice_id);

        $payment->status = strtolower(json_decode($response->body(), true)['status']);
        $payment->save();

        return response([
            'status' => 'success',
            'message' => 'Payment status updated',
            'payment_status' => $payment->status,
        ], 200);
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
        return response([
            'status' => 'success',
            'message' => 'Payment status updated',
            'payment_status' => $payment->status,
        ], 200);
    }
}
