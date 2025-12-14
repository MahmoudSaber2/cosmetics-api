<?php

namespace App\Filters\Website;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class FilterWebsiteProductPrice implements Filter
{
    public function __invoke(Builder $query, $value, string $property)
    {
        // Remove spaces and split by comma
        $priceRange =$value;

        $from = $priceRange[0] ?? null;
        $to   = $priceRange[1] ?? null;

        // Case 1: from only (e.g., "100," or "100")
        if ($from !== null && $from !== '' && ($to === null || $to === '')) {
            return $query->where('price', '>=', (float) $from);
        }

        // Case 2: to only (e.g., ",500" or "500")
        if ($to !== null && $to !== '' && ($from === null || $from === '')) {
            return $query->where('price', '<=', (float) $to);
        }

        // Case 3: full range (e.g., "100,500")
        if ($from !== '' && $to !== '') {
            return $query->whereBetween('price', [(float) $from, (float) $to]);
        }

        // Nothing matched → return query as is
        return $query;
    }
}
