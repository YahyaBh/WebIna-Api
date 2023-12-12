<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCards extends Model
{
    use HasFactory;



    protected $fillable = [
        'user_id',
        'card_number',
        'expiry_month',
        'expiry_year',
        'cvv',
        'name',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'card_type'
    ];


    protected $hidden = [
        'card_number',
        'cvv',
    ];
}
