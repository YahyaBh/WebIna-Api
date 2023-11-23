<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessKeys extends Model
{
    use HasFactory;



    protected $fillable = ['access_key','role'];


    protected $hidden = [
        'access_key',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'access_key' => 'hashed',
    ];
}
