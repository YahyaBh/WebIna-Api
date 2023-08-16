<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Authentication\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Store\StoreController;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return response()->json(([
        'message' => 'Authentication'
    ]));
});


//Home page routes 
Route::get('/home', [HomeController::class, 'home_ret']);
Route::post('/contact', [HomeController::class, 'contactSend']);
Route::post('/register/email', [HomeController::class, 'registerEmail']);


//User regsitration routes
Route::post('/register', [UserController::class, 'createUser']);
Route::post('/login', [UserController::class, 'loginUser']);







Route::middleware('auth:sanctum')->group(function () {
    // Protected routes that require authentication

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);

    Route::post('/store', [StoreController::class, 'index']);
});
