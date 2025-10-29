<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocalizedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        // Add locale information to the response
        $data['_locale'] = [
            'current' => app()->getLocale(),
            'direction' => app()->getLocale() === 'ar' ? 'rtl' : 'ltr',
            'timestamp' => now()->toISOString()
        ];

        return $data;
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'locale' => app()->getLocale(),
                'direction' => app()->getLocale() === 'ar' ? 'rtl' : 'ltr',
                'available_locales' => config('app.available_locales', ['ar', 'en'])
            ]
        ];
    }
}
