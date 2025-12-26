<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index()
    {
        // Anyone can view events (public listing)
        $events = Event::with('ticketTypes')->get();
        foreach ($events as $ev) {
            $this->ensureLocalImage($ev);
            // Normalize image URL to go through Laravel route for CORS headers
            // Use public URL from environment variable for browser access
            if (!empty($ev->image_url)) {
                try {
                    $path = parse_url($ev->image_url, PHP_URL_PATH) ?: null;
                    if ($path && Str::startsWith($path, '/uploads/')) {
                        // Use public URL from environment variable for browser access
                        // Fallback to localhost with port from request if not set
                        $publicUrl = env('BACKEND_PUBLIC_URL');
                        if (empty($publicUrl)) {
                            $port = request()->getPort() ?: 8000;
                            $publicUrl = 'http://localhost:' . $port;
                        }
                        $publicUrl = rtrim($publicUrl, '/');
                        $ev->image_url = $publicUrl . $path;
                    } elseif ($path) {
                        $publicUrl = env('BACKEND_PUBLIC_URL');
                        if (empty($publicUrl)) {
                            $port = request()->getPort() ?: 8000;
                            $publicUrl = 'http://localhost:' . $port;
                        }
                        $publicUrl = rtrim($publicUrl, '/');
                        $ev->image_url = $publicUrl . $path;
                    }
                } catch (\Exception $e) {
                    // ignore and keep existing URL
                }
            }
        }
        return $events;
    }

    public function store(Request $request)
    {
        // Check if user has permission to create events
        $this->authorize('create', Event::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url',
            'location' => 'nullable|string',
            'start_time' => 'nullable|date',
            'tickets_available' => 'nullable|integer|min:0',
            'tickets' => 'array',
            'tickets.*.name' => 'required|string',
            'tickets.*.price' => 'required|numeric|min:0',
        ]);

        // Set the user_id to the authenticated user
        $validated['user_id'] = $request->user()->id;

        // Set random tickets_available if not provided (100-900)
        if (!isset($validated['tickets_available']) || $validated['tickets_available'] === null) {
            $validated['tickets_available'] = rand(100, 900);
        }

        $event = Event::create($validated);

        if (isset($validated['tickets'])) {
            foreach ($validated['tickets'] as $ticketData) {
                $event->ticketTypes()->create($ticketData);
            }
        }

        return response()->json($event->load('ticketTypes'), 201);
    }

    public function show($id)
    {
        $ev = Event::with('ticketTypes')->findOrFail($id);
        
        // Anyone can view events (public)
        $this->ensureLocalImage($ev);
        // Normalize image URL to go through Laravel route for CORS headers
        // Use public URL from environment variable for browser access
        if (!empty($ev->image_url)) {
            try {
                $path = parse_url($ev->image_url, PHP_URL_PATH) ?: null;
                if ($path && Str::startsWith($path, '/uploads/')) {
                    // Use public URL from environment variable for browser access
                    // Fallback to localhost with port from request if not set
                    $publicUrl = env('BACKEND_PUBLIC_URL');
                    if (empty($publicUrl)) {
                        $port = request()->getPort() ?: 8000;
                        $publicUrl = 'http://localhost:' . $port;
                    }
                    $publicUrl = rtrim($publicUrl, '/');
                    $ev->image_url = $publicUrl . $path;
                } elseif ($path) {
                    $publicUrl = env('BACKEND_PUBLIC_URL');
                    if (empty($publicUrl)) {
                        $port = request()->getPort() ?: 8000;
                        $publicUrl = 'http://localhost:' . $port;
                    }
                    $publicUrl = rtrim($publicUrl, '/');
                    $ev->image_url = $publicUrl . $path;
                }
            } catch (\Exception $e) {
                // ignore
            }
        }
        return $ev;
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

        // If already local (starts with /uploads), convert to absolute URL and save.
        $imageUrl = $event->image_url;
        if (Str::startsWith($imageUrl, '/uploads')) {
            $absolute = url($imageUrl);
            if ($absolute !== $imageUrl) {
                $event->image_url = $absolute;
                $event->save();
            }
            return;
        }
        // If already absolute with app URL, skip
        if (Str::startsWith($imageUrl, url('/uploads'))) {
            return;
        }

        // SECURITY: Validate URL before fetching to prevent SSRF attacks
        if (!$this->isUrlSafeToFetch($imageUrl)) {
            \Log::warning('Blocked potentially unsafe image URL: ' . $imageUrl);
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
                    \Log::warning('Invalid content type for image URL: ' . $contentType);
                    return;
                }

                // Limit file size (5MB max)
                $body = $resp->body();
                if (strlen($body) > 5 * 1024 * 1024) {
                    \Log::warning('Image too large: ' . strlen($body) . ' bytes');
                    return;
                }

                $dir = public_path('uploads/images');
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }

                // Determine extension from content type, not URL (safer)
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
                $event->image_url = url('/uploads/images/' . $filename);
                $event->save();
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching image: ' . $e->getMessage());
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

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $event = Event::findOrFail($id);
        
        // Check if user has permission to update this event
        $this->authorize('update', $event);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url',
            'location' => 'nullable|string',
            'start_time' => 'nullable|date',
            'tickets_available' => 'nullable|integer|min:0',
            'tickets' => 'array',
            'tickets.*.name' => 'required|string',
            'tickets.*.price' => 'required|numeric|min:0',
        ]);

        $event->update($validated);

        // Update ticket types if provided
        if (isset($validated['tickets'])) {
            $event->ticketTypes()->delete();
            foreach ($validated['tickets'] as $ticketData) {
                $event->ticketTypes()->create($ticketData);
            }
        }

        return response()->json($event->load('ticketTypes'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $event = Event::findOrFail($id);
        
        // Check if user has permission to delete this event
        $this->authorize('delete', $event);

        $event->delete();

        return response()->json(['message' => 'Event deleted successfully'], 200);
    }
}
