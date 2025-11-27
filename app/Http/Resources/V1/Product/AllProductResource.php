<?php

namespace App\Http\Resources\V1\Product;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $brand = $this->brand ? [
            'name' => $this->brand->name,
        ] : [];
        $category = $this->category ? [
            'name' => $this->category->name,
        ] : [];
        $media = $this->media ?[
            'url' => $this->media->url,
            'mediaType' => $this->media->media_type,
        ] : [];
        $invntory = $this->inventory ? [
            'quantity' => $this->inventory->quantity,
            'stockStatus' => $this->stockStatus
        ] : [];
        return [
            'productId' => $this->id,
            'name' => $this->name,
            'brand' => $brand,
            'category' => $category,
            'media' => $media,
            'hasStock' => $this->has_stock,
            'cost' => $this->cost,
            'price' => $this->price,
            'status' => $this->status,
            'minStock' => $this->min_stock,
            'inventory' => $invntory,
            'createdAt' => Carbon::parse($this->created_at)
    ->translatedFormat('d/m/Y h:i A'),

        ];
    }
}
