<?php

namespace App\Filters\Order;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class FilterOrderDate implements Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        [$startDate, $endDate] = explode(',', $value);
        if( $startDate && ! $endDate ) {
            return $query->whereDate('created_at', $startDate);
        }

        if( !$startDate && $endDate ) {
            return $query->whereDate('created_at', $endDate);
        }

        return $query->whereBetween('created_at', [$startDate, $endDate]);

    }
}
