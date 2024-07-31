<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\History;
use Illuminate\Console\Command;

class TransferOrderToHistoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:transfer-order-to-history-command';

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
        $orders = Order::where('status', 'completed')->get();
        foreach ($orders as $order) {
            History::create($order->only([
                'no_pemesanan',
                'jenis_pemesanan',
                'nama_pemesan',
                'nomor_telepon',
                'alamat',
                'metode_pembayaran',
                'berat_laundry',
                'total_harga',
                'status',
                'tanggal_pengambilan',
                'tanggal_estimasi',
                'laundry_id',
                'user_id',
            ]));
            $order->delete();
        }
    }
}
