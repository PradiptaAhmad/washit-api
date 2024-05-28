<?php

namespace App\Http\Controllers;

use App\Http\Requests\BanUserRequest;
use App\Http\Requests\UnbanRequest;
use App\Models\BannedUser;
use App\Models\User;
use Illuminate\Http\Request;

class BannedUserController extends Controller
{
    public function unbanUser(UnbanRequest $request) {
       $admin = Auth()->user();
       if ($admin->role == 'user') {
        return response(['status' => 'failed',
            'message' => "You are not authorized"
        ], 301);
       }

       $bannedUser = BannedUser::where('user_id', $request->id )->first();
       if ($bannedUser == null) {
        return response([
            'status' => 'failed',
            'message' => 'User Not Found'
        ], 301);
       }

       $bannedUser->delete();
       return response([
        'status' => 'success',
        'message' => 'User Unbanned Successfully'
       ]);
    }

    public function banUser(BanUserRequest $request)
    {
        $request->validated();
        $user = User::where('id', $request->user_id)->first();
        if ($user == null) {
            return response([
                'status' => 'failed',
                'message' => 'User not found',
            ], 404);
        }
        $banned = BannedUser::create([
            'user_id' => $request->user_id,
            'reason' => $request->reason,
            'description' => $request->description,
            'unbanned_at' => $request->unbanned_at
        ]);
        return response([
            'status' => 'success',
            'message' => 'User banned successfully',

        ], 200);
    }



}
