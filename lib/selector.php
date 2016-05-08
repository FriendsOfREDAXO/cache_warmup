<?php

/**
 * Class cache_warmup_selector
 */
abstract class cache_warmup_selector
{
    /**
     * Get all images being used in REDAXO (pages, meta, yforms)
     * »X never, ever marks the spot.« (-- Indiana Jones)
     * 
     * @return array
     * @throws rex_sql_exception
     */
    public static function getImages()
    {
        if (rex_addon::get('media_manager')->isAvailable() && rex_addon::get('structure')->isAvailable()) {

            $images = array();
            $sql = rex_sql::factory();

            /* find images in pages (media1-10, medialist1-10) */

            $select = 'media1,media2,media3,media4,media5,media6,media7,media8,media9,media10,medialist1,medialist2,medialist3,medialist4,medialist5,medialist6,medialist7,medialist8,medialist9,medialist10';
            $sql->setQuery('SELECT ' . $select . ' FROM ' . rex::getTablePrefix() . 'article_slice');
            foreach ($sql as $row) {
                foreach (range(1, 10) as $num) {
                    if (!empty($row->getValue('media' . $num))) {
                        $images[] = $row->getValue('media' . $num);
                    }
                    if (!empty($row->getValue('medialist' . $num))) {
                        $files = explode(',', $row->getValue('medialist' . $num));
                        foreach ($files as $file) {
                            $images[] = $file;
                        }
                    }
                }
            }

            /* find images in yforms (be_media, be_medialist) */

            if (rex_addon::get('yform')->isAvailable()) {
                $yforms = array();

                // get tables and fields where 'be_media' and 'be_medialist' are used
                $sql->setQuery('SELECT table_name,name FROM ' . rex::getTablePrefix() . 'yform_field WHERE type_name LIKE "be_media%"');
                foreach ($sql as $row) {
                    $yforms[$row->getValue('table_name')][] = $row->getValue('name');
                }

                // walk through tables and find images
                foreach ($yforms as $table => $fields) {
                    $sql->setQuery('SELECT ' . implode(',', array_values($fields)) . ' FROM  ' . $table);
                    foreach ($sql as $row) {
                        foreach ($fields as $field) {
                            $files = $row->getValue($field);
                            if (strpos($files, ',') > 0) {
                                // is medialist
                                foreach (explode(',', $files) as $file) {
                                    $images[] = $file;
                                }
                            } else {
                                // is media
                                $images[] = $files;
                            }
                        }
                    }
                }
            }

            /* find images in metainfos (REX_MEDIA_WIDGET, REX_MEDIALIST_WIDGET) */

            if (rex_addon::get('metainfo')->isAvailable()) {
                $metainfos = array();

                // get 'REX_MEDIA_WIDGET' and 'REX_MEDIALIST_WIDGET' ids
                $sql->setQuery('SELECT id FROM ' . rex::getTablePrefix() . 'metainfo_type WHERE label LIKE "REX_MEDIA%"');
                foreach ($sql as $row) {
                    $metainfos['ids'][] = $row->getValue('id');
                }

                // get field names where 'REX_MEDIA_WIDGET' and 'REX_MEDIALIST_WIDGET' are used
                $sql->setQuery('SELECT name FROM ' . rex::getTablePrefix() . 'metainfo_field WHERE type_id IN (' . implode(',', $metainfos['ids']) . ')');
                foreach ($sql as $row) {
                    $metainfos['names'][] = $row->getValue('name');
                }

                // find images in article metas
                $sql->setQuery('SELECT ' . implode(',', $metainfos['names']) . ' FROM ' . rex::getTablePrefix() . 'article');
                foreach ($sql as $row) {
                    foreach ($metainfos['names'] as $field) {
                        $files = $row->getValue($field);
                        if (strpos($files, ',') > 0) {
                            // is medialist
                            foreach (explode(',', $files) as $file) {
                                $images[] = $file;
                            }
                        } else {
                            // is media
                            $images[] = $files;
                        }
                    }
                }
            }

            /* prepare and return ------------------------------------------------- */

            // filter images
            $images = self::filterImages($images);

            return $images;
        }
    }

    /**
     * Filter images: remove duplicate images and non-image items
     * 
     * @param array $items
     * @return array
     */
    public static function filterImages(array $items)
    {
        $filteredImages = array();

        $items = array_unique($items); // remove duplicate values

        foreach ($items as $item) {
            $media = rex_media::get($item);
            if ($media && $media->isImage()) {
                $filteredImages[] = $media->getId();
            }
        }

        return $filteredImages;
    }

    /**
     * Get all images as chunked array including 'count' and 'items'
     * 
     * @return array
     */
    public static function getChunkedImagesArray()
    {
        $items = self::getImages();
        if (count($items) > 0) {
            $chunkedItems = self::chunk($items, rex_addon::get('cache-warmup')->getConfig('chunkSizeImages'));
            return array('images' => array('count' => count($items), 'items' => $chunkedItems));
        }
    }

    /**
     * Get all media types as defined in media manager addon
     * 
     * @return array
     * @throws rex_sql_exception
     */
    public static function getMediaTypes()
    {
        if (rex_addon::get('media_manager')->isAvailable()) {
            $mediaTypes = array();

            $sql = rex_sql::factory();
            $sql->setQuery('SELECT name FROM ' . rex::getTablePrefix() . 'media_manager_type');

            foreach ($sql as $row) {
                $mediaTypes[] = $row->getValue('name');
            }
            return $mediaTypes;
        }
    }

    /**
     * Get all media types as array including 'count' and 'items'
     * 
     * @return array
     */
    public static function getMediaTypesArray()
    {
        $items = self::getMediaTypes();
        if (count($items) > 0) {
            return array('mediaTypes' => array('count' => count($items), 'items' => $items));
        }
    }

    /**
     * Get all pages being online
     * 
     * @return array
     * @throws rex_sql_exception
     */
    public static function getPages()
    {
        if (rex_addon::get('structure')->isAvailable()) {
            $pages = array();

            $sql = rex_sql::factory();
            $sql->setQuery('SELECT id FROM ' . rex::getTablePrefix() . 'article WHERE status = 1');

            foreach ($sql as $row) {
                $pages[] = $row->getArrayValue('id');
            }
            return $pages;
        }
    }

    /**
     * Get all pages as chunked array including 'count' and 'items'
     * 
     * @return array
     */
    public static function getChunkedPagesArray()
    {
        $items = self::getPages();
        if (count($items) > 0) {
            $chunkedItems = self::chunk($items, rex_addon::get('cache-warmup')->getConfig('chunkSizePages'));
            return array('pages' => array('count' => count($items), 'items' => $chunkedItems));
        }
    }

    /**
     * Get all languages
     *
     * @return int[]
     */
    public static function getLanguages()
    {
        return rex_clang::getAllIds(true);
    }

    /**
     * Get all languages as array including 'count' and 'items'
     *
     * @return array
     */
    public static function getLanguagesArray()
    {
        $items = self::getLanguages();
        if (count($items) > 0) {
            return array('languages' => array('count' => count($items), 'items' => $items));
        }
    }

    /**
     * Split an array into chunks
     *
     * @param array $items
     * @param int $chunkSize
     * @return array
     */
    public static function chunk(array $items, $chunkSize = 3)
    {
        return array_chunk($items, $chunkSize);
    }

    /**
     * Build JSON from array
     *
     * @param array $items
     * @return string
     */
    public static function buildJSON(array $items)
    {
        return json_encode($items);
    }
}