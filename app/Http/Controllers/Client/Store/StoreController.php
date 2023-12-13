<?php

namespace App\Http\Controllers\Client\Store;

use App\Http\Controllers\Controller;
use App\Models\Products;
use App\Models\projects;
use App\Models\UserCards;
use App\Models\UserCart;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    public function index()
    {

        try {

            $products = Products::all();
            $hot_products = Products::all()->take(6);
            $porjects = projects::all()->take(6);

            return response()->json([
                'status' => 'success',
                'products' => $products,
                'hot_products' => $hot_products,
                'porjects' => $porjects,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'products' => $e->getMessage()
            ], 401);
        }
    }



    public function userProducts($status)
    {

        try {

            $cart = UserCart::where('user_id', Auth::user()->id)->where('status', $status)->get();



            $products = collect();

            foreach ($cart as $cartItem) {
                // Assuming you have a relationship between UserCart and Products
                $product = Products::where('token', $cartItem->product_token)->first();

                // Add the product to the collection
                $products->push($product);
            }

            return response()->json([
                'status' => 'success',
                'products' => $products
            ], 200);
            
        } catch (Exception $e) {

            return response()->json([
                'status' => 'failed',
                'products' => $e->getMessage()
            ], 401);
        }
    }



    public function cardsIndex()
    {


        try {

            $cards = UserCards::where('user_id', Auth::user()->id)->get();


            return response()->json([
                'status' => 'success',
                'cards' => $cards
            ], 200);
        } catch (Exception $e) {

            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage()
            ], 401);
        }
    }







    public function product(Request $request)
    {
        try {

            $request->validate([
                'product_token' => 'required',
            ]);


            $product = Products::where('token', $request->product_token)->first();

            $product->update([
                'views' => $product->views + 1
            ]);


            return response()->json([
                'status' => 'success',
                'product' => $product
            ], 200);
        } catch (Exception $e) {

            return response()->json([
                'status' => 'failed',
                'product' => $e->getMessage()
            ], 401);
        }
    }
}
