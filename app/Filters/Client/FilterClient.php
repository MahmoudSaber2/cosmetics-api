<?php

namespace App\Filters\Client;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class FilterClient implements Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        return $query->where(function ($query) use ($value) {
            $query->where('name', 'like', '%' . $value . '%')
                ->orWhere('email', 'like', '%' . $value . '%')
                ->orWhere('phone', 'like', '%' . $value . '%')
                ->orWhere('city', 'like', '%' . $value . '%');
        });
    }
}
