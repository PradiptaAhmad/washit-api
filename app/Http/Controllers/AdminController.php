<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Admin;
use App\Models\Order;
use App\Http\Requests\LoginRequests;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\BanUserRequest;
use App\Http\Requests\EditAdminRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Address;
use App\Models\Rating;
use App\Models\Transaction;
use App\Models\TransactionHistory;
use Illuminate\Http\Request;

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

    public function editAccount(EditAdminRequest $request)
    {
        $admin = $request->user();
        $request->validated();
        $admin->update($request->all());
        return response(
            [
                'status' => 'success',
                'message' => 'Account updated successfully',
                'admin' => $admin,
            ],
            200
        );
    }

    public function updateProfilePicture(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        $user = $request->user();
        $image = $request->file('image');
        $imageName = time() . '.' . $image->extension();
        $image->move(storage_path('/app/public/images/'), $imageName);
        $user->image_path = $imageName;
        $user->save();

        return response([
            'status' => 'success',
            'message' => 'Profile Picture updated',
            'user' => $user,
        ], 200);
    }

    public function toggleStatus()
    {
        $admin = Auth::guard('admin')->user();
        if ($admin->status == 'online') {
            $admin->status = 'offline';
            $admin->save();
            return response(
                [
                    'status' => 'success',
                    'message' => 'Admin is now offline',
                    'admin' => $admin,
                ],
                200
            );
        } else {
            $admin->status = 'online';
            $admin->save();
            return response(
                [
                    'status' => 'success',
                    'message' => 'Admin is now online',
                    'admin' => $admin,
                ],
                200
            );
        }
    }

    public function homeOverview()
    {
        $totalOrder = Order::count();
        $totalUser = User::count();
        $totalTransaction = Transaction::sum('amount') + TransactionHistory::sum('amount');
        $avgRating = round(Rating::avg('rating'), 1);

        return response([
            'status' => 'success',
            'message' => 'Overview fetched successfully',
            'total_orders' => $totalOrder,
            'total_users' => $totalUser,
            'total_transactions' => $totalTransaction,
            'average_ratings' => $avgRating,
        ], 200);
    }

    public function getUserDetail(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:users,id',
        ]);
        $user = User::where('id', $request->id)->first();
        $address = Address::where('user_id', $request->id)->where('is_primary', true)->first();
        $orderCount = Order::where('user_id', $request->id)->count();
        $response = [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'phone' => $user->phone,
            'image_path' => $user->image_path,
            'phone_verified_at' => $user->phone_verified_at,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'address' => $address != null ? $address->street . ', Kel. ' . $address->village . ', Kec. ' . $address->district . ', ' . $address->city . ', ' . $address->province . ', ' . $address->postal_code : null,
            'order_count' => $orderCount,
        ];
        return response([
            'status' => 'success',
            'message' => 'Get user successfully',
            'data' => $response,
        ], 200);
    }

    public function deleteUser(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:users,id',
        ]);
        $user = User::where('id', $request->id)->first();
        $user->delete();
        return response([
            'status' => 'success',
            'message' => 'User deleted successfully',
        ], 200);
    }


}
