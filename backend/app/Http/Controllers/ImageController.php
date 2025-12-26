<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    /**
     * Allowed resize widths to prevent abuse
     */
    private array $allowedWidths = [100, 200, 400, 600, 800, 1200, 1600];

    /**
     * Serve resized images with caching
     * URL format: /images/{width}/{filename}
     * Example: /images/800/event-1.jpg
     */
    public function serve(Request $request, int $width, string $filename)
    {
        // Validate width
        if (!in_array($width, $this->allowedWidths)) {
            $width = $this->findClosestWidth($width);
        }

        // Security: sanitize filename
        $filename = basename($filename);
        if (!preg_match('/^[a-zA-Z0-9_\-]+\.(jpg|jpeg|png|gif|webp)$/i', $filename)) {
            abort(400, 'Invalid filename');
        }

        $originalPath = public_path('uploads/images/' . $filename);
        $cachePath = storage_path('app/image-cache/' . $width . '/' . $filename);

        // Check if original exists
        if (!file_exists($originalPath)) {
            abort(404, 'Image not found');
        }

        // Check if GD is available
        if (!function_exists('imagecreatefromjpeg')) {
            // GD not available, serve original
            return $this->serveImage($originalPath);
        }

        // Check cache first
        if (file_exists($cachePath)) {
            $cacheTime = filemtime($cachePath);
            $originalTime = filemtime($originalPath);

            // Serve cached version if it's newer than original
            if ($cacheTime >= $originalTime) {
                return $this->serveImage($cachePath);
            }
        }

        // Create resized image
        $resizedImage = $this->resizeImage($originalPath, $width);
        if ($resizedImage === null) {
            // Fallback to original if resize fails
            return $this->serveImage($originalPath);
        }

        // Ensure cache directory exists
        $cacheDir = dirname($cachePath);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        // Save to cache
        file_put_contents($cachePath, $resizedImage);

        return Response::make($resizedImage, 200, [
            'Content-Type' => 'image/jpeg',
            'Access-Control-Allow-Origin' => '*',
            'Cache-Control' => 'public, max-age=31536000',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Find the closest allowed width
     */
    private function findClosestWidth(int $requested): int
    {
        $closest = $this->allowedWidths[0];
        foreach ($this->allowedWidths as $width) {
            if (abs($width - $requested) < abs($closest - $requested)) {
                $closest = $width;
            }
        }
        return $closest;
    }

    /**
     * Resize image using GD library
     */
    private function resizeImage(string $path, int $targetWidth): ?string
    {
        $info = getimagesize($path);
        if ($info === false) {
            return null;
        }

        [$originalWidth, $originalHeight, $type] = $info;

        // Don't upscale
        if ($originalWidth <= $targetWidth) {
            return file_get_contents($path);
        }

        // Calculate new height maintaining aspect ratio
        $ratio = $targetWidth / $originalWidth;
        $targetHeight = (int) round($originalHeight * $ratio);

        // Create source image based on type
        $source = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG => imagecreatefrompng($path),
            IMAGETYPE_GIF => imagecreatefromgif($path),
            IMAGETYPE_WEBP => imagecreatefromwebp($path),
            default => null,
        };

        if ($source === null || $source === false) {
            return null;
        }

        // Create destination image
        $destination = imagecreatetruecolor($targetWidth, $targetHeight);
        if ($destination === false) {
            imagedestroy($source);
            return null;
        }

        // Preserve transparency for PNG and GIF
        if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_GIF) {
            imagealphablending($destination, false);
            imagesavealpha($destination, true);
            $transparent = imagecolorallocatealpha($destination, 0, 0, 0, 127);
            imagefill($destination, 0, 0, $transparent);
        }

        // Resize
        imagecopyresampled(
            $destination,
            $source,
            0, 0, 0, 0,
            $targetWidth, $targetHeight,
            $originalWidth, $originalHeight
        );

        // Output to string
        ob_start();
        imagejpeg($destination, null, 85); // 85% quality for good balance
        $result = ob_get_clean();

        // Cleanup
        imagedestroy($source);
        imagedestroy($destination);

        return $result ?: null;
    }

    /**
     * Serve an image file with proper headers
     */
    private function serveImage(string $path)
    {
        $type = mime_content_type($path) ?: 'image/jpeg';
        $content = file_get_contents($path);

        return Response::make($content, 200, [
            'Content-Type' => $type,
            'Access-Control-Allow-Origin' => '*',
            'Cache-Control' => 'public, max-age=31536000',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}

