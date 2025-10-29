<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class FileUploadService
{
    protected $allowedTypes;
    protected $maxSize;
    protected $disk;

    public function __construct()
    {
        $this->allowedTypes = explode(',', env('ALLOWED_IMAGE_TYPES', 'jpeg,jpg,png,gif,webp'));
        $this->maxSize = env('MAX_UPLOAD_SIZE', 2048) * 1024; // Convert KB to bytes
        $this->disk = env('FILESYSTEM_DISK', 'public');
    }

    /**
     * Upload and process an image file
     */
    public function uploadImage(UploadedFile $file, string $directory = 'products'): array
    {
        // Validate file
        $this->validateFile($file);

        // Generate unique filename
        $filename = $this->generateFilename($file);
        $path = $directory . '/' . $filename;

        // Process and save image
        $processedImage = $this->processImage($file);

        // Store the processed image
        Storage::disk($this->disk)->put($path, $processedImage);

        // Generate thumbnail
        $thumbnailPath = $this->generateThumbnail($file, $directory, $filename);

        return [
            'original_name' => $file->getClientOriginalName(),
            'filename' => $filename,
            'path' => $path,
            'thumbnail_path' => $thumbnailPath,
            'url' => Storage::disk($this->disk)->url($path),
            'thumbnail_url' => Storage::disk($this->disk)->url($thumbnailPath),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ];
    }

    /**
     * Delete an uploaded file and its thumbnail
     */
    public function deleteImage(string $path, string $thumbnailPath = null): bool
    {
        $deleted = Storage::disk($this->disk)->delete($path);

        if ($thumbnailPath) {
            Storage::disk($this->disk)->delete($thumbnailPath);
        }

        return $deleted;
    }

    /**
     * Validate uploaded file
     */
    protected function validateFile(UploadedFile $file): void
    {
        // Check file size
        if ($file->getSize() > $this->maxSize) {
            throw new \InvalidArgumentException('File size exceeds maximum allowed size of ' . ($this->maxSize / 1024) . 'KB');
        }

        // Check file type
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $this->allowedTypes)) {
            throw new \InvalidArgumentException('File type not allowed. Allowed types: ' . implode(', ', $this->allowedTypes));
        }

        // Check if file is actually an image
        if (!getimagesize($file->getPathname())) {
            throw new \InvalidArgumentException('File is not a valid image');
        }
    }

    /**
     * Generate unique filename
     */
    protected function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        return Str::uuid() . '.' . $extension;
    }

    /**
     * Process image (resize, optimize)
     */
    protected function processImage(UploadedFile $file): string
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file->getPathname());

        // Resize if image is too large (max 1200px width)
        if ($image->width() > 1200) {
            $image->scale(width: 1200);
        }

        // Optimize quality for JPEG
        if (in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg'])) {
            return $image->toJpeg(85)->toString();
        }

        // For PNG, optimize
        if (strtolower($file->getClientOriginalExtension()) === 'png') {
            return $image->toPng()->toString();
        }

        // For WebP
        if (strtolower($file->getClientOriginalExtension()) === 'webp') {
            return $image->toWebp(85)->toString();
        }

        // Default: return as is
        return $image->toString();
    }

    /**
     * Generate thumbnail
     */
    protected function generateThumbnail(UploadedFile $file, string $directory, string $filename): string
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file->getPathname());

        // Create thumbnail (300x300)
        $thumbnail = $image->cover(300, 300);

        $thumbnailFilename = 'thumb_' . $filename;
        $thumbnailPath = $directory . '/thumbnails/' . $thumbnailFilename;

        // Save thumbnail
        if (in_array(strtolower($file->getClientOriginalExtension()), ['jpg', 'jpeg'])) {
            $thumbnailData = $thumbnail->toJpeg(80)->toString();
        } elseif (strtolower($file->getClientOriginalExtension()) === 'png') {
            $thumbnailData = $thumbnail->toPng()->toString();
        } elseif (strtolower($file->getClientOriginalExtension()) === 'webp') {
            $thumbnailData = $thumbnail->toWebp(80)->toString();
        } else {
            $thumbnailData = $thumbnail->toString();
        }

        Storage::disk($this->disk)->put($thumbnailPath, $thumbnailData);

        return $thumbnailPath;
    }

    /**
     * Get file URL
     */
    public function getFileUrl(string $path): string
    {
        return Storage::disk($this->disk)->url($path);
    }

    /**
     * Check if file exists
     */
    public function fileExists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }
}
