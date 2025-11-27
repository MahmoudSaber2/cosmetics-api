<?php

namespace App\Http\Resources\V1\Order;

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
            'orderId' => $this->id,
            'number' => $this->number,
            'totalCost' => $this->total_cost,
            'totalAmount' => $this->total_amount,
            'status' => $this->status,
            'totalAmountAfterDiscount' => $this->total_after_discount,
            'discount' => $this->discount,
            'discountType' => $this->discount_type,
            'client' => [
                'clientId' => $this->client->id,
                'name' => $this->client->name,
                'email' => $this->client->email,
                'phone' => $this->client->phone,
                'address' => $this->client->address,
                'city' => $this->client->city,
            ],
            'orderItems' => OrderItemResource::collection($this->whenLoaded('orderItems')),
            'createdAt' => Carbon::parse($this->created_at)->translatedFormat('d/m/y h:i A'),
            'rejectionReason' => $this->rejection_reason??''
        ];
    }
}
