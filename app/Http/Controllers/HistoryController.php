<?php

namespace App\Http\Controllers;

use App\Http\Resources\AdminHistoryDetailResource;
use App\Http\Resources\HistoryDetailResource;
use App\Http\Resources\HistoryResource;
use App\Models\History;
use App\Models\Order;
use Illuminate\Http\Request;

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
        $history = History::all();
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
        $histories = History::where('user_id', $user->id)
            ->whereDate('created_at', $request->date)
            ->get();
        return response([
            'status' => 'success',
            'message' => 'History fetched successfully',
            'data' => HistoryResource::collection($histories),
        ]);
    }
}
