<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class ImportEventImages extends Command
{
    protected $signature = 'events:import-images {--replace : Delete existing local images before importing}';
    protected $description = 'Download concert/music-related images for events and save them locally';

    public function handle()
    {
        $this->info('Starting event image import...');

        $events = Event::all();
        $dir = public_path('uploads/images');

        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        // If replace flag is provided, clear existing images in the folder
        if ($this->option('replace')) {
            $this->info('Removing existing images in uploads/images...');
            $files = File::files($dir);
            foreach ($files as $f) {
                // Only remove files starting with event- or common image extensions
                if (preg_match('/event-\d+\.(jpg|jpeg|png|webp)$/i', $f->getFilename())) {
                    @unlink($f->getPathname());
                }
            }
        }

        foreach ($events as $event) {
            $this->info("Processing event {$event->id} - {$event->title}");

            $ext = 'jpg';
            $localFilename = "event-{$event->id}.{$ext}";
            $localPath = $dir . DIRECTORY_SEPARATOR . $localFilename;

            $success = false;
            $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120 Safari/537.36';

            // If an Unsplash access key is provided, prefer the Unsplash API for reliable themed images
            $unsplashKey = env('UNSPLASH_ACCESS_KEY') ?: env('UNSPLASH_KEY') ?: null;
            if ($unsplashKey) {
                $this->info('Using Unsplash API for themed images');
                try {
                    // Use the search endpoint to find a concert/music photo
                    $page = ($event->id % 10) + 1; // vary page for more diversity
                    $searchRes = Http::withHeaders([
                        'Authorization' => 'Client-ID ' . $unsplashKey,
                        'Accept-Version' => 'v1',
                        'User-Agent' => $userAgent,
                    ])->timeout(20)->get('https://api.unsplash.com/search/photos', [
                        'query' => 'concert music',
                        'orientation' => 'landscape',
                        'per_page' => 1,
                        'page' => $page,
                    ]);

                    if ($searchRes->successful()) {
                        $json = $searchRes->json();
                        if (!empty($json['results'][0]['urls']['regular'])) {
                            $remote = $json['results'][0]['urls']['regular'];
                            $this->info("Unsplash result: {$remote}");
                            // download via sink
                            try {
                                if (File::exists($localPath)) {@unlink($localPath);}
                                $dl = Http::withHeaders(['User-Agent' => $userAgent])->withOptions(['sink' => $localPath])->timeout(30)->get($remote);
                                if ($dl->successful() && File::exists($localPath) && filesize($localPath) > 0) {
                                    $this->info("Saved image from Unsplash: {$localFilename}");
                                    $success = true;
                                } else {
                                    $this->error("Failed to download Unsplash image for event {$event->id}");
                                    if (File::exists($localPath) && filesize($localPath) === 0) {@unlink($localPath);}
                                }
                            } catch (\Exception $e) {
                                $this->error('Error downloading Unsplash image: ' . $e->getMessage());
                            }
                        }
                    } else {
                        $this->info('Unsplash search returned HTTP ' . $searchRes->status());
                    }
                } catch (\Exception $e) {
                    $this->error('Unsplash API error: ' . $e->getMessage());
                }
            }

            // If Unsplash wasn't used or failed, fall back to public image providers
            if (!$success) {
                $remotes = [
                    'https://picsum.photos/seed/event-' . $event->id . '/800/600',
                    'https://loremflickr.com/800/600/concert,music?random=' . $event->id,
                    'https://source.unsplash.com/featured/800x600/?concert,music&sig=' . $event->id,
                ];

                foreach ($remotes as $remote) {
                    $this->info("Attempting: {$remote}");
                    if (File::exists($localPath)) {@unlink($localPath);}
                    try {
                        set_time_limit(0);
                        $response = Http::withHeaders(['User-Agent' => $userAgent])->withOptions(['sink' => $localPath, 'allow_redirects' => true])->retry(2, 800)->timeout(30)->get($remote);
                        if (!$response->successful() || !File::exists($localPath) || filesize($localPath) === 0) {
                            $this->error("Failed to fetch/save image for event {$event->id} from {$remote} (HTTP {$response->status()})");
                            if (File::exists($localPath) && filesize($localPath) === 0) {@unlink($localPath);}
                            $this->info('Response headers: ' . json_encode($response->headers() ?: []));
                            continue;
                        }
                        $this->info("Saved image: {$localFilename} ({$remote})");
                        $success = true;
                        break;
                    } catch (\Exception $e) {
                        $this->error("Exception fetching image for event {$event->id} from {$remote}: " . $e->getMessage());
                        if (File::exists($localPath)) {@unlink($localPath);}
                        continue;
                    }
                }
            }

            if (!$success) {
                $this->error("All sources failed for event {$event->id}; skipping.");
                continue;
            }

            // Update model to point to local copy
            try {
                $event->image_url = url("uploads/images/{$localFilename}");
                $event->save();
            } catch (\Exception $e) {
                $this->error("Failed to update event {$event->id}: " . $e->getMessage());
            }
        }

        $this->info('Event image import completed.');
        return 0;
    }
}
