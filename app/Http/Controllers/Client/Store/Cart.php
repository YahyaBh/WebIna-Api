<?php

namespace App\Http\Controllers\Client\Store;

use App\Http\Controllers\Controller;
use App\Models\UserCart;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Cart extends Controller
{





    public function add_to_cart(Request $request)
    {
        try {

            $request->validate([
                'product_token' => 'required',
            ]);


            if (Auth::check()) {
                $user = Auth::user();

                $cart = UserCart::where('user_id', $user->id)->first();

                if ($cart) {
                    $cart->product_token = $request->product_token;
                    $cart->save();
                } else {
                    $cart = new UserCart();
                    $cart->user_id = $user->id;
                    $cart->product_token = $request->product_token;
                    $cart->save();
                }


                return response()->json([
                    'success' => true,
                    'message' => 'Product added to cart',

                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 405);
        }
    }
}
