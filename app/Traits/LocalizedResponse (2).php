<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait LocalizedResponse
{
    /**
     * Return a localized success response
     */
    protected function successResponse($data = null, string $messageKey = 'messages.success', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => __($messageKey),
            'data' => $data,
            'locale' => app()->getLocale(),
            'direction' => app()->getLocale() === 'ar' ? 'rtl' : 'ltr'
        ], $status);
    }

    /**
     * Return a localized error response
     */
    protected function errorResponse(string $messageKey = 'messages.error', $errors = null, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => __($messageKey),
            'errors' => $errors,
            'locale' => app()->getLocale(),
            'direction' => app()->getLocale() === 'ar' ? 'rtl' : 'ltr'
        ], $status);
    }

    /**
     * Return a localized validation error response
     */
    protected function validationErrorResponse($errors, string $messageKey = 'validation.failed'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => __($messageKey),
            'errors' => $errors,
            'locale' => app()->getLocale(),
            'direction' => app()->getLocale() === 'ar' ? 'rtl' : 'ltr'
        ], 422);
    }

    /**
     * Return a localized not found response
     */
    protected function notFoundResponse(string $messageKey = 'messages.not_found'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => __($messageKey),
            'locale' => app()->getLocale(),
            'direction' => app()->getLocale() === 'ar' ? 'rtl' : 'ltr'
        ], 404);
    }
}
