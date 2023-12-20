<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cart;
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

        $cart_products = Cart::where('user_id', auth()->user()->id)->where('status', 'incart')->get();


        if ($cart_products->count() > 0) {
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
                return 'undefined';
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
            $stripe->charges->create([
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


            foreach ($cart_products as $cart_product) {
                // Check if the product has a valid token
                if ($cart_product->token) {
                    Order::create([
                        'order_id' => Str::random(40),
                        'user_id' => auth()->user()->id,
                        'name' => $request->name,
                        'order_type' => 'Paid',
                        'product_token' => $cart_product->token || '8e478edf-9c55-4ef9-ae46-f91ddd5d285f',
                        'bussiness_name' => $request->bussiness_name,
                        'receiver_email' => $request->receiver_email,
                    ]);

                    // Update the product status
                    $cart_product->update(['status' => 'purchased']);
                }
            }



            // Display a success message to the user.
            return response()->json([
                'success' => true,
                'message' => 'Order successfully paid'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ]);
        }
    }
}
