<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'car_id',
        'total_amount',
        'car_vin',
        'payment_reference',
        'shipping_location_id',
        'shipping_method_id',
        'status',
    ];

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function shippingLocation()
    {
        return $this->belongsTo(ShippingLocation::class);
    }

    public function shippingMethod()
    {
        return $this->belongsTo(ShippingMethod::class);
    }
}
