<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LaundryController;
use App\Http\Controllers\NotificationController;
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
    Route::group(['prefix' => 'accounts'], function () {
        Route::post('/login', [AdminController::class, 'login']);
        Route::post('/register', [AdminController::class, 'register']);
        Route::post('/logout', [AdminController::class, 'logout']);

    });

    Route::group(['prefix' => 'order', 'middleware' => 'auth:sanctum'], function () {
        Route::post('/accept', [OrderController::class, 'acceptOrder']);
        Route::post('/register', [AdminController::class, 'register']);
        Route::post('/logout', [AdminController::class, 'logout']);
    });

    Route::group(['prefix' => 'laundry', 'middleware' => 'auth:sanctum'], function () {
        Route::post('/add', [LaundryController::class, 'addLaundryServices']);
        Route::post('/update-price', [LaundryController::class, 'updatePrice']);
        Route::delete('/delete/{id}', [LaundryController::class, 'deleteLaundryService']);
        Route::get('/all', [LaundryController::class, 'getLaundryServices']);
    });

    Route::group(['prefix' => 'user', 'middleware' => 'auth:sanctum'], function () {
        Route::get('/all', [AdminController::class, 'getUser']);
        Route::post('/ban', [AdminController::class, 'banUser']);
    });
});

Route::group(['prefix' => '/notification', 'middleware' => 'auth:sanctum'], function () {
    Route::post('/send', [NotificationController::class, 'sendNotification']);
    Route::post('/send-to-all', [NotificationController::class, 'sendNotificationToAll']);
});
