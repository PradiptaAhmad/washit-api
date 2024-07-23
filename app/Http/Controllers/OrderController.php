<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderAdminDetailResource;
use App\Http\Resources\OrderResource;
use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\User;
use App\Services\FirebaseService;
use App\Services\OrderStatusService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    protected $firebaseService;
    protected $updateStatusService;

    public function __construct(FirebaseService $firebaseService, OrderStatusService $updateStatusService)
    {
        $this->firebaseService = $firebaseService;
        $this->updateStatusService = $updateStatusService;
    }
    public function addOrder(OrderRequest $request)
    {
        $request->validated();
        $user = auth()->user();

        $date = date('YmdHis');
        $nomor_pemesanan = $user->id . $request->laundry_id . $date;
        $tanggal_pemesanan = Carbon::now();

        $order = Order::create([
            'no_pemesanan' => $nomor_pemesanan,
            'nama_pemesan' => $request->nama_pemesan,
            'nomor_telepon' => $request->nomor_telepon,
            'jenis_pemesanan' => $request->jenis_pemesanan,
            'alamat' => $request->alamat,
            'metode_pembayaran' => $request->metode_pembayaran,
            'tanggal_pemesanan' => $tanggal_pemesanan,
            'tanggal_pengambilan' => $request->tanggal_pengambilan,
            'laundry_id' => $request->laundry_id,
            'user_id' => $user->id,
        ]);
        $this->updateStatusService->updateStatus($order->id);
        $this->firebaseService->sendNotification($user->notification_token, 'Pesanan Telah Dibuat', 'Pesananmu dengan nomor ' . $order->no_pemesanan . 'Menunggu Konfirmasi', '');
        return response(['message' => 'Order created successfully',
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
            'order' => OrderResource::collection($order),
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

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);
        $auth = Admin::where('email', Auth::guard('admin')->user()->email)->first();
        $order = Order::where('id', $request->id)->first();
        if ($order == null) {
            return response([
                'status' => 'failed',
                'message' => 'Order Not Found'
            ], 301);
        }
        OrderStatus::create([
            'status' => 'success',
            'status_name' => 'Pesanan Telah Diterima',
            'status_description' => 'Order Accepted',
            'order_id' => $order->id,
        ]);
        $this->updateStatusService->updateStatus($request->id);
        $user = $order->user;
        $title = "Halo " . $user->username . "Pesananmu telah kami terima";
        $body = '';
        if ($order->jenis_pemesanan == 'antar_jemput') {
            $body = "Duduk santai dirumah laundry kamu akan segera diambil";
        }
        $body = "Pesanan dengan nomor " . $order->no_pemesanan . " telan kami terima";
        $message = $this->firebaseService->sendNotification($user->notification_token, $title, $body, '');
        return response([
            'status' => 'success',
            'message' => 'Order Accepted Notifiction sent successfully',
        ], 201);
    }

    public function rejectOrder(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);
        $auth = User::where('email', auth()->user()->email)->first();
        if ($auth->role != 'admin') {
            return response([
                'status' => 'failed',
                'message' => 'Not authorized'
            ], 401);
        }
        $order = Order::where('id', $request->id)->first();
        if ($order == null) {
            return response([
                'status' => 'failed',
                'message' => 'Order Not Found'
            ], 301);
        }
        if ($order->accepted == true) {
            return response([
                'status' => 'failed',
                'message' => 'Order Accepted'
            ], 401);
        }
        OrderStatus::create([
            'status' => 'failed',
            'status_name' => 'Pesanan Ditolak',
            'status_description' => 'Order Rejected',
            'order_id' => $order->id,
        ]);
        $user = $order->user;
        $title = "Hai " . $user->username . " Mohon maaf pesananmu tidak bisa kami terima";
        $body = '';
        if ($order->jenis_pemesanan == 'antar_jemput') {
            $body = "Maaf kami tidak bisa mengambil laundry kamu saat ini";
        }
        $body = "Mohon maaf pesanan dengan nomor " . $order->no_pemesanan . " tidak bisa kami terima";
        $message = $this->firebaseService->sendNotification($user->notification_token, $title, $body, '');
        return response([
            'status' => 'success',
            'message' => 'Order Rejected Notifiction sent successfully',
        ], 201);
    }

    // Admin Order Controller
    public function updateWeight(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'berat_laundry' => 'required|integer',
        ]);
        $order = Order::where('id', $request->id)->first();
        if ($order == null) {
            return response([
                'status' => 'failed',
                'message' => 'Order Not Found'
            ], 301);
        }
        $totalPrice = $order->laundry->harga * $request->berat_laundry;
        $order->berat_laundry = $request->berat_laundry;
        $order->total_harga = $totalPrice;
        $order->save();
        return response([
            'status' => 'success',
            'message' => 'Order Weight Updated Successfully',
        ], 201);
    }

    public function getAllOrders()
    {
        $orders = Order::all();
        return response([
            'status' => 'success',
            'message' => 'All Orders Fetched Successfully',
            'orders' => OrderResource::collection($orders),
        ], 200);
    }

    public function cancelOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
        ]);
        $order = Order::where('id', $request->order_id)->first();
        if ($order == null) {
            return response([
                'status' => 'failed',
                'message' => 'Order Not Found'
            ], 301);
        }
        $status = $this->updateStatusService->cancelOrder($request->order_id);
        if ($status == false) {
            return response([
                'status' => 'failed',
                'message' => 'Order Already Canceled'
            ], 301);
        }
        return response([
            'status' => 'success',
            'message' => 'Order Canceled Successfully',
        ]);
    }

    public function getAdminDetailOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
        ]);
        $order = Order::where('id', $request->order_id)->first();
        return response([
            'status' => 'success',
            'message' => 'Order Detail Fetched Successfully',
            'order' => new OrderAdminDetailResource($order),
        ], 200);
    }

}
