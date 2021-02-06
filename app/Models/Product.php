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
}
