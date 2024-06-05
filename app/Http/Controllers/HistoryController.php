<?php

namespace App\Http\Controllers;

use App\Http\Resources\HistoryResource;
use App\Models\History;
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
        return new HistoryResource($histories);
    }

    // public function addHistory()
}
