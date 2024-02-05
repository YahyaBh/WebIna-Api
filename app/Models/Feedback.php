<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;



    protected $fillable = [
        'title',
        'text',
        'user_id',
        'product_token',
        'rating',
        'status'
    ];


    // Assuming your foreign key is user_id
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
