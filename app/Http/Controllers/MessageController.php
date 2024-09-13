<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Events\MessageSent;
use App\Events\MessageSentAdmin;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function loadMessage(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'after' => 'nullable|exists:messages,id',
        ]);
        $user = $request->user();

        if ($request->user()->tokenCan('admin')) {
            if (isset($request->after)) {
                $message = Message::where('id', $request->after)->first();
                $messages = Message::where('to_user_id', $request->user_id)->where('created_at', '<', $message->created_at)->orderBy('created_at', 'desc')->limit(20)->get();
            } else {
                $messages = Message::where('to_user_id', $request->user_id)->orderBy('created_at', 'desc')->paginate(20);
            }
        } else {
            if (isset($request->after)) {
                $message = Message::where('id', $request->after)->first();
                $messages = Message::where('from_user_id', $user->id)->where('created_at', '<', $message->created_at)->orderBy('created_at', 'desc')->limit(20)->get();
            } else {
                $messages = Message::where('from_user_id', $user->id)->orderBy('created_at', 'desc')->paginate(20);
            }
        }
        return response([
            'status' => 'success',
            'message' => 'Messages loaded successfully',
            'data' => isset($request->after) ? $messages : $messages->items()
        ]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'order_id' => 'nullable|exists:orders,id',
        ]);
        $user = $request->user();
        if ($user->tokenCan('admin')) {
            $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);
            $message = Message::create([
                'id' => Str::uuid(),
                'message' => $request->message,
                'from_user_id' => 'admin',
                'to_user_id' => $request->user_id
            ]);
            broadcast(new MessageSent($message));
        } else {
            $message = Message::create([
                'id' => Str::uuid(),
                'message' => $request->message,
                'from_user_id' => $user->id,
                'to_user_id' => 'admin'
            ]);
            broadcast(new MessageSentAdmin($message));
        }
        return response([
            'status' => 'success',
            'message' => 'Message Successfully Sent',
            'data' => $message
        ], 201);
    }
}
