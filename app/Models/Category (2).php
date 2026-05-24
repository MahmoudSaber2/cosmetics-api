<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{

    use HasFactory, CreatedUpdatedBy, SoftDeletes;
    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'image',
    ];

    protected function casts()
    {
        return [
            'status' => StatusEnum::class
        ];
    }

    /**
     * Get the products for the category.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
