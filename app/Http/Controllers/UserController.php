<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequests;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $request->validated();

        if ($request->email == User::where('email', $request->email)->first()) {
            return response([
                'message' => 'Email already exists',
            ], 409);
        } elseif ($request->phone == User::where('phone', $request->phone)->first()) {
            return response([
                'message' => 'Phone already exists',
            ], 409);
        }

        $userdata = [
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ];
        $user = User::create($userdata);
        $token = $user->createToken('wash_it')->plainTextToken;

        return response([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login (LoginRequests $request) {
        $request->validated();

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $user->createToken('wash_it')->plainTextToken;

        return response([
            'user' => $user,
            'token' => $token,
        ], 200);
    }
}
