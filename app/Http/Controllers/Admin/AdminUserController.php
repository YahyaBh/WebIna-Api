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



        //Months Orders Count
        $monthlyOrderCounts = [];
        $xAxisLabelsMonths = [];

        // Get the start of the current month
        $currentMonth = now()->startOfMonth();

        // Iterate over the last 12 months, starting from the current month and going back
        for ($month = 11; $month >= 0; $month--) {
            $startOfMonth = $currentMonth->copy()->subMonths($month);
            $endOfMonth = $startOfMonth->copy()->endOfMonth();

            // Convert the timestamps to UTC when querying the database
            $orders = Order::whereBetween('created_at', [
                $startOfMonth->toDateTimeString(),
                $endOfMonth->toDateTimeString()
            ])->get();

            // Collect the monthly order count
            array_push($monthlyOrderCounts, $orders->count());

            // Generate xAxis labels for months in UTC
            $label = $startOfMonth->format('M Y'); // Adding the month and year to the label
            array_push($xAxisLabelsMonths, $label);
        }



        //Hours Orders Count
        $hourlyOrderCounts = [];
        $xAxisLabelsHours = [];

        // Get the start of the current hour
        $currentHour = now()->startOfHour();

        // Iterate over the last 24 hours, starting from the current hour and going back
        for ($hour = 23; $hour >= 0; $hour--) {
            $startHour = $currentHour->copy()->subHours($hour);
            $endHour = $startHour->copy()->addHour();

            // Convert the timestamps to UTC when querying the database
            $orders = Order::whereBetween('created_at', [
                $startHour->toDateTimeString(),
                $endHour->toDateTimeString()
            ])->get();

            // Collect the hourly order count
            array_push($hourlyOrderCounts, $orders->count());

            // Generate xAxis labels for hours in UTC
            $label = $startHour->format('D H:i'); // Adding the day of the week and hour to the label
            array_push($xAxisLabelsHours, $label);
        }


        //Week Orders Count
        $dailyOrderCounts = [];
        $xAxisLabelsDays = [];

        // Get the start of today
        $today = now()->startOfDay();

        // Iterate over the last 7 days, starting from today and going back
        for ($day = 6; $day >= 0; $day--) {
            $startOfDay = $today->copy()->subDays($day);
            $endOfDay = $startOfDay->copy()->endOfDay();

            // Convert the timestamps to UTC when querying the database
            $orders = Order::whereBetween('created_at', [
                $startOfDay->toDateTimeString(),
                $endOfDay->toDateTimeString()
            ])->get();

            // Collect the daily order count
            array_push($dailyOrderCounts, $orders->count());

            // Generate xAxis labels for days in UTC
            $label = $startOfDay->format('D H:i'); // Adding the day of the week and hour to the label
            array_push($xAxisLabelsDays, $label);
        }

        // Output the results for each day




        $users_total = User::where('role', 'client')->count();

        // Retrieve last month's users
        $lastMonthUsers = User::where('role', 'client')->whereMonth('created_at', '=', Carbon::now()->subMonth()->month)->get();
        $thisMonthUsers = User::where('role', 'client')->whereMonth('created_at', '=', Carbon::now()->month)->get();

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
            'income_total' => $income_total,
            'users_total' => $users_total,
            'percentage_change' => $percentageChange,
            'percentage_change_income' => $percentageChangeIncome,
            'recent_orders' => $recent_orders,
            'pending_projects' => $pending_projects,
            'monthly_orders_count' => $monthlyOrderCounts,
            'week_orders_count' => $dailyOrderCounts,
            'hourly_order_counts' => $hourlyOrderCounts,
            'xAxisLabelsHours' => $xAxisLabelsHours,
            'xAxisLabelsWeek' => $xAxisLabelsDays,
            'xAxisLabelsMonth' => $xAxisLabelsMonths,
        ], 200);
    }

    public function users(Request $request)
    {

        if ($request->has('status')) {
            $users = User::where('status', $request->status)->where('role', 'client')->get();
        } else {
            $users = User::where('role', 'client')->get();
        }

        return response()->json([
            'status' => true,
            'users' => $users,
        ]);
    }


    public function administrators(Request $request)
    {

        try {
            if ($request->has('role')) {
                $admins = User::where('role', $request->role)->where('role', '!=', 'client')->get();
            } else {
                $admins = User::where('role', '!=', 'client')->get();
            }

            return response()->json([
                'status' => true,
                'admins' => $admins,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function products(Request $request)
    {

        if ($request->has('search')) {
            $product = Products::where('token', $request->search)->orWhere('name', $request->search)->get();

            if ($product) {
                return response()->json([
                    'status' => true,
                    'products' => $product,
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'products' => 'No product found',
                ], 404);
            }
        } else {
            if ($request->has('status')) {
                $products = Products::where('status', $request->status)->get();
            } else {
                $products = Products::all();
            }

            return response()->json([
                'status' => true,
                'products' => $products,
            ]);
        }
    }

    public function product_search($search)
    {



        $product = Products::where('token', 'like', '%' . $search . '%')->orWhere('name', 'like', '%' . $search . '%')->get();


        if ($product) {
            return response()->json([
                'status' => true,
                'products' => $product,
            ]);
        } else {
            return response()->json([
                'status' => true,
                'products' => 'No product found',
            ], 404);
        }
    }

    public function new_product(Request $request)
    {

        $request->validate([
            'token' => 'required|unique:products',
            'name' => 'required',
            'description' => 'required',
            'price' => 'required',
            'status' => 'required',
            'image1' => 'required|image',
            'image2' => 'required|image',
            'category' => 'required',
            'tags' => 'required',
            'publisher' => 'required',
            'creationDate' => 'required',
            'type' => 'required',
            'file' => 'required',
            'link' => 'required',
        ]);



        try {

            $product = new Products();

            if ($request->has('image1')) {

                $image1 = time() . '.' . $request->image1->getClientOriginalExtension();
                $request->image1->move(public_path('images/store/products'), $image1);

                $product->image1 = env('APP_URL') . '/images/store/products/' . $image1;

                if ($request->has('image2')) {

                    $image2 = time() . '.' . $request->image2->getClientOriginalExtension();
                    $request->image2->move(public_path('images/store/products/'),  '2' . $image2);

                    $product->image2 = env('APP_URL') . '/images/store/products/2' . $image2;

                    if ($request->has('image3')) {

                        $image3 = time() . '.' . $request->image3->getClientOriginalExtension();
                        $request->image3->move(public_path('images/store/products/'),  '3' . $image3);

                        $product->image3 = env('APP_URL') . '/images/store/products/3' . $image3;

                        if ($request->has('image4')) {

                            $image4 = time() . '.' . $request->image4->getClientOriginalExtension();
                            $request->image4->move(public_path('images/store/products/'),  '4' . $image4);

                            $product->image4 = env('APP_URL') . '/images/store/products/4' . $image4;

                            if ($request->has('image5')) {

                                $image5 = time() . '.' . $request->image5->getClientOriginalExtension();
                                $request->image5->move(public_path('images/store/products/'),  '5' . $image5);

                                $product->image5 = env('APP_URL') . '/images/store/products/5' . $image5;

                                if ($request->has('image6')) {

                                    $image6 = time() . '.' . $request->image6->getClientOriginalExtension();
                                    $request->image6->move(public_path('images/store/products/'),  '6' . $image6);

                                    $product->image6 = env('APP_URL') . '/images/store/products/6' . $image6;

                                    if ($request->has('image7')) {

                                        $image7 = time() . '.' . $request->image7->getClientOriginalExtension();
                                        $request->image7->move(public_path('images/store/products/'),  '7' . $image7);

                                        $product->image7 = env('APP_URL') . '/images/store/products/7' . $image7;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $file = time() . '.' . $request->file->getClientOriginalExtension();
            $request->file->move(public_path('/images/store/files/products'), $file);

            $product->pdf = env('APP_URL') . '/images/store/files/products/' . $file;




            $product->token = $request->token;
            $product->name =  $request->name;
            $product->description = $request->description;
            $product->price = $request->price;
            $product->status = $request->status;
            $product->rating = $request->initRating;
            $product->downloads = $request->initDownloads;
            $product->views = $request->initViews;
            $product->purchases = $request->initPurchases;
            $product->category = $request->category;
            $product->tags = $request->tags;
            $product->publisher = $request->publisher;
            $product->last_updated = $request->creationDate;
            $product->type = $request->type;
            $product->link = $request->link;

            $product->save();


            return response()->json([
                'status' => true,
                'message' => 'Product created successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
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

    public function order_search($search)
    {
        $product = Order::where('order_id', 'like', '%' . $search . '%')->orWhere('name', 'like', '%' . $search . '%')->get();


        if ($product) {
            return response()->json([
                'status' => true,
                'products' => $product,
            ]);
        } else {
            return response()->json([
                'status' => true,
                'products' => 'No product found',
            ], 404);
        }
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

            if ($user && $user->role === 'admin') {
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
