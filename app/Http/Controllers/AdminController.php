<?php

namespace App\Http\Controllers;

use App\Http\Requests\BanUserRequest;
use App\Http\Requests\LoginRequests;
use App\Http\Requests\RegisterRequest;
use App\Models\Admin;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function login(LoginRequests $request)
    {
        $request->validated();

        $user = Admin::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'status' => 'failed',
                'message' => 'Invalid credentials',
            ], 401);
        }
        $token = $user->createToken('admin_washit', ['admin'])->accessToken;
        return response([
            'user' => $user,
            'token' => $token,
        ], 200);
    }
    public function register(RegisterRequest $request)
    {
        $request->validated();
        $user = Admin::where('email', $request->email)->first();
        if ($user != null) {
            return response([
                'message' => 'Email already exists',
            ], 409);
        }
        $user = Admin::where('phone', $request->phone)->first();
        if ($user != null) {
            return response([
                'message' => 'Phone already exists',
            ], 409);
        }
        $userdata = [
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'notification_token' => $request->notification_token,
        ];
        $user = Admin::create($userdata);
        $token = $user->createToken('admin_washit', ['admin'])->accessToken;
        return response([
            'admin' => $user,
            'token' => $token,
        ], 201);
    }

    public function adminDetails()
    {
        $admin = auth()->user();
        return response([
            'status' => 'success',
            'admin' => $admin,
        ], 200);
    }

    public function logout()
    {
        $user = Admin::where('email', auth()->user()->email)->first();
        $user->tokens()->delete();
        return response([
            'message' => 'Logged out',
        ], 200);
    }

    public function getUser()
    {
        $user = User::all();
        return response(['status' => 'success',
            'message' => 'User fetched successfully',
            'user' => $user,
        ], 200);
    }

    public function getOrder()
    {
        $admin = Auth::guard('admin')->user();
        $order = Order::all();
        return response(
            [
                'status' => 'success',
                'message' => 'Order fetched by ' . $admin->username,
                'order' => $order,
            ],
            200
        );
    }


}
