<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LocaleController extends Controller
{
    /**
     * Get available locales
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'available_locales' => config('app.available_locales', ['ar', 'en']),
                'current_locale' => app()->getLocale(),
                'locales' => [
                    'ar' => [
                        'code' => 'ar',
                        'name' => 'العربية',
                        'direction' => 'rtl',
                        'flag' => '🇸🇦'
                    ],
                    'en' => [
                        'code' => 'en',
                        'name' => 'English',
                        'direction' => 'ltr',
                        'flag' => '🇺🇸'
                    ]
                ]
            ]
        ]);
    }

    /**
     * Get translations for a specific key or all translations
     */
    public function translations(Request $request): JsonResponse
    {
        $key = $request->get('key');
        $file = $request->get('file', 'messages');

        if ($key) {
            // Get specific translation
            $translation = __($key);
            return response()->json([
                'success' => true,
                'data' => [
                    'key' => $key,
                    'translation' => $translation,
                    'locale' => app()->getLocale()
                ]
            ]);
        }

        // Get all translations for a file
        $translations = trans($file);

        return response()->json([
            'success' => true,
            'data' => [
                'file' => $file,
                'translations' => $translations,
                'locale' => app()->getLocale()
            ]
        ]);
    }

    /**
     * Get validation messages in current locale
     */
    public function validationMessages(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'validation' => trans('validation'),
                'locale' => app()->getLocale()
            ]
        ]);
    }

    /**
     * Get authentication messages in current locale
     */
    public function authMessages(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'auth' => trans('auth'),
                'locale' => app()->getLocale()
            ]
        ]);
    }
}
