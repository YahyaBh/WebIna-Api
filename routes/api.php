<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Authentication\UserController;
use App\Http\Controllers\Client\Order\OrderController;
use App\Http\Controllers\Client\Store\StoreController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;


//Home page routes 
Route::get('/home', [HomeController::class, 'home_ret']);
Route::post('/contact', [HomeController::class, 'contactSend']);
Route::post('/register/email', [HomeController::class, 'registerEmail']);


//User regsitration routes
Route::post('/register', [UserController::class, 'createUser']);
Route::post('/login', [UserController::class, 'loginUser']);


Route::post('/register/verification/email', [UserController::class, 'verifyEmail']);
Route::post('/register/check-verification', [UserController::class, 'checkVerification']);


//Mobile routes
Route::post('/mobile/signup/', [MobileController::class, 'register']);


//Admin registration routes
Route::post('/admin/login', [AdminUserController::class, 'loginUser']);
Route::post('/admin/register', [AdminUserController::class, 'register']);

Route::post('/admin/register/verification/email', [AdminUserController::class, 'verifyEmail']);
Route::post('/admin/register/check-verification', [AdminUserController::class, 'checkVerification']);





Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    // Protected routes that require authentication

    Route::post('/logout', [UserController::class, 'destroy']);

    Route::post('/store', [StoreController::class, 'index']);

    Route::post('/store/home', [StoreController::class, 'index']);

    Route::get('/order/create', [OrderController::class, 'order_create']);

    Route::post('/order/{id}', [OrderController::class, 'order_track']);



    //Admin routes that require admin authentication



    Route::group(['middleware' => ['admin']], function () {
        Route::post('/admin/dashboard', [AdminUserController::class, 'index']);

        Route::post('/admin/orders', [AdminUserController::class, 'orders']);

        Route::post('/admin/logout', [AdminUserController::class, 'destroy']);

        Route::post('/admin/home/edit', [AdminUserController::class, 'editHome']);

        Route::post('/admin/home/project', [AdminUserController::class, 'projectHome']);

        Route::post('/admin/home/testemonials', [AdminUserController::class, 'testimonialsHome']);
    });
});
