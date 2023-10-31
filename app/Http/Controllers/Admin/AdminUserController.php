<?php

namespace App\Http\Controllers\Admin;

use App\Events\OrderMaking;
use App\Http\Controllers\Controller;
use App\Models\AccessKeys;
use App\Models\Order;
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


        return response()->json([
            'status' => true,
            'user_number' => $users,
        ], 200);
    }


    public function orders(Request $request)
    {

        if ($request->has('order_type')) {

            $orders = Order::where('order_type', $request->order_type)->get();
        } else {

            $orders = Order::all();
        }


        return response()->json([
            'status' => true,
            'orders' => $orders,
        ], 200);
    }


    public function makeOrder(Request $request)
    {


        $message = 'Yahyabh';

        event(new OrderMaking($message));

        return response()->json([
            'status' => true,
            'message' => 'Order created successfully'
        ], 200);
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

            if ($hashMatched && $hashMatched !== '') {

                if ($request->has('avatar')) {
                    $avatar = time() . '.' . $request->avatar->getClientOriginalExtension();
                    $request->avatar->move(public_path('images/admins/avatar'), $avatar);


                    $user = User::create([
                        'avatar' => 'images/admins/avatar/' . $avatar,
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

            $user = User::where('email', $request->email)->where('role', 'admin')->first();

            if ($user) {
                return response()->json([
                    'status' => true,
                    'message' => 'User Logged In Successfully',
                    'token' => $user->createToken("access-token")->plainTextToken,
                    'admin' => $user
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
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
