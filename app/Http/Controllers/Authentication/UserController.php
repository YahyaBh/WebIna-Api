<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Mail\VerifyEmailNotification as MailVerifyEmailNotification;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{

    public $emailToken;

    public function createUser(Request $request)
    {
        try {
            //Validated
            $validateUser = Validator::make(
                $request->all(),
                [
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
                    'password' => ['required', 'confirmed'],
                    'password_confirmation' => 'required|min:6'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $this->emailToken =  Str::random(40);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'verification_token' => $this->emailToken
            ]);


            Mail::to($request->user())->send(new MailVerifyEmailNotification($user->email, $this->emailToken, $user->id, $user->name));

            return response()->json([
                'status' => true,
                'message' => 'User registered successfully. Please check your email for verification.',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function verifyEmail(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'emailToken' => 'required',
                    'id' => 'required'
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


            if ($user->id == $request->id) {

                if ($user->verification_token == $request->emailToken) {

                    $user->update([
                        'email_verified_at' => now(),
                    ]);


                    Auth::login($user);

                    return response()->json([
                        'status' => true,
                        'message' => 'User Verified Successfully',
                        'token' => $user->createToken("access-token")->plainTextToken,
                        'user' => $user
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'User Not Verified'
                    ], 401);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid Email'
                ], 401);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function checkVerification(Request $request)
    {
        try {
            $validateUser = Validator::make(
                $request->all(),
                [
                    'email' => 'required|email',
                    'passowrd' => 'required',
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


            if ($user) {

                if (Hash::check($request->password, $user->password)) {


                    if ($user->hasVerifiedEmail()) {

                        Auth::login($user);

                        return response()->json([
                            'status' => true,
                            'message' => 'User Verified Successfully',
                            'token' => $user->createToken("access-token")->plainTextToken,
                            'user' => $user
                        ], 200);
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' => 'Email Is Not Verified'
                        ], 402);
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Email or password is incorrect'
                    ], 401);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found'
                ], 401);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function loginUser(Request $request)
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

            if ($user) {


                if (Hash::check($request->password, $user->password)) {

                    if ($user->hasVerifiedEmail()) {
                        return response()->json([
                            'status' => true,
                            'message' => 'User Logged In Successfully',
                            'token' => $user->createToken("access-token")->plainTextToken,
                            'user' => $user
                        ], 200);
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' => 'Email is not verified'
                        ], 401);
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid Email or Password'
                    ], 401);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No user found'
                ], 405);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy()
    {
        Auth::guard('web')->logout();

        return response()->json([
            'status' => 'logged off successfully'
        ], 200);
    }
}
