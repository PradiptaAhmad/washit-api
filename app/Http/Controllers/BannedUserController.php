<?php

namespace App\Http\Controllers;

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

    


}
