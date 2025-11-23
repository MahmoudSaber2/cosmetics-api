<?php

namespace App\Http\Resources\V1\Order;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllOrderResource extends JsonResource
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
            'client' => [
                'name' => $this->client->name,
                'email' => $this->client->email,
            ],
            'createdAt' => Carbon::parse($this->created_at)->translatedFormat('d/m/y h:i A'),
        ];
    }
}
