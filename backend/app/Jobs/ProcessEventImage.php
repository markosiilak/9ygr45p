<?php

namespace App\Jobs;

use App\Models\Event;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ProcessEventImage implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Event $event)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->ensureLocalImage($this->event);
    }

    /**
     * Ensure the event's image_url points to a local file in public/uploads/images.
     * If image_url is an external URL, attempt to download and save it locally.
     */
    private function ensureLocalImage(Event $event)
    {
        if (empty($event->image_url)) {
            return;
        }

        // If already local (starts with /uploads), skip
        $imageUrl = $event->image_url;
        if (Str::startsWith($imageUrl, '/uploads') || Str::contains($imageUrl, '/uploads/images/')) {
            return;
        }
        
        // Use Laravel's 'url' helper to check if it matches our own base URL
        if (Str::startsWith($imageUrl, url('/'))) {
             return;
        }

        // SECURITY: Validate URL before fetching to prevent SSRF attacks
        if (!$this->isUrlSafeToFetch($imageUrl)) {
            Log::warning('Blocked potentially unsafe image URL: ' . $imageUrl);
            return;
        }

        try {
            // Add timeout and size limits to prevent abuse
            $resp = Http::timeout(10)
                ->withOptions(['allow_redirects' => ['max' => 2]])
                ->get($imageUrl);

            if ($resp->ok()) {
                // Validate content type is actually an image
                $contentType = $resp->header('Content-Type') ?? '';
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

                if (!Str::startsWith($contentType, $allowedTypes)) {
                    Log::warning('Invalid content type for image URL: ' . $contentType);
                    return;
                }

                // Limit file size (5MB max)
                $body = $resp->body();
                if (strlen($body) > 5 * 1024 * 1024) {
                    Log::warning('Image too large: ' . strlen($body) . ' bytes');
                    return;
                }

                $dir = public_path('uploads/images');
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }

                // Determine extension from content type
                $extMap = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/webp' => 'webp',
                ];
                $ext = 'jpg';
                foreach ($extMap as $mime => $extension) {
                    if (Str::startsWith($contentType, $mime)) {
                        $ext = $extension;
                        break;
                    }
                }

                $filename = "event-{$event->id}.{$ext}";
                $path = $dir . DIRECTORY_SEPARATOR . $filename;
                file_put_contents($path, $body);

                // Update model and save so future requests use local path
                // We store the relative path or absolute path. 
                // The original controller used absolute `url(...)`. We stick to that.
                $event->image_url = url('/uploads/images/' . $filename);
                $event->saveQuietly(); // Use saveQuietly to avoid dispatching events/loops if any observers exist
            }
        } catch (\Exception $e) {
            Log::error('Error fetching image: ' . $e->getMessage());
            // Keep original image_url on error
        }
    }

    /**
     * Check if a URL is safe to fetch (prevents SSRF attacks).
     */
    private function isUrlSafeToFetch(string $url): bool
    {
        $parsed = parse_url($url);

        if (!$parsed || !isset($parsed['host'])) {
            return false;
        }

        // Only allow HTTP and HTTPS
        $scheme = strtolower($parsed['scheme'] ?? '');
        if (!in_array($scheme, ['http', 'https'])) {
            return false;
        }

        $host = strtolower($parsed['host']);

        // Block localhost and loopback addresses
        $blockedHosts = [
            'localhost',
            '127.0.0.1',
            '::1',
            '0.0.0.0',
            '169.254.169.254', // AWS/GCP metadata endpoint
            'metadata.google.internal', // GCP metadata
            'metadata.internal', // Common cloud metadata
        ];

        if (in_array($host, $blockedHosts)) {
            return false;
        }

        // Block private IP ranges
        $ip = gethostbyname($host);
        if ($ip !== $host) { // Resolution succeeded
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                return false; // IP is private or reserved
            }
        }

        // Block internal Docker/Kubernetes hostnames
        $blockedPatterns = [
            '/^10\.\d+\.\d+\.\d+$/',
            '/^172\.(1[6-9]|2\d|3[01])\.\d+\.\d+$/',
            '/^192\.168\.\d+\.\d+$/',
            '/\.internal$/',
            '/\.local$/',
            '/^host\.docker\.internal$/',
            '/^kubernetes\.default/',
        ];

        foreach ($blockedPatterns as $pattern) {
            if (preg_match($pattern, $host) || preg_match($pattern, $ip)) {
                return false;
            }
        }

        return true;
    }
}
