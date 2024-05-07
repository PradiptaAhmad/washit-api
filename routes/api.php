<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\LaundryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OtpController;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::group(['prefix' => 'users'], function () {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::get('/me', [UserController::class, 'details'])->middleware('auth:sanctum');
    Route::post('/update-profile-picture', [UserController::class, 'updateProfilePicture'])->middleware('auth:sanctum');
    Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('/send-otp', [OtpController::class, 'sendOtp'])->middleware('auth:sanctum');
    Route::post('/verify-otp', [OtpController::class, 'verifyOtp'])->middleware('auth:sanctum');
    
    Route::group(['prefix' => 'update', 'middleware' => 'auth:sanctum'], function () {
        Route::post('/username', [UserController::class, 'updateUsername']);
        Route::post('/email', [UserController::class, 'updateEmail']);
        Route::post('/phone', [UserController::class, 'updatePhone']);
        Route::post('/password', [UserController::class, 'updatePassword']);
        Route::post('/profile-picture', [UserController::class, 'updateProfilePicture']);
    });
});

Route::group(['prefix' => 'orders', 'middleware' => 'auth:sanctum'], function () {
    Route::post('/new', [OrderController::class, 'addOrder']);
    Route::get('/all', [OrderController::class, 'getOrder']);
    Route::delete('/delete/{id}', [OrderController::class, 'deleteOrder']);
});

Route::group(['prefix' => 'admin'], function () {
    Route::group(['prefix' => 'laundry', 'middleware' => 'auth:sanctum'], function () {
        Route::post('/add', [LaundryController::class, 'addLaundryServices']);
        Route::post('/update-price', [LaundryController::class, 'updatePrice']);
        Route::delete('/delete/{id}', [LaundryController::class, 'deleteLaundryService']);
        Route::get('/all', [LaundryController::class, 'getLaundryServices']);
    });
});