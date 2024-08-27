<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderChartMonthlyResource;
use App\Http\Resources\OrderChartWeeklyResource;
use App\Models\Order;
use App\Models\OrderChart;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class OrderChartController extends Controller
{
    public function updateChart()
    {
        $totalOrders = Order::whereDate('created_at', now()->format('Y-m-d'))->count();
        $orderChart = OrderChart::whereDate('created_at', now()->format('Y-m-d'))->first();
        if ($orderChart != null) {
            $orderChart->total_orders = $totalOrders;
            $orderChart->save();
        } else {
            OrderChart::create([
                'total_orders' => $totalOrders,
            ]);
        }
        return response([
            'status' => 'success',
            'message' => 'Order chart updated successfully',
        ]);
    }

    public function getDailyChart()
    {
        $orderChart = OrderChart::whereDate('created_at', now()->format('Y-m-d'))->first();
        if ($orderChart == null) {
            return response([
                'status' => 'failed',
                'message' => 'No order chart found',
            ]);
        }
        return response([
            'status' => 'success',
            'data' => $orderChart,
        ]);
    }

    public function getWeeklyChart()
    {
        $orderChart = OrderChart::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->get();
        if ($orderChart == null) {
            return response([
                'status' => 'failed',
                'message' => 'No order chart found',
            ]);
        }
        return response([
            'status' => 'success',
            'total_orders' => $orderChart->sum('total_orders'),
            'data' => OrderChartWeeklyResource::collection($orderChart),
        ]);
    }

    public function getMonthlyChart()
    {
        $orderChart = OrderChart::select(DB::raw('WEEK(created_at) - WEEK(DATE_SUB(created_at, INTERVAL DAYOFMONTH(created_at) - 1 DAY)) + 1 as week_number, COUNT(*) as count'))
        ->whereMonth('created_at', now()->format('m'))
            ->groupBy('week_number')
            ->get();
        if ($orderChart == null) {
            return response([
                'status' => 'failed',
                'message' => 'No order chart found',
            ]);
        }
        return response([
            'status' => 'success',
            'total_orders' => $orderChart->sum('count'),
            'data' => OrderChartMonthlyResource::collection($orderChart),
        ]);
    }
}
