<?php

namespace App\Console\Commands;

use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class UpdateAllPaymentStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-all-payment-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $payments = Payment::all();
        foreach ($payments as $payment) {
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode(env("XENDIT_SECRET_KEY") . ':'),
            ])->get('https://api.xendit.co/v2/invoices/' . $payment->invoice_id);
            $payment->status = strtolower(json_decode($response->body(), true)['status']);
            $payment->save();
        }
    }
}
