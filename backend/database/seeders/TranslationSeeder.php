<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Translation;

class TranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing translations
        Translation::truncate();

        // Load translations from JSON files
        $locales = ['en', 'et'];

        foreach ($locales as $locale) {
            $filePath = resource_path("lang/{$locale}.json");

            if (!file_exists($filePath)) {
                continue;
            }

            $translations = json_decode(file_get_contents($filePath), true);

            $this->seedTranslations($locale, $translations);
        }
    }

    /**
     * Recursively seed translations
     */
    private function seedTranslations(string $locale, array $translations, string $prefix = ''): void
    {
        foreach ($translations as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $this->seedTranslations($locale, $value, $fullKey);
            } else {
                Translation::create([
                    'locale' => $locale,
                    'key' => $fullKey,
                    'value' => $value,
                ]);
            }
        }
    }
}
