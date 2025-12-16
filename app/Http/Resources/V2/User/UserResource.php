<?php

namespace App\Http\Resources\V2\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'userId' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone??'',
            'address' => $this->address??'',
            'status' => $this->status,
            'roleId' => $this->roles->first()->id,
        ];
    }
}
