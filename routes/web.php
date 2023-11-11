<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return view('home.index');
});



// require __DIR__ . '/auth.php';
