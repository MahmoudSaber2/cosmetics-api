<?php

namespace App\Http\Resources\V1\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'productId' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'slug' => $this->slug,
            'price' => $this->price,
            'brand' => $this->when($this->brand, [
                'id' => $this->brand?->id,
                'name' => $this->brand?->name,
            ]),
            'category' => $this->when($this->category, [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
                'slug' => $this->category?->slug,
            ]),
            'image' => $this->when($this->media, [
                'url' => $this->media?->url,
                'type' => $this->media?->media_type,
            ]),
            'hasStock' => $this->has_stock,
            'stockQuantity' => $this->inventory?->quantity ?? 0,
            'stockStatus' => $this->stockStatus,
        ];
    }
}
