<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequests;
use App\Http\Requests\RegisterRequest;
use App\Models\BannedUser;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;

class UserController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $request->validated();

        if (User::where('email', $request->email)->first() != null) {
            return response([
                'status' => 'failed',
                'message' => 'Email already exists',
            ], 409);
        }
        if (User::where('phone', $request->phone)->first() != null) {
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
            'notification_token' => $request->notification_token,
        ];
        $user = User::create($userdata);
        $token = $user->createToken('wash_it', ['user'])->accessToken;

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
        $bannedUser = BannedUser::where('user_id', $user->id)->first();
        if ($bannedUser != null) {
            return response([
                'status' => 'failed',
                'message' => 'User Banned',
                'reason' => $bannedUser->reason,
                'unbanned_at' => $bannedUser->unbanned_at,
            ], 401);
        }
        $token = $user->createToken('wash_it', ['user'])->accessToken;
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
        } elseif (User::where('email', $request->email)->first() != null) {
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
        } elseif (User::where('phone', $request->phone)->first() != null) {
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
        $request->validate(['image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:10240',
        ]);

        $user = User::where('email', auth()->user()->email)->first();
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

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();
        if ($user == null) {
            return response([
                'status' => 'failed',
                'message' => 'User not found',
            ], 404);
        }
        $otp = rand(100000, 999999);
        $otps = Otp::where('user_id', $user->id)->first();
        if ($otps != null) {
            return response([
                'status' => 'failed',
                'message' => 'Otp already sent. Try again after 5 minutes',
            ]);
        }
        Otp::create([
            "otp" => $otp,
            "user_id" => $user->id,
        ]);
        $description = 'Ini adalah kode verifiskasi anda untuk reset password akun anda di aplikasi wash it. Jangan berikan kode ini kepada siapapun. Kode berlaku selama 5 menit';
        Mail::send('email.mail', ['otp' => $otp, "description" => $description, 'username' => $user->username], function ($message) use ($user) {
            $message->to($user->email, $user->username)->subject('OTP Verification');
        });
        return response([
            'status' => 'success',
            'message' => 'OTP sent to your email, check your email address',
        ]);
    }

    public function verifyForgotPassword(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|min:6|max:6',
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();
        $otp = $request->otp;
        $otps = Otp::where('user_id', $user->id)->first();

        if ($otps == null) {
            return response([
                'status' => 'failed',
                'message' => 'OTP not found',
            ], 404);
        }
        if ($otp == $otps->otp) {
            $otps->delete();
            $token = $user->createToken('wash_it', ['user'])->accessToken;
            return response([
                'status' => 'success',
                'message' => 'OTP verified successfully, you can use token for reset password',
                'token' => $token,
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'OTP verification failed',
            ], 401);
        }
    }

    public function googleLogin(Request $request)
    {;
        try {
            $googleUser = Socialite::driver('google')->userFromToken($request->token);
        } catch (\Exception $e) {
            dd($e);
            return response([
                'status' => 'failed',
                'message' => 'Invalid credentials',
            ], 401);
        }
        $user = User::where('email', $googleUser->email)->first();

        if ($user == null) {
            return response([
                'status' => 'failed',
                'message' => 'Invalid credentials',
            ], 401);
        }

        if ($user->email_verified_at == null && $googleUser->user['email_verified'] == true) {
            $user->email_verified_at = now();
            $user->save();
        }

        if ($googleUser->avatar != null) {
            if ($user->image_path != null) {
                File::delete(storage_path(storage_path($user->image_path)));
            }
            $image = file_get_contents($googleUser->avatar);
            $imageName = time() . '.png';
            file_put_contents(storage_path('app/public/images/' . $imageName), $image);
            $user->image_path = $imageName;
            $user->save();
        }

        $token = $user->createToken('wash_it', ['user'])->accessToken;
        return response([
            'status' => 'success',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

}
