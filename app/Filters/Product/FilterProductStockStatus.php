<?php

namespace App\Filters\Product;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class FilterProductStockStatus implements Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        return $query->stockStatus($value);

    }
}
