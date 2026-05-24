<?php

namespace App\Http\Resources\V2\ProductMedia;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductMediaCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->collection->map(function ($media) {
            return new ProductMediaResource($media);
        })->toArray();
    }
}
