<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Translation;
use Illuminate\Support\Facades\Cache;

class TranslationController extends Controller
{
    /**
     * Get translations for a specific locale
     *
     * @param Request $request
     * @param string $locale
     * @return JsonResponse
     */
    public function index(Request $request, string $locale = 'en'): JsonResponse
    {
        // Validate locale
        $allowedLocales = ['en', 'et'];
        if (!in_array($locale, $allowedLocales)) {
            return response()->json([
                'error' => 'Invalid locale. Supported locales: ' . implode(', ', $allowedLocales)
            ], 400);
        }

        try {
            // Cache translations for 1 hour
            $translations = Cache::remember("translations.{$locale}", 3600, function () use ($locale) {
                return Translation::getNestedByLocale($locale);
            });

            // Return translations with cache headers
            return response()->json($translations)
                ->header('Cache-Control', 'public, max-age=3600')
                ->header('Content-Type', 'application/json');
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load translations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all available locales
     *
     * @return JsonResponse
     */
    public function locales(): JsonResponse
    {
        $locales = [];
        $langPath = resource_path('lang');

        if (File::exists($langPath)) {
            $files = File::files($langPath);
            foreach ($files as $file) {
                if ($file->getExtension() === 'json') {
                    $locale = $file->getFilenameWithoutExtension();
                    $locales[] = [
                        'code' => $locale,
                        'name' => $this->getLocaleName($locale),
                        'flag' => $this->getLocaleFlag($locale)
                    ];
                }
            }
        }

        return response()->json($locales);
    }

    /**
     * Get human-readable locale name
     */
    private function getLocaleName(string $locale): string
    {
        return match ($locale) {
            'en' => 'English',
            'et' => 'Eesti',
            default => ucfirst($locale)
        };
    }

    /**
     * Get locale flag emoji
     */
    private function getLocaleFlag(string $locale): string
    {
        return match ($locale) {
            'en' => '🇬🇧',
            'et' => '🇪🇪',
            default => '🌐'
        };
    }
}

