<?php

namespace App\Http\Controllers\Client\Order;

use App\Events\MyEvent;
use App\Events\Order;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;



class OrderController extends Controller
{

    public function order_create()
    {
        try {
            event(new Order('Yahya'));

        return response()->json([
            'status' => true,
            'message' => 'Order created successfully'
        ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Order not created' . $e
            ], 500);
        }
    }
}
