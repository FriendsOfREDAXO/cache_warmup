<?php

namespace FriendsOfRedaxo\CacheWarmup;

/**
 * Class CacheWarmupGeneratorImages.
 */
class CacheWarmupGeneratorImages extends CacheWarmupGenerator
{
    /**
     * Generate cache for given items.
     *
     * @param array $items
     * @return array
     */
    public function generateCache(array $items): array
    {
        if (rex_addon::get('media_manager')->isAvailable()) {
            foreach ($items as $item) {
                [$image, $type] = $item;
                rex_media_manager::create($type, $image);
            }
        }
        return $items;
    }
}
