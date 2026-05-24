<?php

namespace App\Http\Resources\V1\ProductMedia;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductMediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'mediaId' => $this->id,
            'url' => $this->url,
            'fullUrl' => $this->full_url,
            'mediaType' => $this->media_type->value,
            'isMain' => $this->is_main->value,
            'createdAt' => Carbon::parse($this->created_at)->translatedFormat('d/m/y h:i A'),
        ];
    }
}
