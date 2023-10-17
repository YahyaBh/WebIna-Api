<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{





    public function login(Request $request)
    {

        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'password' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            if ($user->role != 'client') {
                return response()->json([
                    'status' => true,
                    'message' => 'User Logged In Successfully',
                    'token' => $user->createToken("access-token")->plainTextToken,
                    'admin' => $user
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized Access',
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
