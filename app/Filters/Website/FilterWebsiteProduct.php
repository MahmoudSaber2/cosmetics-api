<?php

namespace App\Filters\Website;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class FilterWebsiteProduct implements Filter
{
    public function __invoke(Builder $query, $value, string $property)
    {
        return $query->where(function ($q) use ($value) {
            $q->where('name', 'LIKE', "%{$value}%")
              ->orWhere('description', 'LIKE', "%{$value}%");
            //   ->orWhereHas(relation: 'brand', function ($brandQuery) use ($value) {
            //       $brandQuery->where('name', 'LIKE', "%{$value}%");
            //   })
            //   ->orWhereHas('category', function ($categoryQuery) use ($value) {
            //       $categoryQuery->where('name', 'LIKE', "%{$value}%");
            //   });
        });
    }
}
