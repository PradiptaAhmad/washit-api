<?php

namespace App\Http\Controllers;

use App\Models\OrderStatus;
use Illuminate\Http\Request;
use App\Services\OrderStatusService;

class OrderStatusController extends Controller
{
    protected $orderStatusService;

    public function __construct(OrderStatusService $orderStatusService)
    {
        $this->orderStatusService = $orderStatusService;
    }
    public function getOrderStatus(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
        ]);
        $orderStatus = OrderStatus::where('order_id', $request->order_id)->get();
        return response([
            'message' => 'Get all order status',
            'order_status' => $orderStatus,
        ], 200);
    }

    public function getLastStatus(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
        ]);
        $orderStatus = OrderStatus::where('order_id', $request->order_id)->latest()->first();
        return response([
            'message' => 'Get last order status',
            'order_status' => $orderStatus,
        ], 200);
    }

    public function updateOrderStatus(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
        ]);
        $this->orderStatusService->updateStatus($request->order_id);
        return response([
            'status' => 'success',
            'message' => 'Order status updated successfully',
        ], 201);
    }
}
