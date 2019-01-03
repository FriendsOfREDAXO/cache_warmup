<?php

/**
 * Class cache_warmup_generator_images
 */
class cache_warmup_generator_images extends cache_warmup_generator
{

    /**
     * Generate cache for given items
     *
     * @param array $items
     * @return array
     * @throws rex_sql_exception
     */
    public function generateCache(array $items)
    {
        if (rex_addon::get('media_manager')->isAvailable()) {

            // filter image ids
            $imageIds = array_column($items, 0);
            $imageIds = array_filter($imageIds, function($v) {
                return preg_match('/^\d+$/', $v) && intval($v) > 0; // sanitize
            });
            $imageIds = array_unique($imageIds);

            // find image names by id
            $imageFilenames = array();
            $sql = rex_sql::factory();
            $sql->setQuery('SELECT filename FROM ' . rex::getTablePrefix() . 'media WHERE id IN (' . implode(',', $imageIds) . ')');
            foreach ($sql as $row) {
                $imageFilenames[] = $row->getValue('filename');
            }

            // filter media types
            $mediaTypes = array_column($items, 1);
            $mediaTypes = array_unique($mediaTypes);

            // generate image cache
            foreach ($imageFilenames as $image) {
                $media = rex_media::get($image);
                if ($media instanceof rex_media && $media->isImage()) {
                    foreach ($mediaTypes as $type) {
                        if (method_exists('rex_media_manager', 'create')) {
                            rex_media_manager::create($type, $image);
                        } else {
                            // use fallback for media_manager < 2.3.0
                            cache_warmup_media_manager::init($image, $type);
                        }
                    }
                }
            }
        }
        return $items;
    }
}
