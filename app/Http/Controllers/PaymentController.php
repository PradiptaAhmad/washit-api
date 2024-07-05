<?php

namespace App\Http\Controllers;

require_once base_path('/vendor/autoload.php');

use App\Http\Requests\PaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Xendit\Configuration;
use Xendit\Invoice\Invoice;
use Xendit\Invoice\InvoiceApi;

Configuration::setXenditKey('xnd_development_c4YytwtLcsFH5XnTvzCySGI3JNqWg4rWFK13VNg4Fl0ValXaBrlFPIOd5xPxxKI1');


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
        $description = $request->description;
        $amount = $request->amount;

        $transaction = Payment::where('order_id', $order->id)->first();
        if ($transaction != null) {
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

    public function expirePayment(Request $request)
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

    
}
