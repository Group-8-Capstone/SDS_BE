<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    protected $guarded = [];
    protected $table = 'products';
    use SoftDeletes;
    protected $fillable = [
        'product_name', 'product_price','product_availability'
    ];

    public function orders(){
        return $this->belongsToMany(OnlineOrders::class, 'order_details', 'order_id', 'product_id');
    }
}
