<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Payment;

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
                    if ($this->checkPayment($id)) {
                        return;
                    }
                    $this->createOrderStatus($id, 'success', '4', 'Pembayaran Berhasil');
                    sleep(2);
                    $this->createOrderStatus($id, 'pending', '5', 'Pesananmu Sedang Diantar');
                    $this->firebaseService->sendNotification($order->user->notification_token, 'Pembayaran berhasil', 'Pesananmu nomor ' . $order->no_pemesanan . ' akan segera diantar ', '', ['route' => '/transaction-page', 'data' => $order->id]);
                    return;
                }
                if ($order->metode_pembayaran == 'tunai') {
                    $this->createOrderStatus($id, 'success', '4', 'Pembayaran dilakukan secara tunai');
                    sleep(2);
                    $this->createOrderStatus($id, 'pending', '5', 'Pesananmu Sedang Diantar');
                    $this->firebaseService->sendNotification($order->user->notification_token, 'Pesanan telah selesai', 'Pesananmu nomor ' . $order->no_pemesanan . ' bisa segera diantar ', '', ['route' => '/transaction-page', 'data' => $order->id]);
                    return;
                }
            }
            if ($order->jenis_pemesanan == 'antar_mandiri') {
                if ($order->metode_pembayaran == 'non_tunai') {
                    if ($this->checkPayment($id)) {
                        return;
                    }
                    $this->createOrderStatus($id, 'success', '4', 'Pembayaran Berhasil');
                    sleep(2);
                    $this->createOrderStatus($id, 'pending', '5', 'Menunggu Pengambilan');
                    $this->firebaseService->sendNotification($order->user->notification_token, 'Pembayaran berhasil', 'Pesananmu nomor ' . $order->no_pemesanan . ' bisa segera diambil ', '', ['route' => '/transaction-page', 'data' => $order->id]);
                    return;
                }
                if ($order->metode_pembayaran == 'tunai') {
                    $this->createOrderStatus($id, 'success', '4', 'Pembayaran dilakukan secara tunai');
                    sleep(2);
                    $this->createOrderStatus($id, 'pending', '5', 'Menunggu Pengambilan');
                    $this->firebaseService->sendNotification($order->user->notification_token, 'Pesanan telah selesai dan bisa diambil', 'Pesananmu nomor ' . $order->no_pemesanan . ' bisa segera diambil ', '', ['route' => '/transaction-page', 'data' => $order->id]);
                    return;
                }
            }
        }
        if ($orderStatus == 5) {
            if ($order->jenis_pemesanan == 'antar_jemput') {
                $this->createOrderStatus($id, 'success', '5', 'Pesanan telah diantar');
                $order->status = 'completed';
                $order->save();
                $this->firebaseService->sendNotification($order->user->notification_token, 'Laundrymu sudah sampai', 'Pesananmu nomor ' . $order->no_pemesanan . ' sudah diantar ke alamat ', '', ['route' => '/transaction-page', 'data' => $order->id]);
                return;
            }
            if ($order->jenis_pemesanan == 'antar_mandiri') {
                $this->createOrderStatus($id, 'success', '5', 'Pesanan telah selesai');
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
        $this->firebaseService->sendNotification($order->user->notification_token, 'Pesanan Dibatalkan', 'Pesananmu dengan nomor ' . $order->no_pemesanan . 'Telah dibatalkan', '', ['route' => '/transaction-page', 'data' => $order->id]);
        return true;

    }

    public function checkPayment($id)
    {
        $payment = Payment::where('order_id', $id)->first();
        if ($payment == null) {
            return false;
        }
        if ($payment->status == 'paid' || $payment->status == 'settled') {
            return true;
        }

    }

}
