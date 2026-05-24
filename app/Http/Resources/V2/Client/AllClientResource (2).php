<?php

namespace App\Http\Resources\V2\Client;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'clientId' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone??'',
            'address' => $this->address??'',
            'city' => $this->city??'',
        ];
    }
}
