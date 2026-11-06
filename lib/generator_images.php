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
        if ($items && !rex_addon::get('media_manager')->isAvailable()) {
            throw new RuntimeException('The media_manager AddOn is not available');
        }
        if (rex_addon::get('media_manager')->isAvailable()) {
            foreach ($items as $item) {
                [$image, $type] = $item;
                $profile = $item[2] ?? 'original';
                cache_warmup_profiles::run($profile, static function () use ($type, $image, $profile) {
                    $manager = rex_media_manager::create($type, $image);
                    if (!$manager->isCached()) {
                        // An existing source with no effects is a legitimate uncached passthrough.
                        if (!is_file($manager->getMedia()->getSourcePath()) || $manager->effectsFromType($type)) {
                            throw new RuntimeException('Image cache missing or invalid: ' . $type . ' / ' . $image . ' (' . $profile . ')');
                        }
                    }
                });
            }
        }
        return $items;
    }
}
