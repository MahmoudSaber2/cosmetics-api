<?php

namespace App\Models;

use App\Enums\ProductStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;

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
        'brand_id',
        'category_id',
        'gender',
        'cost',
        'price',
        'status',
        'slug',
        'min_stock',
        'has_stock',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ProductStatusEnum::class,
            'cost' => 'decimal:2',
            'price' => 'decimal:2',
        ];
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function media()
    {
        return $this->hasMany(ProductMedia::class);
    }

    public function mainMedia()
    {
        return $this->hasOne(ProductMedia::class)->where('is_main', true);
    }

    public function getProductMediaAttribute()
    {
        return $this->mainMedia;
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStockStatusAttribute()
    {
        if( $this->inventory && $this->inventory->quantity > 0  && $this->inventory->quantity > $this->min_stock ) {
            return 2;
        }

        if( $this->inventory && $this->inventory->quantity <= $this->min_stock  && $this->inventory->quantity >= 1 ) {
            return 1;
        }

        if( !$this->inventory ) {
            return 3;
        }

        return 0;
    }

    public function scopeStockStatus(Builder $query, $status)
{
    return $query->where(function ($q) use ($status) {

        // IN STOCK → status 2
        if ($status == 2) {
            $q->whereHas('inventory', function ($inv) {
                $inv->whereColumn('quantity', '>', 'products.min_stock')
                    ->where('quantity', '>', 0);
            });
        }

        // LOW STOCK → status 1
        if ($status == 1) {
            $q->whereHas('inventory', function ($inv) {
                $inv->whereColumn('quantity', '<=', 'products.min_stock')
                    ->where('quantity', '>=', 1);
            });
        }

        // NO INVENTORY → status 3
        if ($status == 3) {
            $q->whereDoesntHave('inventory');
        }

        // OUT OF STOCK → status 0
        if ($status == 0) {
            $q->whereHas('inventory', function ($inv) {
                $inv->where('quantity', '=', 0);
            });
        }
    });
}

}
