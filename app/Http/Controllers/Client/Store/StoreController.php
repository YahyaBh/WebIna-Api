<?php

namespace App\Http\Controllers\Client\Store;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Products;
use App\Models\projects;
use App\Models\UserCard;
use App\Models\UserCards;
use App\Models\UserCart;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

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


    public function downloadPdf($token)
    {

        $product = Products::where('token', $token)->first();

        if ($product) {

            $file = public_path() . "/store/products/pdf/" . $product->pdf . '.pdf';

            $headers = array(
                'Content-Type: application/pdf',
            );

            return response()->download($file);
        } else {

            return response()->json([
                'status' => 'failed',
                'message' => 'Product not found'
            ], 401);
        }
    }


    public function userProducts($status)
    {

        try {

            if ($status === 'purchased') {

                $cart = Cart::where('user_id', Auth::user()->id)->where('status', $status)->get();

                $products = collect();
                $orders = collect();

                foreach ($cart as $cartItem) {
                    // Assuming you have a relationship between UserCart and Products
                    $product = Products::where('token', $cartItem->product_token)->first();

                    // Add the product to the collection
                    $products->push($product);

                    $order = Order::where('user_id', Auth::user()->id)->where('product_token', $cartItem->product_token)->first();

                    $orders->push($order);
                }
            }

            return response()->json([
                'status' => 'success',
                'products' => $products,
                'orders' => $orders
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

            $cards = UserCard::where('user_id', Auth::user()->id)->get();


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
