<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

/**
 * Provides methods for handling file uploads and media storage.
 * 
 * @property-read \Illuminate\Filesystem\FilesystemAdapter $storage
 */
trait MediaUpload
{
    /**
     * Store a file in the specified storage disk.
     *
     * @param UploadedFile $file
     * @param string $path
     * @param string $disk
     * @return string|null
     */
    public function storeMedia(UploadedFile $file, string $path = 'uploads', string $disk = 'public'): ?string
    {
        try {
            // Generate a unique filename with original extension
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            
            // Store the file
            $filePath = $file->storeAs($path, $filename, $disk);
            
            return $filePath ?: null;
        } catch (Exception $e) {
            Log::error('File upload failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
                'path' => $path
            ]);
            return null;
        }
    }

    /**
     * Store an image with validation and optional resizing.
     *
     * @param UploadedFile $file
     * @param string $path
     * @param array $dimensions
     * @return string|null
     */
    public function storeImage(UploadedFile $file, string $path = 'images', array $dimensions = []): ?string
    {
        // Validate mime type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            Log::warning('Invalid image type uploaded', [
                'mime_type' => $file->getMimeType(),
                'file' => $file->getClientOriginalName()
            ]);
            return null;
        }

        try {
            // If dimensions are specified and Intervention Image is available, resize the image
            if (!empty($dimensions) && class_exists('Intervention\Image\Facades\Image')) {
                $image = \Intervention\Image\Facades\Image::make($file->getRealPath());
                
                if (isset($dimensions['width']) && isset($dimensions['height'])) {
                    $image->fit($dimensions['width'], $dimensions['height']);
                } elseif (isset($dimensions['width'])) {
                    $image->widen($dimensions['width'], function ($constraint) {
                        $constraint->upsize();
                    });
                } elseif (isset($dimensions['height'])) {
                    $image->heighten($dimensions['height'], function ($constraint) {
                        $constraint->upsize();
                    });
                }

                // Generate filename
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $fullPath = storage_path("app/public/{$path}/{$filename}");
                
                // Ensure directory exists
                if (!file_exists(dirname($fullPath))) {
                    mkdir(dirname($fullPath), 0755, true);
                }

                // Save the resized image
                $image->save($fullPath);
                
                return "{$path}/{$filename}";
            }

            // If no resizing needed or Intervention Image is not available, store original
            return $this->storeMedia($file, $path);

        } catch (Exception $e) {
            Log::error('Image upload failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
                'path' => $path
            ]);
            return null;
        }
    }

    /**
     * Delete a file from storage.
     *
     * @param string $path
     * @param string $disk
     * @return bool
     */
    public function deleteMedia(string $path, string $disk = 'public'): bool
    {
        try {
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->delete($path);
            }
            return false;
        } catch (Exception $e) {
            Log::error('File deletion failed', [
                'error' => $e->getMessage(),
                'path' => $path,
                'disk' => $disk
            ]);
            return false;
        }
    }

    /**
     * Get the full URL for a stored file.
     *
     * @param string|null $path
     * @param string $disk
     * @return string|null
     */
    public function getMediaUrl(?string $path, string $disk = 'public'): ?string
    {
        if (empty($path)) {
            return null;
        }

        try {
            $diskDriver = Storage::disk($disk);
            
            if (!$diskDriver->exists($path)) {
                return null;
            }

            if ($disk === 'public') {
                return asset('storage/' . $path);
            }

            if (method_exists($diskDriver, 'url')) {
                return $diskDriver->url($path);
            }

            Log::warning('URL generation not supported for disk: ' . $disk);
            return null;

        } catch (Exception $e) {
            Log::error('Failed to get media URL', [
                'error' => $e->getMessage(),
                'path' => $path,
                'disk' => $disk
            ]);
            return null;
        }
    }

    /**
     * Validate file size and type.
     *
     * @param UploadedFile $file
     * @param array $options
     * @return bool
     */
    public function validateFile(UploadedFile $file, array $options = []): bool
    {
        $maxSize = $options['max_size'] ?? 5120; // Default 5MB
        $allowedTypes = $options['allowed_types'] ?? ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];

        // Check file size (in kilobytes)
        if ($file->getSize() / 1024 > $maxSize) {
            return false;
        }

        // Check file type
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            return false;
        }

        return true;
    }
}
