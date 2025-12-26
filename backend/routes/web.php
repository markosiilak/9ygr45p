<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\ImageController;

// Serve resized images on-the-fly with caching
// URL format: /images/{width}/{filename}
// Example: /images/800/event-1.jpg for 800px wide image
Route::get('/images/{width}/{filename}', [ImageController::class, 'serve'])
    ->where('width', '[0-9]+')
    ->where('filename', '[a-zA-Z0-9_\-]+\.(jpg|jpeg|png|gif|webp)');

// Serve uploaded images with CORS headers (original size)
// This route must come BEFORE the root route to catch upload requests
Route::get('/uploads/{path}', function ($path) {
    try {
        // SECURITY: Prevent directory traversal attacks
        // Remove any path traversal attempts
        $path = str_replace(['../', '..\\', '..'], '', $path);

        // Only allow specific subdirectories
        $allowedPrefixes = ['images/'];
        $isAllowed = false;
        foreach ($allowedPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $isAllowed = true;
                break;
            }
        }

        // If no subdirectory, check if it's just a filename (legacy support)
        if (!$isAllowed && !str_contains($path, '/')) {
            $isAllowed = true;
        }

        if (!$isAllowed) {
            abort(403, 'Access denied');
        }

        // Validate filename doesn't contain suspicious characters
        if (preg_match('/[<>:"|?*\x00-\x1f]/', $path)) {
            abort(400, 'Invalid filename');
        }

        $filePath = public_path('uploads/' . $path);

        // Ensure the resolved path is within the uploads directory
        $realPath = realpath($filePath);
        $uploadsDir = realpath(public_path('uploads'));

        if ($realPath === false || !str_starts_with($realPath, $uploadsDir)) {
            abort(403, 'Access denied');
        }

        if (!file_exists($realPath)) {
            abort(404, 'File not found');
        }

        // Only allow image files
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        $type = mime_content_type($realPath) ?: 'application/octet-stream';

        if (!in_array($type, $allowedMimes)) {
            abort(403, 'File type not allowed');
        }

        $file = file_get_contents($realPath);
        if ($file === false) {
            abort(500, 'Failed to read file');
        }

        return Response::make($file, 200, [
            'Content-Type' => $type,
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
            'Cache-Control' => 'public, max-age=31536000',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        throw $e;
    } catch (\Exception $e) {
        \Log::error('Error serving upload: ' . $e->getMessage());
        abort(500, 'Error serving file');
    }
})->where('path', '[a-zA-Z0-9_\-./]+'); // Only allow safe characters

Route::get('/', function () {
    return view('welcome');
});
