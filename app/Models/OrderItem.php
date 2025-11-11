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
        'unit_price',
        'discount_type',
        'discount_value',
        'discount_amount',
        'subtotal',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Get the validation rules for order item data
     *
     * @return array<string, string>
     */
    public static function validationRules(): array
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
        ];
    }

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
    public function calculateDiscountAmount(): float
    {
        if (!$this->discount_type || !$this->discount_value) {
            return 0;
        }

        if ($this->discount_type === 'percentage') {
            return ($this->unit_price * $this->discount_value) / 100;
        }

        return $this->discount_value;
    }

    /**
     * Calculate subtotal based on quantity, unit price and discount
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

    /**
     * Boot method to automatically calculate subtotal
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($orderItem) {
            $orderItem->discount_amount = $orderItem->calculateDiscountAmount();
            $orderItem->subtotal = $orderItem->calculateSubtotal();
        });

        static::updating(function ($orderItem) {
            if ($orderItem->isDirty(['quantity', 'unit_price', 'discount_type', 'discount_value'])) {
                $orderItem->discount_amount = $orderItem->calculateDiscountAmount();
                $orderItem->subtotal = $orderItem->calculateSubtotal();
            }
        });
    }
}
