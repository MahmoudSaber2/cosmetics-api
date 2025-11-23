<?php

namespace App\Filters\Order;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class FilterOrder implements Filter
{
    public function __invoke(Builder $query, $value, string $property): Builder
    {
        return $query->where(function ($q) use ($value) {

            // Search inside orders table
            $q->where('number', 'like', '%' . $value . '%');

            // Search inside clients table
            $q->orWhereHas('client', function ($client) use ($value) {
                $client->where('email', 'like', '%' . $value . '%')
                       ->orWhere('phone', 'like', '%' . $value . '%');
            });

        });
    }
}
