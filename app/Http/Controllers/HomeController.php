<?php

namespace App\Http\Controllers;

use App\Models\blogs;
use App\Models\Contact;
use App\Models\emails;
use App\Models\Home;
use App\Models\projects;
use App\Models\testimonials;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HomeController extends Controller
{


    public function home_ret()
    {
        $testimonials = testimonials::all();
        $projects = projects::all();
        $blogs = blogs::get()->take(4);
        $home = Home::all();


        return response()->json([
            'testimonials' => $testimonials,
            'blogs' => $blogs,
            'projects' => $projects,
            'homeData' => $home,
            'blogs' => $blogs,
        ], 200);
    }

    public function registerEmail(Request $request)
    {

        $request->validate([
            'email' => 'required|email'
        ]);


        $email = emails::find($request->email);

        if (!$email) {

            try {
                emails::create([
                    'email' => $request->email
                ]);

                return response()->json([
                    'message' => 'Email Successfully Registered'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Something went wrong with your registration please try again leater',
                    'error' => $e->getMessage()
                ], 404);
            }
        } else {
            return response()->json([
                'message' => 'Email Already Registered , Please Try Another Email'
            ], 405);
        }
    }


    public function contactSend(Request $request)
    {

        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'message' => 'required'
        ]);

        try {
            Contact::create([
                'name' => $request->name,
                'email' => $request->email,
                'message' => $request->message
            ]);

            $email = emails::find($request->email);

            if (!$email) {
                emails::create([
                    'email' => $request->email
                ]);
            }

            return response()->json([
                'message' => 'Message Successfully Sent , We Will Contact You As Soon As Possible'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong with your contact message please try again leater',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
