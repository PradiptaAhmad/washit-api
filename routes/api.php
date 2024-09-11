<?php

use App\Http\Controllers\AddressController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\LaundryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\BannedUserController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OrderStatusController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderChartController;
use App\Http\Controllers\TransactionChartController;
use App\Http\Controllers\TransactionController;
use App\Models\TransactionChart;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::group(['prefix' => 'users'], function () {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/google/login', [UserController::class, 'googleLogin']);
    Route::get('/google/callback', [UserController::class, 'googleCallback']);
    Route::get('/store/status', [UserController::class, 'storeStatus']);

    Route::group(['middleware' => ['auth:user', 'scope:user']], function () {
        Route::get('/me', [UserController::class, 'details']);
        Route::post('/update-profile-picture', [UserController::class, 'updateProfilePicture']);
        Route::post('/logout', [UserController::class, 'logout']);

        Route::group(['prefix' => 'update',], function () {
            Route::post('/username', [UserController::class, 'updateUsername']);
            Route::post('/email', [UserController::class, 'updateEmail']);
            Route::post('/phone', [UserController::class, 'updatePhone']);
            Route::post('/password', [UserController::class, 'updatePassword']);
            Route::post('/profile-picture', [UserController::class, 'updateProfilePicture']);
        });

        Route::group(['prefix' => 'otp',], function () {
            Route::post('/send/phone', [OtpController::class, 'sendOtp']);
            Route::post('/send/email', [OtpController::class, 'sendEmailOtp']);
            Route::post('/verify/phone', [OtpController::class, 'verifyOtp']);
            Route::post('/verify/email', [OtpController::class, 'verifyEmailOtp']);
        });
    });

    Route::group(['prefix' => '/forgot-password',], function () {
        Route::post('/checkEmail', [UserController::class, 'forgotPassword']);
        Route::post('/verify', [UserController::class, 'verifyForgotPassword']);
    });
});

Route::group(['prefix' => 'orders', 'middleware' => ['auth:user', 'scope:user']], function () {
    Route::post('/new', [OrderController::class, 'addOrder']);
    Route::get('/all', [OrderController::class, 'getOrder']);
    Route::post('/update', [OrderController::class, 'updateStatus']);
    Route::get('/detail', [OrderController::class, 'getOrderDetail']);
    Route::put('/complete', [OrderController::class, 'completeOrder']);
    Route::put('/cancel', [OrderController::class, 'cancelOrder']);
    Route::delete('/delete/{id}', [OrderController::class, 'deleteOrder']);

    Route::group(['prefix' => '/status'], function () {
        Route::get('/all', [OrderStatusController::class, 'getOrderStatus']);
        Route::get('/last', [OrderStatusController::class, 'getLastStatus']);
    });

    Route::prefix('/filter')->group(function () {
        Route::get('/date', [OrderController::class, 'filterOrderByDate']);
        Route::get('/status', [OrderController::class, 'filterOrderByStatus']);
        Route::get('/service', [OrderController::class, 'filterOrderByService']);
    });
});

Route::get('/laundry/all', [LaundryController::class, 'getLaundryServices'])->middleware(['auth:user', 'scope:user']);

