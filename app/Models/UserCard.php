<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCard extends Model
{
    use HasFactory;




    protected $fillable = [
        'user_id',
        'card_name',
        'card_last_four',
        'card_number',
        'exp_month',
        'exp_year',
        'cvc',
        'is_default',
        'card_type',
    ];

    
}
