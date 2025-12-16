<?php

namespace App\Http\Resources\V2\Product;

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

        $brand = $this->brand ? [
            'id' => $this->brand->id,
            'name' => $this->brand->name,
        ] : [];
        $category = $this->category ? [
            'id' => $this->category->id,
            'name' => $this->category->name,
        ] : [];
        $media = $this->media->map(fn ($mediaItem) => [
            'mediaId' => $mediaItem->id,
            'url' => $mediaItem->url,
            'fullUrl' => $mediaItem->full_url,
            'mediaType' => $mediaItem->media_type->value,
            'isMain' => $mediaItem->is_main->value,
        ])->toArray();
        $invntory = $this->inventory ? [
            'quantity' => $this->inventory->quantity,
            'stockStatus' => $this->stockStatus
        ] : [];
        return [
            'productId' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'slug' => $this->slug,
            'brand' => $brand,
            'category' => $category,
            'media' => $media,
            'hasStock' => $this->has_stock,
            'cost' => $this->cost,
            'price' => $this->price,
            'status' => $this->status,
            'minStock' => $this->min_stock,
            'inventory' => $invntory,
        ];
    }
}
