<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class packs extends Model
{
    use HasFactory;



    protected $fillable = ['pack_category' , 'pack_name' , 'pack_price' , 'pack_description' ,'pack_specs' , 'available' , 'pack_level' , 'pack_token' ];
}
