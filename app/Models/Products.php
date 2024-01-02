<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;


    protected $fillable = ['token' , 'name', 'rating', 'purchases', 'description', 'price', 'old_price', 'image1', 'image2', 'image3', 'image4', 'image5', 'image6', 'image7', 'status', 'category', 'tags', 'publisher', 'views', 'downloads', 'purchases' , 'last_updated' , 'type' , 'hot_deal'];


}
