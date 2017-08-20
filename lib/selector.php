<?php

/**
 * Class cache_warmup_selector
 * Selects images, media types, pages and languages
 */
abstract class cache_warmup_selector
{

    /**
     * Prepare all cache items
     *
     * @return array
     */
    public static function prepareCacheItems()
    {
        return array(
            'images' => self::getChunkedImagesArray(),
            'pages' => self::getChunkedPagesArray()
        );
    }


    /**
     * Get all images being used in REDAXO (pages, meta, yforms)
     * »X never, ever marks the spot.« (-- Indiana Jones)
     *
     * @return array
     * @throws rex_sql_exception
     */
    private static function getImages()
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

            /* find images in yforms (be_media, be_medialist, mediafile) */

            if (rex_addon::get('yform')->isAvailable()) {
                $yforms = array();

                // get tables and fields where 'be_media' and 'be_medialist' are used
                $sql->setQuery('SELECT table_name,name FROM ' . rex::getTablePrefix() . 'yform_field WHERE type_name LIKE "be_media%" OR type_name LIKE "mediafile"');
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

                // find images in metas (article, clang, media)
                if (isset($metainfos['names'])) {
                    $tablesFrom = array(
                        rex::getTablePrefix() . 'article',
                        rex::getTablePrefix() . 'clang',
                        rex::getTablePrefix() . 'media'
                    );
                    foreach ($tablesFrom as $table) {
                        $sql->setQuery('SELECT * FROM ' . $table);
                        if ($sql->getRows() > 0) {
                            foreach ($sql as $row) {
                                foreach ($metainfos['names'] as $field) {
                                    if ($row->hasValue($field)) {
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
                    }
                }
            }

            /* prepare and return ------------------------------------------------- */

            // create extension point (EP) for developers to extend selected images
            $images = rex_extension::registerPoint(new rex_extension_point('CACHE_WARMUP_IMAGES', $images));
            
            // filter images
            $images = self::filterImages($images);

            return $images;
        }
        return array();
    }


    /**
     * Filter images: remove duplicate images and non-image items
     *
     * @param array $items
     * @return array
     */
    private static function filterImages(array $items)
    {
        $filteredImages = array();

        $items = array_unique($items); // remove duplicate values

        foreach ($items as $item) {
            $media = rex_media::get($item);
            if ($media) {
                if ($media->isImage()) {
                    $filteredImages[] = $media->getId();
                }
                rex_media::clearInstance($item);
            }
        }

        return $filteredImages;
    }


    /**
     * Get all images and mediatypes as chunked array including 'count' and 'items'
     *
     * @return array
     */
    private static function getChunkedImagesArray()
    {
        $images = self::getImages();
        $mediaTypes = self::getMediaTypes();

        $items = array();
        if (count($images) > 0 && count($mediaTypes) > 0) {
            foreach ($images as $image) {
                foreach ($mediaTypes as $type) {
                    $items[] = array($image, $type);
                }
            }
        }
        $chunkedItems = self::chunk($items, rex_addon::get('cache_warmup')->getConfig('chunkSizeImages'));
        return array('count' => count($items), 'items' => $chunkedItems);
    }


    /**
     * Get all media types as defined in media manager addon
     *
     * @return array
     * @throws rex_sql_exception
     */
    private static function getMediaTypes()
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
        return array();
    }


    /**
     * Get all pages being online
     *
     * @return array
     * @throws rex_sql_exception
     */
    private static function getPages()
    {
        if (rex_addon::get('structure')->isAvailable()) {

            $query = 'SELECT a.id, a.clang_id FROM ' . rex::getTable('article') . ' AS a INNER JOIN ' . rex::getTable('clang') . ' AS c ON a.clang_id = c.id WHERE a.status = ?';
            $params = [1];
            
            // if clang has status on/off (REX >=5.1), adjust query to select online pages only
            if (method_exists('rex_clang', 'isOnline')) {
                $query .= ' AND c.status = ?';
                $params = [1, 1];                
            }

            $sql = rex_sql::factory();
            $pages = $sql->getArray($query, $params, PDO::FETCH_NUM);

            return $pages;
        }
        return array();
    }


    /**
     * Get all pages and languages as chunked array including 'count' and 'items'
     *
     * @return array
     */
    private static function getChunkedPagesArray()
    {
        $items = self::getPages();

        $chunkedItems = self::chunk($items, rex_addon::get('cache_warmup')->getConfig('chunkSizePages'));
        return array('count' => count($items), 'items' => $chunkedItems);
    }


    /**
     * Split an array into chunks
     *
     * @param array $items
     * @param int $chunkSize
     * @return array
     */
    private static function chunk(array $items, $chunkSize)
    {
        return array_chunk($items, $chunkSize);
    }
}
