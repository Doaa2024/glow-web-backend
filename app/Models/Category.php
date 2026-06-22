<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // ✅ correct namespace

class Category extends Model
{
    use SoftDeletes;
    protected $fillable = ['name','image_url'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
     protected static function boot()
    {
        parent::boot();

        static::deleting(function ($category) {
            if ($category->isForceDeleting()) {
                // real delete → let DB cascade handle it
                return;
            }

            // soft delete → manually soft delete products
            $category->products()->delete();
        });
    }
}
