<?php

/**
 * Class cache_warmup_generator_images.
 */
class cache_warmup_generator_images extends cache_warmup_generator
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

                if (method_exists('rex_media_manager', 'create')) {
                    rex_media_manager::create($type, $image);
                } else {
                    // use fallback for media_manager < 2.3.0
                    cache_warmup_media_manager::init($image, $type);
                }
            }
        }
        return $items;
    }
}
