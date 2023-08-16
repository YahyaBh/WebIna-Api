<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Products;
use Exception;
use Illuminate\Http\Request;

class StoreController extends Controller
{



    public function index()
    {

        // try {

            $products = Products::all();

            return response()->json([
                'status' => 'success',
                'products' => $products
            ], 200);
        // } catch (Exception $e) {

        //     $products = Products::all();

        //     return response()->json([
        //         'status' => 'failed',
        //         'products' => $e->getMessage()
        //     ], 401);
        // }
    }
}
