<?php

namespace App\Http\Controllers;

use App\Http\Requests\BanUserRequest;
use App\Http\Requests\LoginRequests;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function login(LoginRequests $request)
    {
        $request->validated();

        $user = User::where('email', $request->email)->first();
        if ($user == null | !Hash::check($request->password, $user->password)) {
            return response([
                'message' => 'Invalid credentials',
            ], 401);
        } else if ($user->role != 'admin') {
            return response([
                'message' => 'You are not authorized',
            ], 401);
        }
        $token = $user->createToken('wash_it')->plainTextToken;
        return response([
            'user' => $user,
            'token' => $token,
        ], 200);
    }
    public function logout()
    {
        $user = User::where('email', auth()->user()->email)->first();
        return response([
            'message' => 'Logged out',
        ], 200);
    }

    public function getUser()
    {
        $user = Auth()->user();
        if ($user->role != 'admin') {
            return response([
                'message' => 'You are not authorized',
            ], 401);
        }
        $user = User::where('role', 'user')->get();
        return response([
            'message' => 'User fetched succes',
            'user' => $user,
        ], 200);
    }

    public function banUser(BanUserRequest $request)
    {
        $request->validated();
        $user = User::where('id', $request->id)->first();
        if ($user == null) {
            return response([
                'message' => 'User not found',
            ], 404);
        }
        $user->delete();
        return response([
            'message' => 'User deleted successfully',
        ], 200);
    }
}
