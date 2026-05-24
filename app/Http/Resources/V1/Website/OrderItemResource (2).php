<?php

namespace App\Http\Resources\V1\Website;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'unitPrice' => $this->price,
            'totalPrice' => $this->total_price,

            'product' => $this->when($this->product, [
                'id' => $this->product?->id,
                'name' => $this->product?->name,
                'slug' => $this->product?->slug,
                'image' => $this->when($this->product?->media, [
                    'url' => $this->product?->media?->url,
                    'type' => $this->product?->media?->media_type,
                ]),
                'brand' => $this->when($this->product?->brand, [
                    'id' => $this->product?->brand?->id,
                    'name' => $this->product?->brand?->name,
                ]),
                'category' => $this->when($this->product?->category, [
                    'id' => $this->product?->category?->id,
                    'name' => $this->product?->category?->name,
                    'slug' => $this->product?->category?->slug,
                ]),
            ]),
        ];
    }
}
