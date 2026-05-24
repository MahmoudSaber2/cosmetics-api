<?php

namespace App\Http\Resources\V2\Website;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'orderNumber' => $this->number,
            'status' => $this->status->value,
            'statusLabel' => $this->getStatusLabel(),
            'totalAmount' => $this->total_amount,
            'totalAfterDiscount' => $this->total_after_discount,
            'discount' => $this->discount,
            'note' => $this->note,
            'createdAt' => Carbon::parse($this->created_at)->format('Y-m-d H:i:s'),
            'createdAtFormatted' => Carbon::parse($this->created_at)->translatedFormat('d F Y - h:i A'),

            'client' => $this->when($this->client, [
                'name' => $this->client?->name,
                'email' => $this->client?->email,
                'phone' => $this->client?->phone,
                'address' => $this->client?->address,
                'city' => $this->client?->city,
            ]),

            'items' => OrderItemResource::collection($this->whenLoaded('orderItems')),
            'itemsCount' => $this->orderItems?->count() ?? 0,
        ];
    }

    /**
     * Get status label in Arabic
     */
    private function getStatusLabel(): string
    {
        return match($this->status->value) {
            'pending' => 'في الانتظار',
            'approved' => 'مؤكد',
            'rejected' => 'مرفوض',
            'completed' => 'مكتمل',
            default => $this->status->value,
        };
    }
}
