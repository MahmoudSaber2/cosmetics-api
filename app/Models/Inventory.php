<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'inventory';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'quantity',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'stock_quantity' => 'integer',
        'min_stock_level' => 'integer',
    ];

    /**
     * Get the validation rules for inventory data
     *
     * @return array<string, string>
     */
    public static function validationRules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock_level' => 'required|integer|min:0',
        ];
    }

    /**
     * Get the product that owns the inventory
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Check if the product is in stock
     */
    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    /**
     * Check if the product is low in stock
     */
    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->min_stock_level;
    }

    /**
     * Check if the product is out of stock
     */
    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }

    /**
     * Reduce stock quantity by the given amount
     */
    public function reduceStock(int $quantity): bool
    {
        if ($this->stock_quantity < $quantity) {
            return false;
        }

        $this->stock_quantity -= $quantity;
        return $this->save();
    }

    /**
     * Increase stock quantity by the given amount
     */
    public function increaseStock(int $quantity): bool
    {
        $this->quantity += $quantity;
        return $this->save();
    }

    /**
     * Set stock quantity to a specific amount
     */
    public function setStock(int $quantity): bool
    {
        $this->stock_quantity = $quantity;
        return $this->save();
    }

    /**
     * Scope to get low stock items
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('stock_quantity <= min_stock_level AND stock_quantity > 0');
    }

    /**
     * Scope to get out of stock items
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('stock_quantity', '<=', 0);
    }

    /**
     * Scope to get in stock items
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }
}
