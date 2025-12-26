<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $fillable = [
        'locale',
        'key',
        'value',
    ];

    /**
     * Get translations by locale
     */
    public static function getByLocale(string $locale): array
    {
        return self::where('locale', $locale)
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Get nested translations by locale
     */
    public static function getNestedByLocale(string $locale): array
    {
        $translations = self::getByLocale($locale);
        $nested = [];

        foreach ($translations as $key => $value) {
            $keys = explode('.', $key);
            $current = &$nested;

            foreach ($keys as $i => $k) {
                if ($i === count($keys) - 1) {
                    $current[$k] = $value;
                } else {
                    if (!isset($current[$k]) || !is_array($current[$k])) {
                        $current[$k] = [];
                    }
                    $current = &$current[$k];
                }
            }
        }

        return $nested;
    }
}
