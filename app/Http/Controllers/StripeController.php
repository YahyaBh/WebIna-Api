<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\UserCard;
use App\Models\UserCards;
use App\Models\UserCart;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StripeController extends Controller
{




    public function checkout(Request $request)
    {


        $request->validate([
            'total' => 'required',
            'products' => 'required',
            'discount' => 'required',
            'subtotal'  => 'required',
            'cardHolderName' => 'required',
            'cardNumber' => 'required',
            'cvv' => 'required',
            'exp_month' => 'required',
            'exp_year' => 'required',
            'saveCard' => 'required',
            'name' => 'required',
            'email' => 'required',
        ]);



        function detectCardType($cardNumber)
        {
            // Define regular expressions for different card types
            $patterns = [
                'visa'       => '/^4[0-9]{12}(?:[0-9]{3})?$/',
                'mastercard' => '/^5[1-5][0-9]{14}$/',
                'amex'       => '/^3[47][0-9]{13}$/',
                'discover'   => '/^6(?:011|5[0-9]{2})[0-9]{12}$/',
            ];

            // Check the card number against each pattern
            foreach ($patterns as $type => $pattern) {
                if (preg_match($pattern, $cardNumber)) {
                    return $type;
                }
            }

            // If no match is found, return null or handle as needed
            return null;
        }


        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));

        // Create a token from the customer's credit card information
        $token = $stripe->tokens->create([
            'card' => [
                'number' => trim($request->cardNumber, '\0'),
                'exp_month' => $request->exp_month,
                'exp_year' => $request->exp_year,
                'cvc' => $request->cardCvc,
            ],
        ]);

        // Create a new customer with the customer's name and email
        $customer = $stripe->customers->create([
            'name' => $request->name,
            'email' => $request->email,
            'source' => $token->id,
        ]);

        // Charge the customer using their payment source
        $charge = $stripe->charges->create([
            'amount' => $request->total * 100,
            'currency' => 'USD',
            'description' => 'WEBINA DIGITAL PRODUCT',
            'customer' => $customer->id,
        ]);

        if ($request->saveCard) {
            if (UserCard::find(auth()->user()->id)) {
                UserCard::where('user_id', auth()->user()->id)->update([
                    'card_name' => $request->cardHolderName,
                    'card_number' => $request->cardNumber,
                    'exp_month' => $request->exp_month,
                    'exp_year' => $request->exp_year,
                    'cvc' => $request->cvv,
                    'card_type' => detectCardType($request->cardNumber),
                    'card_last_four' => substr($request->cardNumber, -4),
                ]);
            } else {
                UserCard::create([
                    'user_id' => auth()->user()->id,
                    'card_name' => $request->cardHolderName,
                    'card_number' => $request->cardNumber,
                    'exp_month' => $request->exp_month,
                    'exp_year' => $request->exp_year,
                    'cvc' => $request->cvv,
                    'card_type' => detectCardType($request->cardNumber),
                    'card_last_four' => substr($request->cardNumber, -4),
                ]);
            }
        }

        foreach ($request->products as $product) {
            UserCart::where('user_id', auth()->user()->id)->where('product_token', $product)->update(['status' => 'purchased']);
        }

        foreach ($request->products as $product) {
            Order::create([
                'order_id' => Str::random(40),
                'user_id' => auth()->user()->id,
                'order_type' => 'Paid',
                'product_token' => $product->token,
                'bussiness_name' => $request->bussiness_name,
                'receiver_email' => $request->receiver_email,
            ]);
        }


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

        // Display a success message to the user.
        return response()->json([
            'success' => true,
            'message' => 'Order successfully paid'
        ]);
    }
}
