<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequests;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $request->validated();

        if ($request->email == User::where('email', $request->email)->first()) {
            return response([
                'status' => 'failed',
                'message' => 'Email already exists',
            ], 409);
        } elseif ($request->phone == User::where('phone', $request->phone)->first()) {
            return response([
                'status' => 'failed',
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
                'status' => 'failed',
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $user->createToken('wash_it')->plainTextToken;
        $user->notification_token = $request->notification_token;
        $user->save();

        return response([
            'status' => 'success',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    public function details() {
        return response([
            'user' => auth()->user(),
        ], 200);
    }

    public function logout() {
        $user = User::where('email', auth()->user()->email)->first();
        $user->tokens()->delete();
        return response([
            'message' => 'Logged out',
        ], 200);
    }

    // Update Profile

    public function updateUsername(Request $request) {
        $request->validate([
            'username' => 'required|string|max:255',
        ]);

        $user = User::where('email', auth()->user()->email)->first();
        if ($request->username == $user->username) {
            return response([
                'status' => 'failed',
                'message' => 'Username Cannot be the same as the previous one',
            ], 409);
        }
        $user->username = $request->username;
        $user->save();

        return response([
            'status' => 'success',
            'message' => 'Username updated',
            'user' => $user,
        ], 200);
    }

    public function updateEmail(Request $request) {
        $request->validate([
            'email' => 'required|string|email|max:255|unique:users',
        ]);

        $user = User::where('email', auth()->user()->email)->first();
        if ($request->email == $user->email) {
            return response([
                'status' => 'failed',
                'message' => 'Email Cannot be the same as the previous one',
            ], 409);
        } elseif ($request->email == User::where('email', $request->email)->first()) {
            return response([
                'status' => 'failed',
                'message' => 'Email already exists',
            ], 409);
        }
        $user->email = $request->email;
        $user->save();

        return response([
            'status' => 'success',
            'message' => 'Email updated',
            'user' => $user,
        ], 200);
    }

    public function updatePhone(Request $request) {
        $request->validate([
            'phone' => 'required|string|max:255|unique:users',
        ]);

        $user = User::where('email', auth()->user()->email)->first();
        if ($request->phone == $user->phone) {
            return response([
                'status' => 'failed',
                'message' => 'Phone Cannot be the same as the previous one',
            ], 409);
        } elseif ($request->phone == User::where('phone', $request->phone)->first()) {
            return response([
                'status' => 'failed',
                'message' => 'Phone already exists',
            ], 409);
        }
        $user->phone = $request->phone;
        $user->save();

        return response([
            'status' => 'success',
            'message' => 'Phone updated',
            'user' => $user,
        ], 200);
    }

    public function updatePassword(Request $request) {
        $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $user = User::where('email', auth()->user()->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        return response([
            'status' => 'success',
            'message' => 'Password updated',
            'user' => $user,
        ], 200);
    }

    public function updateProfilePicture(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $user = User::where('email', auth()->user()->email)->first();
        $image = $request->file('image');
        $imageName = time() . '.' . $image->extension();
        $image->move(public_path('images'), $imageName);
        $user->image_path = $imageName;
        $user->save();

        return response([
            'status' => 'success',
            'message' => 'Profile Picture updated',
            'user' => $user,
        ], 200);
    }

}
