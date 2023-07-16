<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class projects extends Model
{
    use HasFactory;



    protected $fillable = ['image', 'name', 'description', 'owner','categories', 'technologies', 'status', 'date'];
}
