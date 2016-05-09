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
                foreach ($mediaTypes as $type) {
                    $media = rex_media::get($image);
                    if ($media instanceof rex_media && $media->isImage()) {
                        cache_warmup_media_manager::init($image, $type);
                    }
                }
            }
        }
        return $items;
    }
}