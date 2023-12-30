<?php

namespace App\Http\Controllers\Client\Store;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Discount;
use App\Models\Products;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{



    public function index()
    {

        try {

            $user = Auth::user();



            $cart = Cart::where('user_id', $user->id)->where('status', 'incart')->get();

            $products = collect();

            foreach ($cart as $cartItem) {
                // Assuming you have a relationship between UserCart and Products
                $product = Products::where('token', $cartItem->product_token)->first();

                // Add the product to the collection
                $products->push($product);
            }


            return response()->json([
                'success' => true,
                'products' => $products,
                'cart_count' => $cart->count()
            ], 200);
        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 405);
        }
    }


    public function get_cart_product(Request $request)
    {
        try {

            $request->validate([
                'product_token' => 'required',
            ]);

            $user = Auth::user();


            $product = Cart::where('user_id', $user->id)->where('product_token', $request->product_token)->where('status', 'incart')->first();

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
            ]);

            $user = Auth::user();


            $product = Cart::where('user_id', $user->id)->where('product_token', $request->product_token)->where('status', 'incart')->first();


            if ($product) {

                $product->delete();

                $cart_count = Cart::where('user_id', $user->id)->where('status', 'incart')->count();


                return response()->json([
                    'success' => true,
                    'message' => 'Product removed from cart',
                    'cart_count' => $cart_count,
                ], 200);
            } else {

                $cart_count = Cart::where('user_id', $user->id)->where('status', 'incart')->count();


                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                    'cart_count' => $cart_count,
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
            ]);


            $user = Auth::user();

            $product = Cart::where('user_id', $user->id)->where('product_token', $request->product_token)->where('status', 'incart')->first();



            if ($product) {

                $cart_count = Cart::where('user_id', $user->id)->where('status', 'incart')->count();

                return response()->json([
                    'success' => false,
                    'message' => 'Product already in cart',
                    'cart_count' => $cart_count,
                ], 404);
            } else {
                Cart::create([
                    'user_id' => $user->id,
                    'product_token' => $request->product_token
                ]);

                $cart_count = Cart::where('user_id', $user->id)->where('status', 'incart')->count();

                return response()->json([
                    'success' => true,
                    'message' => 'Product added to cart',
                    'cart_count' => $cart_count,
                ], 200);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 405);
        }
    }


    public function discount_check(Request $request)
    {

        try {

            $request->validate([
                'discount' => 'required'
            ]);


            $discount = Discount::where('discount_code', $request->discount)->first();



            if ($discount && $discount->expired !== true) {

                $discount->update([
                    'expired' => true
                ]);

                return response()->json([
                    'success' => true,
                    'discount' => $discount
                ], 200);
            } else {

                return response()->json([
                    'success' => false,
                    'discount' => 'Discount not found'
                ], 404);
            }
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 405);
        }
    }
}
