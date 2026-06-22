<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // ✅ correct import

class Product extends Model
{
    use SoftDeletes;
 protected $casts = [
    'price' => 'float',
];
    protected $fillable = ['name', 'description', 'subtitle', 'image_url', 'price', 'category_id'];  
    public function category()
    {
        return $this->belongsTo(Category::class);
    } 
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }
}
