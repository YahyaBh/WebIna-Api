<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessKeys;
use App\Models\Order;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Mail\VerifyEmailNotificationAdmin as MailVerifyEmailNotification;
use App\Models\Home;
use App\Models\Products;
use App\Models\projects;
use App\Models\testimonials;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AdminUserController extends Controller
{



    public $emailToken;

    public function index()
    {

        $recent_orders = Order::orderBy('created_at', 'desc')->take(10)->get();


        $pending_projects = projects::all();

        $monthlyOrderCounts = [];


        for ($month = 1; $month <= 12; $month++) {
            $orders = Order::whereMonth('created_at', '=', str_pad($month, 2, '0', STR_PAD_LEFT))->get();
            array_push($monthlyOrderCounts, $orders->count());
        }

        $users_total = User::where('role', 'client')->count();

        // Retrieve last month's users
        $lastMonthUsers = User::whereMonth('created_at', '=', Carbon::now()->subMonth()->month)->get();

        $thisMonthUsers = User::whereMonth('created_at', '=', Carbon::now()->month)->get();

        $lastMonthUserCount = $lastMonthUsers->count();
        $thisMonthUserCount = $thisMonthUsers->count();

        $percentageChange = 0;

        if ($lastMonthUserCount > 0) {
            $percentageChange = (($thisMonthUserCount - $lastMonthUserCount) / $lastMonthUserCount) * 100;
        } else {
            $percentageChange = ($thisMonthUserCount - $lastMonthUserCount) * 100;
        }

        //

        $income_total = Order::where('order_type', '=', 'Paid')->sum('total');

        $lastMonthIncome = Order::whereMonth('created_at', '=', Carbon::now()->subMonth()->month)->get();
        $thisMonthIncome = Order::whereMonth('created_at', '=', Carbon::now()->month)->get();

        $lastMonthIncomeCount = $lastMonthIncome->sum('total');
        $thisMonthIncomeCount = $thisMonthIncome->sum('total');

        $percentageChangeIncome = 0;

        if ($lastMonthIncomeCount > 0) {
            $percentageChangeIncome = (($thisMonthIncomeCount - $lastMonthIncomeCount) / $lastMonthIncomeCount) * 100;
        } else {
            $percentageChangeIncome = ($thisMonthIncomeCount - $lastMonthIncomeCount) * 100;
        }



        return response()->json([
            'status' => true,
            'orders_count' => $monthlyOrderCounts,
            'income_total' => $income_total,
            'users_total' => $users_total,
            'percentage_change' => $percentageChange,
            'percentage_change_income' => $percentageChangeIncome,
            'recent_orders' => $recent_orders,
            'pending_projects' => $pending_projects 
        ], 200);
    }

    public function users(Request $request)
    {

        if ($request->has('status')) {
            $users = User::where('status', $request->status)->get();
        } else {
            $users = User::all();
        }

        return response()->json([
            'status' => true,
            'users' => $users,
        ]);
    }


    public function admins(Request $request)
    {

        if ($request->has('role')) {
            $users = User::where('role', $request->role)->get();
        } else {
            $users = User::all();
        }

        return response()->json([
            'status' => true,
            'users' => $users,
        ]);
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

            $hashMatched = '';

            foreach ($accessKeys as $storedHash) {
                if (Hash::check($request->access_key, $storedHash->access_key)) {
                    // Hash matches, set the flag to true and break the loop
                    $hashMatched = $storedHash;
                } else {
                    // Hash does not match, set the flag to false
                }
            }

            if ($hashMatched) {
                if ($hashMatched !== '') {

                    if ($request->has('avatar')) {
                        $avatar = time() . '.' . $request->avatar->getClientOriginalExtension();
                        $request->avatar->move(public_path('images/admins/avatar'), $avatar);

                        $this->emailToken =  Str::random(40);




                        $user = User::create([
                            'avatar' => 'images/admins/avatar/' . $avatar,
                            'name' => $request->name,
                            'email' => $request->email,
                            'password' => Hash::make($request->password),
                            'role' => $hashMatched->role,
                            'verification_token' => $this->emailToken,
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

                    Mail::to($request->user())->send(new MailVerifyEmailNotification($user->email, $this->emailToken, $user->id, $user->name));

                    return response()->json([
                        'status' => true,
                        'message' => 'Please verify email before you continue',
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Access key does not match our records'
                    ], 405);
                }
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
                        'message' => 'Administrator Verified Successfully',
                        'token' => $user->createToken("access-token")->plainTextToken,
                        'admin' => $user
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
            $user = User::where('email', $request->email)->first();


            if ($user) {

                if (Hash::check($request->password, $user->password)) {


                    if ($user->hasVerifiedEmail()) {

                        Auth::login($user);

                        return response()->json([
                            'status' => true,
                            'message' => 'Administrator Verified Successfully',
                            'token' => $user->createToken("access-token")->plainTextToken,
                            'admin' => $user
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











    public function editHome(Request $request)
    {

        try {

            if ($request->date && $request->image) {

                $home = Home::first();


                if ($request->has('image')) {

                    $imageGif = time() . '.' . $request->image->getClientOriginalExtension();
                    $request->image->move(public_path('images/admins/home/edit/video'), $imageGif);
                }

                if ($home) {
                    $home->update([
                        'targetDate' => $request->date,
                        'imageGif' => env('APP_URL') . '/images/admins/home/edit/video/' . $imageGif
                    ]);
                } else {
                    Home::create([
                        'targetDate' => $request->date,
                        'imageGif' => env('APP_URL') . '/images/admins/home/edit/video/' . $imageGif
                    ]);
                }


                return response()->json([
                    'message' => 'Home page updated successfully'
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function projectHome(Request $request)
    {

        $request->validate([
            'name' => 'required',
            'image' => 'required|image',
            'category' => 'required'
        ]);

        try {

            if ($request->has('image')) {

                $image = time() . '.' . $request->image->getClientOriginalExtension();
                $request->image->move(public_path('images/admins/home/projects/images'), $image);
            }


            projects::create([
                'name' =>  $request->name,
                'image' => env('APP_URL') . '/images/admins/home/projects/images/' . $image,
                'category' => $request->category
            ]);


            return response()->json([
                'message' => 'Project added successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function testimonialsHome(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'image' => 'required|image',
            'description' => 'required',
            'rating' => 'required'
        ]);

        try {

            if ($request->has('image')) {

                $image = time() . '.' . $request->image->getClientOriginalExtension();
                $request->image->move(public_path('images/admins/home/testimonials/images'), $image);
            }


            testimonials::create([
                'name' =>  $request->name,
                'image' => env('APP_URL') . '/images/admins/home/testimonials/images/' . $image,
                'description' => $request->description,
                'rating' => $request->rating
            ]);


            return response()->json([
                'message' => 'Testimonial added successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function createProduct(Request $request)
    {

        $request->validate([
            'token' => 'required|unique:products',
            'name' => 'required',
            'description' => 'required',
            'price' => 'required',
            'status' => 'required',
            'image' => 'required|image',
            'image2' => 'required|image',
            'category' => 'required',
            'tags' => 'required',
            'publisher' => 'required',
            'status' => 'required'
        ]);



        try {

            if ($request->has('image')) {

                $image1 = time() . '.' . $request->image->getClientOriginalExtension();
                $request->image->move(public_path('images/store/products'), $image1);

                $image2 = time() . '.' . $request->image2->getClientOriginalExtension();
                $request->image2->move(public_path('images/store/products/'),  '2' . $image2);

                if ($request->has('image3')) {

                    $image3 = time() . '.' . $request->image3->getClientOriginalExtension();
                    $request->image3->move(public_path('images/store/products/3'),  '3' .  $image3);

                    if ($request->has('image4')) {

                        $image4 = time() . '.' . $request->image4->getClientOriginalExtension();
                        $request->image4->move(public_path('images/store/products/4'),  '4' .  $image4);

                        if ($request->has('image5')) {

                            $image5 = time() . '.' . $request->image5->getClientOriginalExtension();
                            $request->image5->move(public_path('images/store/products/5'), '5' .  $image5);

                            if ($request->has('image6')) {

                                $image6 = time() . '.' . $request->image6->getClientOriginalExtension();
                                $request->image6->move(public_path('images/store/products/6'), '6' .  $image6);

                                if ($request->has('image7')) {

                                    $image7 = time() . '.' . $request->image7->getClientOriginalExtension();
                                    $request->image7->move(public_path('images/store/products/7'), '7' . $image7);
                                }
                            }
                        }
                    }
                }
            }


            Products::create([
                'token' => $request->token,
                'name' =>  $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'old_price' => $request->old_price,
                'available' => $request->available,
                'image1' => env('APP_URL') . '/images/store/products/' . $image1,
                'image2' => env('APP_URL') . '/images/store/products/' . '2' . $image2,
                'image3' => $request->has('image3') ? env('APP_URL') . '/images/store/products/' . '3' . $image3 : '',
                'image4' => $request->has('image4') ? env('APP_URL') . '/images/store/products/' . '4' . $image4 : '',
                'image5' => $request->has('image5') ? env('APP_URL') . '/images/store/products/' . '5'  . $image5 : '',
                'image6' => $request->has('image6') ? env('APP_URL') . '/images/store/products/' . '6' . $image6 : '',
                'image7' => $request->has('image7') ? env('APP_URL') . '/images/store/products/' . '7'  . $image7 : '',
                'rating' => $request->rating ?? 0,
                'purchases' => $request->purchases ?? 0,
                'views' => $request->views ?? 0,
                'downloads' => $request->downloads ?? 0,
                'status' => $request->status === 'true' ? 'Available' : 'Not Available',
                'category' => $request->category,
                'tags' => $request->tags,
                'publisher' => $request->publisher,
                'last_updated' => Carbon::now()
            ]);

            return response()->json([
                'message' => 'Product created successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
