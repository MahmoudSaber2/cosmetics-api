<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\LocalizedResponse;
use Illuminate\Http\JsonResponse;

class BaseApiController extends Controller
{
    use LocalizedResponse;

    /**
     * Success response method.
     */
    public function sendResponse($result, $message = 'Success', $code = 200): JsonResponse
    {
        // Check if message is a translation key
        $translatedMessage = str_starts_with($message, 'messages.') ? __($message) : $message;

        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $translatedMessage,
            'locale' => app()->getLocale(),
            'direction' => app()->getLocale() === 'ar' ? 'rtl' : 'ltr'
        ];

        return response()->json($response, $code);
    }

    /**
     * Error response method.
     */
    public function sendError($error, $errorMessages = [], $code = 404): JsonResponse
    {
        // Check if error is a translation key
        $translatedError = str_starts_with($error, 'messages.') ? __($error) : $error;

        $response = [
            'success' => false,
            'message' => $translatedError,
            'locale' => app()->getLocale(),
            'direction' => app()->getLocale() === 'ar' ? 'rtl' : 'ltr'
        ];

        if (!empty($errorMessages)) {
            $response['errors'] = $errorMessages;
        }

        return response()->json($response, $code);
    }
}
