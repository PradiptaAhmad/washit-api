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
        $orderStatus = OrderStatus::where('order_id', $id)->orderBy('created_at', 'desc')->first();

        if ($orderStatus == null) {
            $this->createOrderStatus($id, 'success', '0', 'Pesanan Telah Dibuat');
            $this->firebaseService->sendNotification($order->user->notification_token, 'Pesanan Telah Dibuat', 'Pesananmu dengan nomor ' . $order->no_pemesanan . ' Menunggu Konfirmasi', '');
            return;
        }
        $orderStatus = $orderStatus->status_code;
        if ($orderStatus == 0) {
            if($order->jenis_pemesanan == 'antar_jemput')
            {
                $this->createOrderStatus($id, 'success', '1', 'Pesanan telah dikonfirmasi');
                $this->firebaseService->sendNotification($order->user->notification_token, 'Pesanan telah terkonfirmasi', 'Pesananmu dengan nomor ' . $order->no_pemesanan . 'Telah dikonfirmasi', '');

                $this->createOrderStatus($id, 'pending', '2', 'Menunggu Penjemputan');
                $this->firebaseService->sendNotification($order->user->notification_token, 'Menunggu Penjemputan', 'Kami akan mengambil pesananmu', '');
                return;


            }
            if ($order->jenis_pemesanan == 'antar_mandiri')
            {
                $this->createOrderStatus($id, 'success', '1', 'Pesanan telah dikonfirmasi');
                $this->firebaseService->sendNotification($order->user->notification_token, 'Pesanan telah terkonfirmasi', 'Pesananmu dengan nomor ' . $order->no_pemesanan . 'Telah dikonfirmasi', '');

                $this->createOrderStatus($id, 'pending', '2', 'Menunggu Pengantaran');
                $this->firebaseService->sendNotification($order->user->notification_token, 'Menunggu Pengantaran', 'Pesananmu dengan nomor ' . $order->no_pemesanan . '', '');
                return;
            }

        }
        if ($orderStatus == 2) {
            if($order->jenis_pemesanan == 'antar_jemput')
            {
                $this->createOrderStatus($id, 'success', '2', 'Pesanan telah dipickup');
                $this->createOrderStatus($id, 'pending', '3', 'Sedang diproses');

                $this->firebaseService->sendNotification($order->user->notification_token, 'Pesananmu telah di pickup', 'Kami telah mengambil pesananmu semoga sehat selalu', '');
                return;
            }
            if($order->jenis_pemesanan == 'antar_mandiri')
            {
                $this->createOrderStatus($id, 'success', '2', 'Pesanan telah diterima');
                $this->createOrderStatus($id, 'pending', '3', 'Sedang diproses');
                return;
            }
        }
        if($orderStatus == 3)
        {
            if ($order->jenis_pemesanan == 'antar_jemput') {
                $this->createOrderStatus($id, 'success', '3', 'Pesanan selesai diproses');
                $this->createOrderStatus($id, 'pending', '4', 'Menunggu Pembayaran');
                return;
            }
            if ($order->jenis_pemesanan == 'antar_mandiri') {
                $this->createOrderStatus($id, 'success', '3', 'Pesanan selesai diproses');
                return;
                if($order->payment_method == 'cashless') {
                    $this->createOrderStatus($id, 'pending', '4', 'Menunggu Pembayaran');
                    return;
                }
                if($order->payment_method == 'cash') {
                    $this->createOrderStatus($id, 'pending', '4', 'Menunggu Pengambilan');
                    return;
                }

            }
        }
        if ($orderStatus == 4) {
            if ($order->jenis_pemesanan == 'antar_jemput') {
                if ($this->checkPayment($id)) {
                    return;
                }
                $this->createOrderStatus($id, 'success', '4', 'Pembayaran Berhasil');
                $this->createOrderStatus($id, 'pending', '5', 'Sedang diantar menuju alamat');
                $this->firebaseService->sendNotification($order->user->notification_token, 'Pembayaran berhasil', 'Pesananmu nomor ' . $order->no_pemesanan . ' akan segera kami antar ke lokasi ', '');
                return;
            }
            if ($order->jenis_pemesanan == 'antar_mandiri') {
                if ($order->metode_pembayaran == 'non_tunai') {
                    if ($this->checkPayment($id)) {
                        return;
                    }
                    $this->createOrderStatus($id, 'success', '4', 'Pembayaran Berhasil');
                    $this->createOrderStatus($id, 'pending', '5', 'Menunggu Pengambilan');
                    $this->firebaseService->sendNotification($order->user->notification_token, 'Pembayaran berhasil', 'Pesananmu nomor ' . $order->no_pemesanan . ' bisa segera diambil ', '');
                    return;
                }
                if ($order->metode_pembayaran == 'tunai') {
                    $this->createOrderStatus($id, 'success', '4', 'Pembayaran dilakukan secara tunai');
                    $this->createOrderStatus($id, 'pending', '5', 'Menunggu Pengambilan');
                    $this->firebaseService->sendNotification($order->user->notification_token, 'Pesanan telah selesai dan bisa diambil', 'Pesananmu nomor ' . $order->no_pemesanan . ' bisa segera diambil ', '');
                    return;
                }
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
        $this->createOrderStatus($id, 'failed', '99', 'Pesanan Dibatalkan');
        $this->firebaseService->sendNotification($order->user->notification_token, 'Pesanan Dibatalkan', 'Pesananmu dengan nomor ' . $order->no_pemesanan . 'Telah dibatalkan', '');
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
