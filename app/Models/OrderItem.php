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
     * Calculate subtotal based on quantity and unit price
     */
    public function calculateSubtotal(): float
    {
        return $this->quantity * $this->unit_price;
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
            $orderItem->subtotal = $orderItem->calculateSubtotal();
        });

        static::updating(function ($orderItem) {
            if ($orderItem->isDirty(['quantity', 'unit_price'])) {
                $orderItem->subtotal = $orderItem->calculateSubtotal();
            }
        });
    }
}
