<?php

namespace App\Filters\Product;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class FilterProductPrice implements Filter
{
    public function __invoke(Builder $query, $value, string $property)
    {
        // Expected format: min,max
        if (!is_array($value)) {
            $value = explode(',', $value);
        }

        $min = $value[0] ?? null;
        $max = $value[1] ?? null;

        if ($min !== null) {
            $query->where('price', '>=', $min);
        }

        if ($max !== null) {
            $query->where('price', '<=', $max);
        }
    }
}
