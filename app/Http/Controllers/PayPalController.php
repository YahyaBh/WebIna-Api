<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PayPalController extends Controller
{
    /**
     * Create transaction.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createTransaction()
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();
        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('successTransaction'),
                "cancel_url" => route('cancelTransaction'),
            ],
            "purchase_units" => [
                0 => [
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => "1000.00"
                    ]
                ]
            ]
        ]);

        if (isset($response['id']) && $response['id'] != null) {
            return response()->json(['approve_url' => $this->getApproveUrl($response)]);
        } else {
            return response()->json(['error' => $response['message'] ?? 'Something went wrong.']);
        }
    }


    /**
     * Process transaction.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processTransaction(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();
        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('successTransaction'),
                "cancel_url" => route('cancelTransaction'),
            ],
            "purchase_units" => [
                0 => [
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => "1000.00"
                    ]
                ]
            ]
        ]);

        if (isset($response['id']) && $response['id'] != null) {
            // Return JSON response
            return response()->json(['approve_url' => $this->getApproveUrl($response)]);
        } else {
            return response()->json(['error' => $response['message'] ?? 'Something went wrong.']);
        }
    }

    /**
     * Success transaction.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function successTransaction(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        $response = $provider->capturePaymentOrder($request['token']);

        if (isset($response['status']) && $response['status'] == 'COMPLETED') {
            return response()->json(['success' => 'Transaction complete.']);
        } else {
            return response()->json(['error' => $response['message'] ?? 'Something went wrong.']);
        }
    }

    /**
     * Cancel transaction.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelTransaction(Request $request)
    {
        return response()->json(['error' => $response['message'] ?? 'You have canceled the transaction.']);
    }

    /**
     * Get the approve URL from the PayPal response.
     *
     * @param array $response
     * @return string|null
     */
    private function getApproveUrl(array $response)
    {
        foreach ($response['links'] as $links) {
            if ($links['rel'] == 'approve') {
                return $links['href'];
            }
        }

        return null;
    }
}
