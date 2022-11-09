<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpensCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'user_id',
    ];

    public function expens(){
        return $this->hasMany(Expens::class, 'category_id', 'id');
    }
}
