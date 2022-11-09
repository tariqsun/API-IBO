<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentHistory extends Model
{
    use HasFactory;


    protected $fillable = [
        'payment',
        'payment_id',
        'user_id',
    ];
}
