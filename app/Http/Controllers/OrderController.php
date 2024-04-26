<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteRequest;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;  

class OrderController extends Controller
{
    public function addOrder(OrderRequest $request)
    {
        $request->validated();
        $user = Auth()->user();

        $date = date('YmdHis');
        $nomor_pemesanan = $user->id . $request->laundry_id . $date;
        $tanggal_pemesanan = Carbon::now();

        $order = Order::create([
            'no_pemesanan' => $nomor_pemesanan,
            'nama_pemesan' => $request->nama_pemesan,
            'nomor_telepon' => $request->nomor_telepon,
            'jenis_pemesanan' => $request->jenis_pemesanan,
            'alamat' => $request->alamat,
            'tanggal_pemesanan' => $tanggal_pemesanan,
            'status' => 'Menunggu Konfirmasi',
            'tanggal_pengambilan' => $request->tanggal_pengambilan,
            'laundry_id' => $request->laundry_id,
            'user_id' => $user->id,
        ]);
        return response([
            'message' => 'Order added successfully',
            'order' => $order,
        ], 201);
    }

    public function getOrder()
    {
        $user = Auth()->user();
        $order = Order::where('user_id', $user->id)->get();

        if ($order == null) {
            return response([
                'message' => 'Order is empty',
            ], 200);
        }
        return response([
            'message' => 'Order fetched successfully',
            'order' => $order,
        ], 200);
    }

    public function deleteOrder($id)
    {
        $order = Order::where('id', $id)->first();

        if ($order == null) {
            return response([
                'message' => 'Order not found',
            ], 404);
        }
        $order->delete();
        return response([
            'message' => 'Order deleted successfully',
        ], 200);
    }
}
