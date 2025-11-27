<?php

namespace App\Http\Resources\V1\User;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllUserResource extends JsonResource
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
            'status' => $this->status,
            'roleName' => $this->roles->first()->name,
            'createdAt' => Carbon::parse($this->created_at)->translatedFormat('d/m/Y h:i A'),
        ];
    }
}
