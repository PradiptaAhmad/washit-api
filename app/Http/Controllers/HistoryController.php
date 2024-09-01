<?php

namespace App\Http\Controllers;

use App\Http\Resources\AdminHistoryDetailResource;
use App\Http\Resources\HistoryDetailResource;
use App\Http\Resources\HistoryResource;
use App\Models\History;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        return response([
            'status' => 'success',
            'message' => 'History fetched successfully',
            'data' => HistoryResource::collection($histories),
        ]);
    }

    public function getHistoryDetail(Request $request)
    {
        $request->validate([
            'history_id' => 'required|integer|exists:histories,id',
        ]);
        $user = auth()->user();
        $history = History::where('id', $request->history_id)->first();

        if ($history == null) {
            return response([
                'status' => 'failed',
                'message' => 'No history found',
            ]);
        }
        return response([
            'status' => 'success',
            'data' => new HistoryDetailResource($history),
        ]);
    }

    public function getAdminHistory()
    {
        $history = History::paginate(15);
        return response([
            'status' => 'success',
            'data' => HistoryResource::collection($history),
        ]);
    }

    public function getDetailAdminHistory(Request $request)
    {
        $request->validate([
            'history_id' => 'required|integer|exists:histories,id'
        ]);

        $history = History::where('id', $request->history_id)->first();
        return response([
            'status' => 'success',
            'data' => new AdminHistoryDetailResource($history),
        ]);
    }

    public function filterHistoryByDate(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'date' => 'required|date',
        ]);
        if ($user->tokenCan('admin')) {
            $histories = History::whereDate('created_at', $request->date)->paginate(15);
        } else {
            $histories = History::where('user_id', $user->id)->whereDate('created_at', $request->date)->paginate(15);
        }
        return response([
            'status' => 'success',
            'message' => 'History fetched successfully',
            'data' => HistoryResource::collection($histories),
        ]);
    }

    public function searchHistory(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'q' => 'required|string',
        ]);
        $query = $request->q;
        $result = [];
        $collums = [
            'no_pemesanan',
            'jenis_pemesanan',
            'nama_pemesan',
            'nomor_telepon',
            'alamat',
            'metode_pembayaran',
            'berat_laundry',
            'total_harga',
            'status',
            'catatan',
            'laundry_service',
            'laundry_description',
            'laundry_price',
        ];
        foreach ($collums as $collum) {
            $histories = History::where('user_id', $user->id)
                ->where($collum, 'like', '%' . $query . '%')
                ->get();
            if (!$histories == null) {
                $result = array_merge($result, $histories->toArray());
            }
        }
        return response([
            'status' => 'success',
            'message' => 'History fetched successfully',
            'data' => $result,
        ]);
    }

    public function filterHistoryByService(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'service' => 'required|in:' . implode(',', ['antar_jemput', 'antar_mandiri']),
        ]);
        if ($user->tokenCan('admin')) {
            $histories = History::where('jenis_pemesanan', $request->service)->paginate(15);
        } else {
            $histories = History::where('user_id', $user->id)->where('jenis_pemesanan', $request->service)->paginate(15);
        }
        return response([
            'status' => 'success',
            'message' => 'History fetched successfully',
            'data' => HistoryResource::collection($histories),
        ]);
    }

    public function filterHistoryByStatus(Request $request)
    {
        $user = $request->user();
        $request->validate([
            'status' => 'required|in:' . implode(',', ['completed', 'canceled']),
        ]);
        if ($user->tokenCan('admin')) {
            $histories = History::where('status', $request->status)->paginate(15);
        } else {
            $histories = History::where('user_id', $user->id)->where('status', $request->status)->paginate(15);
        }
        return response([
            'status' => 'success',
            'message' => 'History fetched successfully',
            'data' => HistoryResource::collection($histories),
        ]);
    }
}
