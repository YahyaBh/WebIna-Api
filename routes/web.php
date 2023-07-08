<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return Redirect::to('https://webinadigital.com');;
});



require __DIR__ . '/auth.php';
