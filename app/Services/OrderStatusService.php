<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatus;

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
        $orderStatus = OrderStatus::where('order_id', $id)->orderBy('created_at', 'desc')->first()->status_code;
        if ($orderStatus == 1) {
            if($order->jenis_pemesanan == 'antar_jemput')
            {
                $this->createOrderStatus($id, 'success', '1', 'Pesanan telah dikonfirmasi');
                $this->firebaseService->sendNotification($order->user->notification_token, 'Pesanan telah terkonfirmasi', 'Pesananmu dengan nomor ' . $order->no_pemesanan . 'Telah dikonfirmasi', '');

                $this->createOrderStatus($id, 'pending', '2', 'Menunggu Penjemputan');
                $this->firebaseService->sendNotification($order->user->notification_token, 'Menunggu Penjemputan', 'Kami akan mengambil pesananmu', '');


            }
            if ($order->jenis_pemesanan == 'antar_mandiri')
            {
                $this->createOrderStatus($id, 'pending', '2', 'Menunggu Konfirmasi');
                $this->firebaseService->sendNotification($order->user->notification_token, 'Menunggu Konfirmasi', 'Pesananmu dengan nomor ' . $order->no_pemesanan . 'Telah dikonfirmasi', '');
            }

        }
        if ($orderStatus == 2) {
            if($order->jenis_pemesanan == 'antar_jemput')
            {
                $this->createOrderStatus($id, 'success', '2', 'Pesanan telah dipickup');
                $this->firebaseService->sendNotification($order->user->notification_token, 'Pesananmu telah di pickup', 'Kami telah mengambil pesananmu semoga sehat selalu', '');
                $this->createOrderStatus($id, 'pending', '3', 'Sedang diproses');
            }
            if($order->jenis_pemesanan == 'antar_mandiri')
            {
                $this->createOrderStatus($id, 'success', '2', 'Pesanan telah dikonfirmasi');
                $this->createOrderStatus($id, 'pending', '3', 'Sedang diproses');
            }
        }
        if($orderStatus == 3)
        {
            if ($order->jenis_pemesanan == 'antar_jemput') {
                $this->createOrderStatus($id, 'success', '3', 'Pesanan selesai diproses');
                $this->createOrderStatus($id, 'pending', '4', 'Menunggu Pembayaran');
            }
            if ($order->jenis_pemesanan == 'antar_mandiri') {
                $this->createOrderStatus($id, 'success', '3', 'Pesanan selesai diproses');
                if($order->payment_method == 'cashless') {
                    $this->createOrderStatus($id, 'pending', '4', 'Menunggu Pembayaran');
                }
                if($order->payment_method == 'cash') {
                    $this->createOrderStatus($id, 'pending', '4', 'Menunggu Pengambilan');
                }

            }
        }
        if ($orderStatus == 4) {
            if ($order->jenis_pemesanan == 'antar_jemput') {
                $this->createOrderStatus($id, 'success', '4', 'Pembayaran Berhasil');
                $this->createOrderStatus($id, 'pending', '5', 'Sedang diantar menuju alamat');
            }
            if ($order->jenis_pemesanan == 'antar_mandiri') {
                if ($order->payment_method == 'cashless') {
                    $this->createOrderStatus($id, 'success', '4', 'Pembayaran Berhasil');
                    $this->createOrderStatus($id, 'pending', '4', 'Menunggu Pengambilan');
                }
                if ($order->payment_method == 'cash') {
                    $this->createOrderStatus($id, 'success', '4', 'Mohon Sediakan Uang Pas');
                    $this->createOrderStatus($id, 'pending', '4', 'Menunggu Pengambilan');
                }
            }
        }
    }

}
