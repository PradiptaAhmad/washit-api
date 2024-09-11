<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function loadMessage(Request $request)
    {
        $user = $request->user();
        if ($request->user()->tokenCan('admin')) {
            $messages = Message::where('from_user_id', $request->user_id)->orWhere('to_user_id', $request->user_id)->orderBy('created_at', 'asc')->paginate(20);
        } else {
            $messages = Message::where('from_user_id', $user->id)->orWhere('to_user_id', $user->id)->orderBy('created_at', 'asc')->paginate(20);
        }
        return response([
            'status' => 'success',
            'message' => 'Messages loaded successfully',
            'data' => $messages->items()
        ]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);
        $user = $request->user();
        if ($user->tokenCan('admin')) {
            $message = Message::create([
                'id' => Str::uuid(),
                'message' => $request->message,
                'from_user_id' => 'admin',
                'to_user_id' => $request->user_id
            ]);
        } else {
            $message = Message::create([
                'id' => Str::uuid(),
                'message' => $request->message,
                'from_user_id' => $user->id,
                'to_user_id' => 'admin'
            ]);
        }
        broadcast(new MessageSent($message));
        return response([
            'status' => 'success',
            'data' => $message
        ], 201);
    }
}
