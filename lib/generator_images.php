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
                rex_media_manager::create($type, $image);
            }
        }
        return $items;
    }
}
