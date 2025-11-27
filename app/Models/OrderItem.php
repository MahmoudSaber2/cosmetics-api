<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'cost',
        'total_cost',
        'total_price',
    ];

    /**
     * Get the order that owns the order item
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product that owns the order item
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    private function cleanNumber($value)
    {
        return rtrim(rtrim(number_format($value, 3, '.', ''), '0'), '.');
    }

    public function getTotalCostAttribute($value)
    {
        return $this->cleanNumber($value);
    }

    public function getTotalPriceAttribute($value)
    {
        return $this->cleanNumber($value);
    }

    public function getCostAttribute($value)
    {
        return $this->cleanNumber($value);
    }

    public function getPriceAttribute($value)
    {
        return $this->cleanNumber($value);
    }
}
