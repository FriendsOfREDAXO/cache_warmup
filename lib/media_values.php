<?php

/** Extract explicit REDAXO media widgets from nested MForm/MBlock JSON values. */
abstract class cache_warmup_media_values
{
    public static function extract($value, int $depth = 0): array
    {
        if ($depth > 32) {
            return [];
        }
        if (is_string($value)) {
            // Database values can contain raw JSON or HTML-escaped JSON.
            $decoded = json_decode($value, true);
            if (!is_array($decoded)) {
                $decoded = json_decode(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'), true);
            }
            $value = $decoded;
        }
        if (!is_array($value)) {
            return [];
        }

        $images = [];
        foreach ($value as $key => $entry) {
            if (is_string($key) && preg_match('/^REX_(MEDIA|MEDIALIST)(?:_\d+|\[\d+\])$/i', $key) && is_string($entry)) {
                foreach (explode(',', $entry) as $filename) {
                    $filename = trim($filename);
                    if ('' !== $filename) {
                        $images[] = $filename;
                    }
                }
            } elseif (is_array($entry) || is_string($entry)) {
                $images = array_merge($images, self::extract($entry, $depth + 1));
            }
        }
        return array_values(array_unique($images));
    }
}
