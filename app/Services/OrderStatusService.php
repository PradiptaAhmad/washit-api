<?php

namespace App\Services;

use App\Models\History;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\TransactionHistory;

/**
 * Class OrderStatusService.
 */
class OrderStatusService
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function createOrderStatus($order_id, $status, $status_code, $status_description)
    {
        $orderStatus = new OrderStatus();
        $orderStatus->order_id = $order_id;
        $orderStatus->status = $status;
        $orderStatus->status_code = $status_code;
        $orderStatus->status_description = $status_description;
        $orderStatus->save();
        return $orderStatus;
    }

    public function updateStatus($id) {
        $order = Order::where('id', $id)->first();
        $orderStatus = OrderStatus::where('order_id', $id)->latest()->first();

        if ($orderStatus == null) {
            $this->createOrderStatus($id, 'pending', '1', 'Pesanan Telah Dibuat');
            $this->firebaseService->sendNotification($order->user->notification_token, 'Pesanan Telah Dibuat', 'Pesananmu dengan nomor ' . $order->no_pemesanan . ' Menunggu Konfirmasi', '', ['route' => '/transaction-page', 'data' => $order->id]);
            return;
        }
        $orderStatus = $orderStatus->status_code;
        if ($orderStatus == 1) {
            if($order->jenis_pemesanan == 'antar_jemput')
            {
                $this->createOrderStatus($id, 'success', '1', 'Pesanan telah dikonfirmasi');
                sleep(2);
                $this->createOrderStatus($id, 'pending', '2', 'Menunggu Penjemputan');
                $this->firebaseService->sendNotification($order->user->notification_token, 'Pesanan telah terkonfirmasi', 'Pesananmu dengan nomor ' . $order->no_pemesanan . 'Telah dikonfirmasi', '', ['route' => '/transaction-page', 'data' => $order->id]);
                return;


            }
            if ($order->jenis_pemesanan == 'antar_mandiri')
            {
                $this->createOrderStatus($id, 'success', '1', 'Pesanan telah dikonfirmasi');
                sleep(2);
                $this->createOrderStatus($id, 'pending', '2', 'Menunggu Pengantaran');
                $this->firebaseService->sendNotification($order->user->notification_token, 'Pesanan telah terkonfirmasi', 'Pesananmu dengan nomor ' . $order->no_pemesanan . 'Telah dikonfirmasi. Silahkan antar pesanan anda ke toko', '', ['route' => '/transaction-page', 'data' => $order->id]);
                return;
            }

        }
        if ($orderStatus == 2) {
            if($order->jenis_pemesanan == 'antar_jemput')
            {
                $this->createOrderStatus($id, 'success', '2', 'Pesanan telah dipickup');
                sleep(2);
                $this->createOrderStatus($id, 'pending', '3', 'Sedang diproses');
                $this->firebaseService->sendNotification($order->user->notification_token, 'Pesananmu telah di pickup', 'Kami telah mengambil pesananmu semoga sehat selalu', '', ['route' => '/transaction-page', 'data' => $order->id]);
                return;
            }
            if($order->jenis_pemesanan == 'antar_mandiri')
            {
                $this->createOrderStatus($id, 'success', '2', 'Pesanan telah diterima');
                sleep(2);
                $this->createOrderStatus($id, 'pending', '3', 'Sedang diproses');
                $this->firebaseService->sendNotification($order->user->notification_token, 'Pesananmu telah diterima', 'Pesananmu dengan nomor ' . $order->no_pemesanan . 'Telah diterima', '', ['route' => '/transaction-page', 'data' => $order->id]);
                return;
            }
        }
        if($orderStatus == 3)
        {
            if ($order->jenis_pemesanan == 'antar_jemput') {
                $this->createOrderStatus($id, 'success', '3', 'Pesanan selesai diproses');
                sleep(2);
                $this->firebaseService->sendNotification($order->user->notification_token, 'Pesananmu sudah selesai diproses', 'Pesananmu dengan nomor ' . $order->no_pemesanan . 'Selesai Diproses, Menunggu Pembayaran', '', ['route' => '/transaction-page', 'data' => $order->id]);

                if ($order->metode_pembayaran == 'non_tunai') {
                    $this->createOrderStatus($id, 'pending', '4', 'Menunggu Pembayaran');
                    $this->firebaseService->sendNotification($order->user->notification_token, 'Menunggu Pembayaran', 'Pesananmu dengan nomor ' . $order->no_pemesanan . 'Selesai Diproses, Menunggu Pembayaran', '', ['route' => '/transaction-page', 'data' => $order->id]);
                    return;
                }
                if ($order->metode_pembayaran == 'tunai') {
                    $this->createOrderStatus($id, 'pending', '4', 'Pesananmu akan segera diantar');
                    $this->firebaseService->sendNotification($order->user->notification_token, 'Pesananmu sudah selesai diproses', 'Pesananmu dengan nomor ' . $order->no_pemesanan . 'Selesai Diproses, Sesaat lagi pesananmu akan diantar', '', ['route' => '/transaction-page', 'data' => $order->id]);
                    return;
                }
                return;
            }
            if ($order->jenis_pemesanan == 'antar_mandiri') {
                $this->createOrderStatus($id, 'success', '3', 'Pesanan selesai diproses');
                sleep(2);
                $this->firebaseService->sendNotification($order->user->notification_token, 'Pesananmu sudah selesai diproses', 'Pesananmu dengan nomor ' . $order->no_pemesanan . 'Selesai Diproses', '', ['route' => '/transaction-page', 'data' => $order->id]);
                if ($order->metode_pembayaran == 'non_tunai') {
                    $this->createOrderStatus($id, 'pending', '4', 'Menunggu Pembayaran');
                    $this->firebaseService->sendNotification($order->user->notification_token, 'Menunggu Pembayaran', 'Pesananmu dengan nomor ' . $order->no_pemesanan . 'Selesai Diproses, Menunggu Pembayaran', '', ['route' => '/transaction-page', 'data' => $order->id]);
                    return;
                }
                if ($order->metode_pembayaran == 'tunai') {
                    $this->createOrderStatus($id, 'pending', '4', 'Menunggu Pengambilan');
                    $this->firebaseService->sendNotification($order->user->notification_token, 'Pesananmu sudah selesai diproses', 'Pesananmu dengan nomor ' . $order->no_pemesanan . 'Selesai Diproses, Menunggu Diambil', '', ['route' => '/transaction-page', 'data' => $order->id]);
                    return;
                }

            }
        }
        if ($orderStatus == 4) {
            if ($order->jenis_pemesanan == 'antar_jemput') {
                if ($order->metode_pembayaran == 'non_tunai') {
                    if ($this->checkPayment($id) == false) {
                        return;
                    }
                    $this->createOrderStatus($id, 'success', '4', 'Pembayaran Berhasil');
                    sleep(2);
                    $this->createOrderStatus($id, 'pending', '4', 'Pesananmu Sedang Diantar');
                    $this->firebaseService->sendNotification($order->user->notification_token, 'Pembayaran berhasil', 'Pesananmu nomor ' . $order->no_pemesanan . ' akan segera diantar ', '', ['route' => '/transaction-page', 'data' => $order->id]);
                    return;
                }
                if ($order->metode_pembayaran == 'tunai') {
                    $this->createOrderStatus($id, 'success', '4', 'Pembayaran dilakukan secara tunai');
                    sleep(2);
                    $this->createOrderStatus($id, 'pending', '4', 'Pesananmu Sedang Diantar');
                    $this->firebaseService->sendNotification($order->user->notification_token, 'Pesanan telah selesai', 'Pesananmu nomor ' . $order->no_pemesanan . ' bisa segera diantar ', '', ['route' => '/transaction-page', 'data' => $order->id]);
                    return;
                }
            }
            if ($order->jenis_pemesanan == 'antar_mandiri') {
                if ($order->metode_pembayaran == 'non_tunai') {
                    if ($this->checkPayment($id) == false) {
                        return;
                    }
                    $this->createOrderStatus($id, 'success', '4', 'Pembayaran Berhasil');
                    sleep(2);
                    $this->createOrderStatus($id, 'pending', '4', 'Menunggu Pengambilan');
                    $this->firebaseService->sendNotification($order->user->notification_token, 'Pembayaran berhasil', 'Pesananmu nomor ' . $order->no_pemesanan . ' bisa segera diambil ', '', ['route' => '/transaction-page', 'data' => $order->id]);
                    return;
                }
                if ($order->metode_pembayaran == 'tunai') {
                    $this->createOrderStatus($id, 'success', '4', 'Pembayaran dilakukan secara tunai');
                    sleep(2);
                    $this->createOrderStatus($id, 'pending', '4', 'Menunggu Pengambilan');
                    $this->firebaseService->sendNotification($order->user->notification_token, 'Pesanan telah selesai dan bisa diambil', 'Pesananmu nomor ' . $order->no_pemesanan . ' bisa segera diambil ', '', ['route' => '/transaction-page', 'data' => $order->id]);
                    return;
                }
            }
        }
        if ($orderStatus == 5) {
            if ($order->jenis_pemesanan == 'antar_jemput') {
                $this->createOrderStatus($id, 'success', '5', 'Pesanan telah diantar');
                $this->createTransaction($id);
                $order->status = 'completed';
                $order->save();
                $this->firebaseService->sendNotification($order->user->notification_token, 'Laundrymu sudah sampai', 'Pesananmu nomor ' . $order->no_pemesanan . ' sudah diantar ke alamat ', '', ['route' => '/transaction-page', 'data' => $order->id]);
                return;
            }
            if ($order->jenis_pemesanan == 'antar_mandiri') {
                $this->createOrderStatus($id, 'success', '5', 'Pesanan telah selesai');
                $this->createTransaction($id);
                $this->firebaseService->sendNotification($order->user->notification_token, 'Laundrymu sudah selesai', 'Pesananmu nomor ' . $order->no_pemesanan . ' sudah diantar ke alamat ', '', ['route' => '/transaction-page', 'data' => $order->id]);
                return;
            }
        }
    }

    public function cancelOrder($id)
    {
        $order = Order::where('id', $id)->first();
        $orderStatus = OrderStatus::where('order_id', $id)->latest()->first();
        if ($orderStatus != null && $orderStatus->status_code == '99') {
            return false;
        }
        $order->status = 'canceled';
        $order->save();

        $this->createOrderStatus($id, 'failed', '99', 'Pesanan Dibatalkan');
        $this->toHistory($order->id);
        $this->firebaseService->sendNotification($order->user->notification_token, 'Pesanan Dibatalkan', 'Pesananmu dengan nomor ' . $order->no_pemesanan . 'Telah dibatalkan', '', ['route' => '/transaction-page', 'data' => $order->id]);
        return true;

    }

    public function checkPayment($id)
    {
        $payment = Transaction::where('order_id', $id)->first();
        if ($payment == null) {
            return false;
        }
        return true;
    }

    public function toHistory($id)
    {
        $order = Order::where('id', $id)->first();
        $transaction = Transaction::where('order_id', $id)->first();
        $history = History::create(
            [
                'id' => $order->id,
                'no_pemesanan' => $order->no_pemesanan,
                'jenis_pemesanan' => $order->jenis_pemesanan,
                'nama_pemesan' => $order->nama_pemesan,
                'nomor_telepon' => $order->nomor_telepon,
                'alamat' => $order->alamat,
                'metode_pembayaran' => $order->metode_pembayaran,
                'berat_laundry' => $order->berat_laundry,
                'total_harga' => $order->total_harga,
                'status' => $order->status,
                'tanggal_pengambilan' => $order->tanggal_pengambilan,
                'tanggal_estimasi' =>   $order->tanggal_estimasi,
                'catatan' => $order->catatan,
                'laundry_service' => $order->laundry_service,
                'laundry_description' => $order->laundry_description,
                'laundry_price' => $order->laundry_price,
                'user_id' => $order->user_id,
            ]
        );
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
    }

    public function createTransaction($id)
    {
        $order = Order::where('id', $id)->first();
        Transaction::create([
            'payment_type' => 'tunai',
            'external_id' => $order->no_pemesanan,
            'payment_method' => 'tunai',
            'status' => 'PAID',
            'amount' => $order->total_harga,
            'payment_id' => null,
            'payment_channel' => 'tunai',
            'description' => 'Pembayaran tunai',
            'paid_at' => now(),
            'order_id' => $order->id,
        ]);
    }

}
