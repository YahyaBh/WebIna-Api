<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessKeys;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminUserController extends Controller
{


    public function index()
    {

        $users = User::all()->count();
    }

    public function register(Request $request)
    {
        try {
            //Validated
            $validateUser = Validator::make(
                $request->all(),
                [
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
                    'password' => ['required', 'confirmed'],
                    'password_confirmation' => ['required_with:password|same,password|min:6'],
                    'access_key' => ['required'],
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $accessKeys = AccessKeys::all(); // Replace with your logic to retrieve hashes from the database

            foreach ($accessKeys as $storedHash) {
                if (Hash::check($request->access_key, $storedHash->access_key)) {
                    // Hash matches, set the flag to true and break the loop
                    $hashMatched = $storedHash;
                }
            }

            if ($hashMatched !== '') {

                if ($request->has('avatar')) {
                    $avatar = time() . '.' . $request->file->extension();
                    $request->avatar->move(public_path('uploads/admins/avatar'), $avatar);


                    $user = User::create([
                        'avatar' => 'uploads/admins/avatar/' . $avatar,
                        'name' => $request->name,
                        'email' => $request->email,
                        'password' => Hash::make($request->password),
                        'role' => $hashMatched->role,
                        'email_verified' => Date::now()
                    ]);
                } else {


                    $user = User::create([
                        'avatar' => 'uploads/admins/avatar/default_avatar.png',
                        'name' => $request->name,
                        'email' => $request->email,
                        'password' => Hash::make($request->password),
                        'role' => $hashMatched->role,
                        'email_verified' => Date::now()
                    ]);
                }


                Auth::login($user);

                return response()->json([
                    'status' => true,
                    'message' => 'User Created Successfully',
                    'token' => $user->createToken("access-token")->plainTextToken,
                    'admin' => $user
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Access key does not match our records'
                ], 405);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

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

            if ($user) {
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
                    ], 405);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'No user found',
                ], 404);
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
        if (Auth::check()) {
            Auth::guard('web')->logout();

            return response()->json([
                'status' => true,
                'message' => 'logged off successfully'
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'status' => 'Something went wrong , please re-login to your account'
            ], 401);
        }
    }
}
