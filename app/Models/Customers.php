<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customers extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'name',
        'email',
        'phone_number',
        'service_type',
        'service_id',
        'password',
        'discount',
        'address',
        'start_date',
        'status',
        'plan_id',
        'user_id',
        'last_payment_date'
    ];




    public function plan()
    {
        $this->hasOne(Plan::class, 'id', 'plan_id');
    }
}