Route::group(['prefix' => 'admin'], function () {
    Route::group(['prefix' => 'accounts'], function () {
        Route::post('/login', [AdminController::class, 'login']);
        Route::post('/register', [AdminController::class, 'register']);
        Route::put('/update', [AdminController::class, 'editAccount'])->middleware(['auth:admin', 'scope:admin']);
        Route::post('/update/profile-picture', [AdminController::class, 'updateProfilePicture'])->middleware(['auth:admin', 'scope:admin']);
        Route::get('/details', [AdminController::class, 'adminDetails'])->middleware(['auth:admin', 'scope:admin']);

        Route::delete('/logout', [AdminController::class, 'logout'])->middleware(['auth:admin', 'scope:admin']);
    });

    Route::middleware(['auth:admin', 'scope:admin'],)->group(function () {
        Route::get('/home/overview', [AdminController::class, 'homeOverview']);
        Route::put('/toggle/status', [AdminController::class, 'toggleStatus']);
        Route::group(['prefix' => '/laundry'], function () {
            Route::get('/all', [LaundryController::class, 'getLaundryServices']);
            Route::post('/add', [LaundryController::class, 'addLaundryServices']);
            Route::put('/update', [LaundryController::class, 'updateLaundry']);
            Route::delete('/delete/{id}', [LaundryController::class, 'deleteLaundryService']);
        });

        Route::group(['prefix' => 'users'], function () {
            Route::get('/all', [AdminController::class, 'getUser']);
            Route::get('/detail', [AdminController::class, 'getUserDetail']);
            Route::delete('/delete', [AdminController::class, 'deleteUser']);
            Route::post('/ban', [BannedUserController::class, 'banUser']);
            Route::delete('/unban/{id}', [BannedUserController::class, 'unBanUser']);
        });

        Route::group(['prefix' => 'orders'], function () {
            Route::get('/all', [OrderController::class, 'getAllOrders']);
            Route::get('/detail', [OrderController::class, 'getAdminDetailOrder']);
            Route::put('/cancel', [OrderController::class, 'cancelOrder']);
            Route::put('/update-weight', [OrderController::class, 'updateWeight']);
            Route::put('/status/update', [OrderStatusController::class, 'updateOrderStatus']);
            Route::delete('/status/delete/{id}', [OrderStatusController::class, 'deleteStatus']);

            Route::prefix('/filter')->group(function () {
                Route::get('/date', [OrderController::class, 'filterOrderByDate']);
                Route::get('/status', [OrderController::class, 'filterOrderByStatus']);
                Route::get('/service', [OrderController::class, 'filterOrderByService']);
                Route::get('/user', [OrderController::class, 'filterOrderByUser']);
            });
        });

        Route::group(['prefix' => 'orders/status'], function () {
            Route::get('/all', [OrderStatusController::class, 'getOrderStatus']);
            Route::get('/last', [OrderStatusController::class, 'getLastStatus']);
        });

        Route::group(['prefix' => 'ratings'], function () {
            Route::get('/all', [RatingController::class, 'getAllRatings']);
            Route::get('/summary', [RatingController::class, 'getRatingSummary']);
            Route::delete('/delete', [RatingController::class, 'deleteRating']);
        });

        Route::group(['prefix' => 'histories'], function () {
            Route::get('/all', [HistoryController::class, 'getAdminHistory']);
            Route::get('/detail', [HistoryController::class, 'getDetailAdminHistory']);

            Route::prefix('/filter')->group(function () {
                Route::get('/date', [HistoryController::class, 'filterHistoryByDate']);
                Route::get('/status', [HistoryController::class, 'filterHistoryByStatus']);
                Route::get('/service', [HistoryController::class, 'filterHistoryByService']);
                Route::get('/user', [HistoryController::class, 'filterHistoryByUser']);
            });
        });

        Route::group(['prefix' => 'transaction'], function () {
            Route::get('/all', [TransactionController::class, 'getAllTransaction']);
            Route::delete('/delete/{id}', [PaymentController::class, 'deleteTransaction']);
        });
    });
});

Route::group(['prefix' => '/histories', 'middleware' => ['auth:user', 'scope:user']], function () {
    Route::get('/all', [HistoryController::class, 'getHistory']);
    Route::get('/detail', [HistoryController::class, 'getHistoryDetail']);
    Route::get('/search', [HistoryController::class, 'searchHistory']);
    Route::get('/filter/date', [HistoryController::class, 'filterHistoryByDate']);
    Route::get('/filter/service', [HistoryController::class, 'filterByService']);
    Route::delete('/delete/{id}', [HistoryController::class, 'deleteHistory']);

    Route::prefix('/filter')->group(function () {
        Route::get('/date', [HistoryController::class, 'filterHistoryByDate']);
        Route::get('/status', [HistoryController::class, 'filterHistoryByStatus']);
        Route::get('/service', [HistoryController::class, 'filterHistoryByService']);
    });
});

