<?php

namespace App\Http\Controllers\Client\Store;

use App\Http\Controllers\Controller;
use App\Models\UserCart;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Cart extends Controller
{


    public function get_cart_product(Request $request)
    {
        try {

            $request->validate([
                'product_token' => 'required',
                'user_id' => 'required',
            ]);


            $product = UserCart::where('user_id', $request->user_id)->where('product_token', $request->product_token)->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'available' => false,
                ], 404);
            } else {
                return response()->json([
                    'success' => true,
                    'available' => true,
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 405);
        }
    }


    public function remove_from_cart(Request $request)
    {
        try {
            $request->validate([
                'product_token' => 'required',
                'user_id' => 'required',
            ]);


            $product = UserCart::where('user_id', $request->user_id)->where('product_token', $request->product_token)->first();


            if ($product) {

                $product->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Product removed from cart',
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                ], 404);
            }

            
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 405);
        }
    }


    public function add_to_cart(Request $request)
    {
        try {

            $request->validate([
                'product_token' => 'required',
                'user_id' => 'required',
            ]);



            UserCart::create([
                'user_id' => $request->user_id,
                'product_token' => $request->product_token
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product added to cart',
            ], 200);

            // }

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 405);
        }
    }
}
