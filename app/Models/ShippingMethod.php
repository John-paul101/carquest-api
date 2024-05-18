<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'price',
        'shipping_location_id',
    ];

    /**
     * Get the shipping location that owns the shipping method.
     */
    public function shippingLocation()
    {
        return $this->belongsTo(ShippingLocation::class);
    }
}
