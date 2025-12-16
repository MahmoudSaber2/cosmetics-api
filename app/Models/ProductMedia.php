<?php

namespace App\Models;

use App\Enums\IsMainEnum;
use App\Enums\MediaTypeEnum;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductMedia extends Model
{
    use HasFactory, CreatedUpdatedBy, SoftDeletes;

    protected $fillable = [
        'product_id',
        'url',
        'media_type',
        'is_main',
    ];

    protected $casts = [
        'is_main' => IsMainEnum::class,
        'media_type' => MediaTypeEnum::class,
    ];

    protected $appends = ['full_url'];

    public function getFullUrlAttribute()
    {
        return asset("storage/{$this->url}");
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope to get main media only
     */
    public function scopeMain($query)
    {
        return $query->where('is_main', IsMainEnum::MAIN->value);
    }

    /**
     * Scope to get non-main media only
     */
    public function scopeNonMain($query)
    {
        return $query->where('is_main', IsMainEnum::NOT_MAIN->value);
    }
}
