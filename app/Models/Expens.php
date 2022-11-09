<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expens extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'description',
        'expens_date',
        'category_id',
        'user_id'
    ];

    public function category(){
        return $this->hasOne(ExpensCategory::class, 'id', 'category_id');
    }
}
