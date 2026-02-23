<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Illuminate\Http\UploadedFile;

class CloudinaryService
{
    private Cloudinary $cloudinary;

    public function __construct()
    {
        $url = config('cloudinary.url');
        if ($url) {
            $this->cloudinary = new Cloudinary($url);
        } else {
            $this->cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => config('cloudinary.cloud_name'),
                    'api_key' => config('cloudinary.api_key'),
                    'api_secret' => config('cloudinary.api_secret'),
                ],
            ]);
        }
    }

    /**
     * Upload an image file to Cloudinary. Returns the secure URL or null on failure.
     */
    public function uploadImage(UploadedFile $file, string $folder = null): ?string
    {
        $folder = $folder ?? config('cloudinary.folders.events', 'metaseat/events');
        $path = $file->getRealPath();
        $result = $this->cloudinary->uploadApi()->upload($path, [
            'folder' => $folder,
            'resource_type' => 'image',
            'unique_filename' => true,
        ]);
        return $this->getSecureUrl($result);
    }

    /**
     * Upload a video file to Cloudinary. Returns the secure URL or null on failure.
     */
    public function uploadVideo(UploadedFile $file, string $folder = null): ?string
    {
        $folder = $folder ?? config('cloudinary.folders.recordings', 'metaseat/recordings');
        $path = $file->getRealPath();
        $result = $this->cloudinary->uploadApi()->upload($path, [
            'folder' => $folder,
            'resource_type' => 'video',
            'unique_filename' => true,
        ]);
        return $this->getSecureUrl($result);
    }

    /**
     * Upload a thumbnail image for recordings. Returns the secure URL or null on failure.
     */
    public function uploadThumbnail(UploadedFile $file): ?string
    {
        $folder = config('cloudinary.folders.recordings_thumbnails', 'metaseat/recordings/thumbnails');
        return $this->uploadImage($file, $folder);
    }

    private function getSecureUrl($result): ?string
    {
        if (is_array($result) && isset($result['secure_url'])) {
            return $result['secure_url'];
        }
        if (is_object($result) && isset($result->secure_url)) {
            return $result->secure_url;
        }
        return null;
    }

    /**
     * Check if Cloudinary is configured (so we can use it for uploads).
     */
    public static function isConfigured(): bool
    {
        if (config('cloudinary.url')) {
            return true;
        }
        return config('cloudinary.cloud_name') && config('cloudinary.api_key') && config('cloudinary.api_secret');
    }
}
