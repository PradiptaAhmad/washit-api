<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderAdminDetailResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\UserOrderDetailResource;
use App\Models\Address;
use App\Models\Admin;
use App\Models\History;
use App\Models\Laundry;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Transaction;
use App\Models\TransactionHistory;
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
        $laundry = Laundry::where('id', $request->laundry_id)->first();
        $estimasi = $laundry->estimasi_waktu;
        $estimatedDate = Carbon::now()->addDays($estimasi);
        $address = Address::where('id', $request->address_id)->first();
        $alamat = $address->street . ', Kel. ' . $address->village . ', Kec. ' . $address->district . ', ' . $address->city . ', ' . $address->province;
        $order = Order::create([
            'no_pemesanan' => $nomor_pemesanan,
            'nama_pemesan' => $request->nama_pemesan,
            'nomor_telepon' => $request->nomor_telepon,
            'jenis_pemesanan' => $request->jenis_pemesanan,
            'alamat' => $alamat,
            'catatan' => $request->catatan,
            'metode_pembayaran' => $request->metode_pembayaran,
            'tanggal_pengambilan' => $request->tanggal_pengambilan,
            'tanggal_estimasi' => $estimatedDate,
            'laundry_id' => $request->laundry_id,
            'user_id' => $user->id,
        ]);
        $this->updateStatusService->updateStatus($order->id);
        $this->firebaseService->sendToAdmin("Ada Pesanan Baru", "Ada Pesanan Baru Dari " . $user->username, '', ['route' => '/transaction_page.screen', 'data' => $order->id]);
        return response([
            'status' => 'success',
            'message' => 'Order created successfully',
            'order' => $order,
        ], 201);
    }

    public function getOrder()
    {
        $user = Auth()->user();
        $order = Order::where('user_id', $user->id)->orderBy('created_at', 'asc')->get();

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

    public function getOrderDetail(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
        ]);
        $order = Order::where('id', $request->order_id)->first();
        return response([
            'message' => 'Order Detail Fetched Successfully',
            'order' => new UserOrderDetailResource($order),
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
            ], 400);
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

    public function getChartData()
    {
        $today = Carbon::today();
        $lastMonth = $today->copy()->subDays(30);

        $data = Order::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->whereBetween('created_at', [$lastMonth->format('Y-m-d'), $today->format('Y-m-d')])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        dd($data);
        $dates = [];
        $currentDate = $lastMonth->copy();
        while ($currentDate->lte($today)) {
            $dates[$currentDate->format('Y-m-d')] = 0;
            $currentDate->addDay();
        }

        foreach ($data as $entry) {
            $dates[$entry->date] = $entry->total;
        }

        $formattedData = collect($dates)->map(function ($total, $date) {
            return [
                'date' => $date,
                'total' => $total
            ];
        })->values();

        return response()->json($formattedData);
    }

    public function completeOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
        ]);
        $order = Order::where('id', $request->order_id)->first();
        $transaction = Transaction::where('order_id', $request->order_id)->first();
        $history = History::create($order->only([
            'id',
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
        if ($transaction != null) {
            TransactionHistory::create(
                [
                    'id' => $transaction->id,
                    'payment_type' => $transaction->payment_type,
                    'external_id' => $transaction->external_id,
                    'payment_method' => $transaction->payment_method,
                    'status' => $transaction->status,
                    'amount' => $transaction->amount,
                    'payment_id' => $transaction->payment_id,
                    'payment_channel' => $transaction->payment_channel,
                    'description' => $transaction->description,
                    'paid_at' => $transaction->paid_at,
                    'history_id' => $order->id,
                ]
            );
            $transaction->delete();
        }
        $order->delete();

        return response([
            'status' => 'success',
            'message' => 'Order Completed Successfully',
        ]);
    }

}
