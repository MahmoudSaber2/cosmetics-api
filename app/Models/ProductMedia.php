<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductMedia extends Model
{
    protected $fillable = [
        'product_id',
        'url',
        'media_type',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
