<?php

namespace App\Http\Resources\V2\Order;

use Carbon\Carbon;
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
            'orderItemId' => $this->id,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'totalPrice' => $this->total_price,
            'product' => [
                'product' => $this->product->id,
                'productName' => $this->product->name,
            ],
        ];
    }
}
