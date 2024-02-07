<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\UserCart;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Str;

class PayPalController extends Controller
{
    /**
     * Create transaction.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createOrder(Request $request)
    {


        $request->validate([
            'total' => 'required',
            'products' => 'required',
            'discount' => 'required',
            'subtotal'  => 'required',
            'paymentMethod' => 'required'
        ]);

        $array_products = explode(',', $request->products);



        foreach ($array_products as $product) {
            Order::create([
                'order_id' => Str::random(40),
                'user_id' => auth()->user()->id,
                'order_type' => 'Paid',
                'product_token' => $product,
            ]);
        }

        $cart_products = UserCart::where('user_id', auth()->user()->id)->get();

        $cart_products->each(function ($cart_product) {
            $cart_product->status = 'purchased';
            $cart_product->update();
        });

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully',
        ]);
    }
}
