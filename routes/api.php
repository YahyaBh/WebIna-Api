<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Authentication\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Mobile\MobileController;
use App\Http\Controllers\Store\StoreController;
use Illuminate\Support\Facades\Route;


//Home page routes 
Route::get('/home', [HomeController::class, 'home_ret']);
Route::post('/contact', [HomeController::class, 'contactSend']);
Route::post('/register/email', [HomeController::class, 'registerEmail']);


//User regsitration routes
Route::post('/register', [UserController::class, 'createUser']);
Route::post('/login', [UserController::class, 'loginUser']);



//Mobile routes
Route::post('/mobile/signup/', [MobileController::class, 'register']);


//Admin registration routes
Route::post('/admin/login', [AdminUserController::class, 'login']);
Route::post('/admin/register', [AdminUserController::class, 'register']);
Route::post('/admin/logout', [AdminUserController::class, 'destroy']);

Route::middleware('auth:sanctum')->group(function () {
    // Protected routes that require authentication

    Route::post('/logout', [UserController::class, 'destroy']);

    Route::post('/store', [StoreController::class, 'index']);

    Route::post('/store/home', [StoreController::class, 'index']);





    //Admin routes that require admin authentication
    Route::group(['middleware' => ['admin']], function () {

        Route::post('/dashboard', [AdminUserController::class, 'index']);
    });
});
