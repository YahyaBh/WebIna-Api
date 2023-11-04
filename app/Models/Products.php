<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;


    private $fillable = ['name', 'rating', 'purchases', 'description', 'price', 'image', 'status', 'category'];
}
