<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Twilio\Rest\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OtpController extends Controller
{
    public function sendOtp()
    {
        $user = Auth()->user();
        $phone = "+62" . substr($user->phone, 1);
        $otp = rand(100000, 999999);
        $otps = Otp::where('user_id', $user->id)->first();

        if ($otps != null) {
            return response([
                "status" => "failed",
                'message' => "Try Again After 5 Minutes"
            ]);
        } else if ($user->phone_verified_at != null) {
            return response([
                "status" => "failed",
                'message' => "Phone Number Verified"
            ]);
        }
        Http::post('https://wapiiiiiii-957b9f860ed5.herokuapp.com/message/', [
            'phoneNumber' => $phone,
            'message' => "Halo " . $user->username . ", " . $otp . " adalah kode OTP Anda. Demi Keamanan jangan berikan kode ini kepada siapapun.",
        ]);
        Otp::create([
            "otp" => $otp,
            "user_id" => $user->id,
        ]);

        return response([
            "status" => "success",
            'message' => 'OTP sent successfully',
        ], 200);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|min:6|max:6',
        ]);

        $user = User::where('id', Auth()->user()->id)->first();
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
            $user->phone_verified_at = Carbon::now();
            $user->save();
            return response([
                'status' => 'success',
                'message' => 'OTP verified successfully',
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'OTP verification failed',
            ], 401);
        }
    }

    public function verifyEmailOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|min:6|max:6',
        ]);

        $user = User::where('id', Auth()->user()->id)->first();
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
            $user->email_verified_at = Carbon::now();
            $user->save();
            return response([
                'status' => 'success',
                'message' => 'OTP verified successfully',
            ], 200);
        } else {
            return response([
                'status' => 'failed',
                'message' => 'OTP verification failed',
            ], 401);
        }
    }

    public function sendEmailOtp(Request $request)
    {
        $user = Auth()->user();
        $otp = rand(100000, 999999);
        $otps = Otp::where('user_id', $user->id)->first();
        $description = 'Ini adalah kode verifiskasi anda untuk aktivasi akun anda di aplikasi wash it. Jangan berikan kode ini kepada siapapun. Kode berlaku selama 5 menit';
        Mail::send('email.mail', ['otp' => $otp, "description" => $description, 'username' => $user->username], function ($message) use ($user) {
            $message->to($user->email, $user->username)->subject('OTP Verification');
        });

        if ($user->email_verified_at != null) {
            return response([
                "status" => "failed",
                'message' => "Email Address Verified"
            ]);
        }

        Otp::create([
            "otp" => $otp,
            "user_id" => $user->id,
        ]);

        return response([
            "status" => "success",
            'message' => 'OTP sent successfully',
        ], 200);
    }
}