Route::group(['prefix' => 'notifications', 'middleware' => ['auth:admin', 'scope:admin']], function () {
    Route::post('/send', [NotificationController::class, 'sendNotification']);
    Route::post('/send-to-all', [NotificationController::class, 'sendNotificationToAll']);
    Route::post('/send-to-admin', [NotificationController::class, 'sendNotificationToAdmin']);
    Route::get('/all', [NotificationController::class, 'getNotifications']);
});

Route::group(['prefix' => 'payments'], function () {
    Route::group(['middleware' => ['auth:user', 'scope:user']], function () {
        Route::post('/create', [PaymentController::class, 'createPayment']);
        Route::post('/update', [PaymentController::class, 'updatePaymentStatus']);
        Route::get('/get', [PaymentController::class, 'getInvoiceUser']);
        Route::delete('/expire', [PaymentController::class, 'expirePayment']);
    });

    Route::group(['prefix' => 'callback', 'middleware' => 'xendit-callback'], function () {
        Route::post('/invoice-status', [TransactionController::class, 'invoiceStatus']);
    });
});

Route::group(['prefix' => 'ratings', 'middleware' => ['auth:user', 'scope:user']], function () {
    Route::post('/add', [RatingController::class, 'addRating']);
    Route::get('/get', [RatingController::class, 'getRating']);

});

Route::group(['prefix' => 'addresses'], function () {
    Route::middleware(['auth:user', 'scope:user'])->group(function () {
        Route::post('/add', [AddressController::class, 'addAddress']);
        Route::get('/primary', [AddressController::class, 'getPrimaryAddress']);
        Route::get('/detail', [AddressController::class, 'getAddressDetail']);
        Route::get('/get', [AddressController::class, 'getAddressPerUser']);
        Route::put('/edit', [AddressController::class, 'editAddress']);
        Route::delete('/delete', [AddressController::class, 'deleteAddress']);
    });
    Route::group(['prefix' => 'get'], function () {
        Route::get('/search', [AddressController::class, 'getAddressByCode']);
        Route::get('/provinces', [AddressController::class, 'getProvince']);
        Route::get('/cities', [AddressController::class, 'getCityByProvince']);
        Route::get('/districts', [AddressController::class, 'getDistrictByCity']);
        Route::get('/villages', [AddressController::class, 'getVillageByDistrict']);
        Route::get('postal-code', [AddressController::class, 'getPostalCodeByVilage']);
    });
});

Route::group(['prefix' => 'charts', 'middleware' => ['auth:admin', 'scope:admin']], function () {
    Route::prefix('orders')->group(function () {
        Route::get('/daily', [OrderChartController::class, 'getDailyChart']);
        Route::get('/weekly', [OrderChartController::class, 'getWeeklyChart']);
        Route::get('/monthly', [OrderChartController::class, 'getMonthlyChart']);
    });
    Route::prefix('transactions')->group(function () {
        Route::get('/daily', [TransactionChartController::class, 'getDailyChart']);
        Route::get('/weekly', [TransactionChartController::class, 'getWeeklyChart']);
        Route::get('/monthly', [TransactionChartController::class, 'getMonthlyChart']);
    });
});

Route::group(['prefix' => 'message', 'middleware' => 'auth:user'], function () {
    Route::get('/get', [MessageController::class, 'loadMessage']);
    Route::post('/send', [MessageController::class, 'sendMessage']);
});
Route::group(['prefix' => 'transaction', 'middleware' => ['auth:user', 'scope:user']], function () {
    Route::get('/get', [TransactionController::class, 'getTransaction']);
});

Route::post('/test', [HistoryController::class, 'migrateToHistories']);
