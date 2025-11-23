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

    /**
     * Calculate discount amount
     */
    public function calculateSubtotal(): float
    {
        $discountAmount = $this->calculateDiscountAmount();
        $priceAfterDiscount = max(0, $this->unit_price - $discountAmount);
        return $this->quantity * $priceAfterDiscount;
    }

    /**
     * Update the subtotal based on quantity and unit price
     */
    public function updateSubtotal(): bool
    {
        $this->subtotal = $this->calculateSubtotal();
        return $this->save();
    }
}
