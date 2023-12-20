<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;




    protected $fillable = ['name' , 'order_id', 'user_id', 'order_type', 'product_token' , 'receiver_email' , 'country' , 'bussiness_name'];
}
