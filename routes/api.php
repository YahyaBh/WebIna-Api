<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return Redirect::to('https://webinadigital.com');;
});

Route::get('/testimonials', [HomeController::class, 'testimonials']);
Route::post('/contact' , [HomeController::class, 'contactSend']);
Route::post('/register/email' , [HomeController::class, 'registerEmail']);



Route::middleware('auth:sanctum')->group(function () {
    // Protected routes that require authentication
});
