<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FileUploadRequest;
use App\Services\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Upload a file
     */
    public function upload(FileUploadRequest $request): JsonResponse
    {
        try {
            $directory = $request->input('directory', 'products');
            $file = $request->file('file');

            $uploadResult = $this->fileUploadService->uploadImage($file, $directory);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => $uploadResult
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload file'
            ], 500);
        }
    }

    /**
     * Delete a file
     */
    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string',
            'thumbnail_path' => 'sometimes|string'
        ]);

        try {
            $deleted = $this->fileUploadService->deleteImage(
                $request->input('path'),
                $request->input('thumbnail_path')
            );

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'File deleted successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'File not found or could not be deleted'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete file'
            ], 500);
        }
    }

    /**
     * Get file information
     */
    public function info(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string'
        ]);

        try {
            $path = $request->input('path');

            if (!$this->fileUploadService->fileExists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'path' => $path,
                    'url' => $this->fileUploadService->getFileUrl($path),
                    'exists' => true
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get file information'
            ], 500);
        }
    }
}
