<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetailsModel extends Model
{
    protected $guarded = [];
    protected $table = 'order_details';
    protected $fillable = [
        'order_id',
        'customer_id',
        'product_id',
        'order_quantity',
    ];
}
