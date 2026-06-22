<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $casts = [
    'total_price' => 'float',
];
    protected $fillable = ['user_id', 'total_price','full_name','email','phone','address','city','zip_code','payment_method'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }
}
