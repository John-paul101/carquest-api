<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand',
        'title',
        'description',
        'colors',
        'pictures',
        'year',
        'price',
        'customs_price',
        'available_quantity',
    ];

    protected $casts = [
        'colors' => 'json',
        'pictures' => 'json',
    ];
}
