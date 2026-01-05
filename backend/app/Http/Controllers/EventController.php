<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Jobs\ProcessEventImage;

class EventController extends Controller
{
    public function index()
    {
        // Anyone can view events (public listing)
        $events = Event::with('ticketTypes')->get();
        foreach ($events as $ev) {
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

        // Dispatch job to process image if URL is provided
        if (!empty($event->image_url)) {
            ProcessEventImage::dispatch($event);
        }

        return response()->json($event->load('ticketTypes'), 201);
    }

    public function show($id)
    {
        $ev = Event::with('ticketTypes')->findOrFail($id);
        
        // Anyone can view events (public)
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

        // Dispatch job to process image if URL is changed or provided
        if ((isset($validated['image_url']) && !empty($validated['image_url'])) || !empty($event->image_url)) {
             // Re-process just in case it was updated to a new external URL
             ProcessEventImage::dispatch($event);
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
