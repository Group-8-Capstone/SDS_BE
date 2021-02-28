<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlineOrders extends Model
{
    protected $guarded = [];
    protected $table = 'online_orders';
    protected $primaryKey = 'order_id';
    protected $fillable = [
        'order_id',
        'receiver_name',
        'email',
        'building_or_street',
        'barangay',
        'city_or_municipality',
        'province',
        'contact_number',
        'total_payment',
        'preferred_delivery_date',
        // 'distance',
        'order_status',
        'payment_status',
        'payment_method',
        'landmark',
        // 'latitude',
        // 'longitude'
    ];

    public function products() {
        return $this->belongsToMany(Product::class, 'order_details', 'order_id', 'product_id')
        ->withTimestamps()
        ->withPivot('order_quantity');
    }
}
