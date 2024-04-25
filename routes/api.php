<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\LaundryController;
use Illuminate\Http\Request;
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

    Route::group(['prefix' => 'update', 'middleware' => 'auth:sanctum'], function () {
        Route::post('/username', [UserController::class, 'updateUsername']);
        Route::post('/email', [UserController::class, 'updateEmail']);
        Route::post('/phone', [UserController::class, 'updatePhone']);
        Route::post('/password', [UserController::class, 'updatePassword']);
        Route::post('/profile-picture', [UserController::class, 'updateProfilePicture']);
    });
});

Route::group(['prefix' => 'laundry', 'middleware' => 'auth:sanctum'], function () {
    Route::post('/add', [LaundryController::class, 'addLaundryServices']);
    Route::post('/update-price', [LaundryController::class, 'updatePrice']);
    Route::post('/delete', [LaundryController::class, 'deleteLaundryService']);
    Route::get('/all', [LaundryController::class, 'getLaundryServices']);
});
