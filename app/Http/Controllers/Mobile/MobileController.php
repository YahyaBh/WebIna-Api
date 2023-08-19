<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MobileController extends Controller
{
    




    public function register(Request $request) {


        $request->validate([
            'username' => 'required',
            'email' => 'required|email',
            'password' => 'required|password',
        ]);



        dd($request);



    }

}
