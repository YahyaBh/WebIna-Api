<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Authentication\UserController;
use App\Http\Controllers\Client\Store\Cart;
use App\Http\Controllers\Client\Store\StoreController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SocialAuthController;
use Illuminate\Support\Facades\Route;


//Home page routes 
Route::get('/home', [HomeController::class, 'home_ret']);
Route::post('/contact', [HomeController::class, 'contactSend']);
Route::post('/register/email', [HomeController::class, 'registerEmail']);


//User regsitration routes
Route::post('/register', [UserController::class, 'createUser']);
Route::post('/login', [UserController::class, 'loginUser']);
Route::post('/forget-password' , [UserController::class , 'forgetPassword']);


Route::post('/auth/{provider}', [SocialAuthController::class, 'redirectToProvider']);
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback']);


Route::delete('/auth/{provider}/delete/account', [SocialAuthController::class, 'handleUserDeletion']);



Route::post('/register/verification/email', [UserController::class, 'verifyEmail']);
Route::post('/register/check-verification', [UserController::class, 'checkVerification']);


//User Store routes


Route::post('/store', [StoreController::class, 'index']);
Route::post('/store/product', [StoreController::class, 'product']);



//Mobile routes
Route::post('/mobile/signup/', [MobileController::class, 'register']);


//Admin registration routes
Route::post('/admin/login', [AdminUserController::class, 'loginUser']);
Route::post('/admin/register', [AdminUserController::class, 'register']);

Route::post('/admin/register/verification/email', [AdminUserController::class, 'verifyEmail']);
Route::post('/admin/register/check-verification', [AdminUserController::class, 'checkVerification']);

Route::post('/cart/add' , [Cart::class, 'add_to_cart']);



Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    // Protected routes that require authentication

    Route::post('/logout', [UserController::class, 'destroy']);



    //Admin routes that require admin authentication



    Route::group(['middleware' => ['admin']], function () {
        Route::post('/admin/dashboard', [AdminUserController::class, 'index']);

        Route::post('/admin/orders', [AdminUserController::class, 'orders']);

        Route::post('/admin/logout', [AdminUserController::class, 'destroy']);

        Route::post('/admin/home/edit', [AdminUserController::class, 'editHome']);

        Route::post('/admin/home/project', [AdminUserController::class, 'projectHome']);

        Route::post('/admin/home/testemonials', [AdminUserController::class, 'testimonialsHome']);

        Route::post('/admin/product/create', [AdminUserController::class, 'createProduct']);
    });
});
