<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'brand',
        'type',
        'color',
        'size',
        'gender',
        'price',
        'image_url',
        'image_path',
        'thumbnail_url',
        'thumbnail_path',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
    ];

    /**
     * Get the validation rules for product data
     *
     * @return array<string, string>
     */
    public static function validationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'brand' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'color' => 'nullable|string|max:100',
            'size' => 'nullable|string|max:50',
            'gender' => 'required|in:men,women,unisex',
            'price' => 'required|numeric|min:0',
            'image_url' => 'nullable|string|max:500',
            'status' => 'required|in:active,inactive',
        ];
    }

    /**
     * Get the inventory for the product
     */
    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }

    /**
     * Get the order items for the product
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope to get only active products
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to filter by gender
     */
    public function scopeByGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }

    /**
     * Scope to filter by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by brand
     */
    public function scopeByBrand($query, $brand)
    {
        return $query->where('brand', $brand);
    }

    /**
     * Get the full image URL
     */
    public function getImageUrlAttribute($value): ?string
    {
        if (!$value) {
            // Try to get from image_path if image_url is not set
            if ($this->image_path) {
                return $this->generateStorageUrl($this->image_path);
            }
            return null;
        }

        // If it's already a full URL, return as is
        if (str_starts_with($value, 'http')) {
            return $value;
        }

        // Otherwise, generate the storage URL
        return $this->generateStorageUrl($value);
    }

    /**
     * Get the full thumbnail URL
     */
    public function getThumbnailUrlAttribute($value): ?string
    {
        if (!$value) {
            // Try to get from thumbnail_path if thumbnail_url is not set
            if ($this->thumbnail_path) {
                return $this->generateStorageUrl($this->thumbnail_path);
            }
            return null;
        }

        // If it's already a full URL, return as is
        if (str_starts_with($value, 'http')) {
            return $value;
        }

        // Otherwise, generate the storage URL
        return $this->generateStorageUrl($value);
    }

    /**
     * Generate storage URL, handling both real and fake storage
     */
    private function generateStorageUrl(string $path): string
    {
        // Always use the fallback URL construction to avoid method issues
        // This works for both real and fake storage environments
        $baseUrl = config('app.url', 'http://localhost');
        return $baseUrl . '/storage/' . $path;
    }

    /**
     * Check if product is in stock
     */
    public function isInStock(): bool
    {
        return $this->inventory && $this->inventory->stock_quantity > 0;
    }

    /**
     * Get available stock quantity
     */
    public function getStockQuantity(): int
    {
        return $this->inventory ? $this->inventory->stock_quantity : 0;
    }

    /**
     * Check if product is low in stock
     */
    public function isLowStock(): bool
    {
        if (!$this->inventory) {
            return true;
        }

        return $this->inventory->stock_quantity <= $this->inventory->min_stock_level;
    }
}
