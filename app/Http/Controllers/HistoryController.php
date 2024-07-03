<?php

namespace App\Http\Controllers;

use App\Http\Resources\HistoryResource;
use App\Models\History;
use App\Models\Order;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function getHistory()
    {
        $user = auth()->user();
        $histories = History::where('user_id', $user->id)->get();

        if ($histories == null) {
            return response([
                'status' => 'failed',
                'message' => 'No history found',
            ]);
        }
        return new HistoryResource($histories);
    }

    public function migrateToHistories()
    {
        $orders = Order::all();
        foreach ($orders as $order) {
            History::create([
                'no_pemesanan' => $order->no_pemesanan,
                'nama_pemesan' => $order->nama_pemesan,
                'nomor_telepon' => $order->nomor_telepon,
                'jenis_pemesanan' => $order->jenis_pemesanan,
                'alamat' => $order->alamat,
                'tanggal_pemesanan' => $order->tanggal_pemesanan,
                'tanggal_pengambilan' => $order->tanggal_pengambilan,
                'laundry_id' => $order
            ]);
        }
    }
}
