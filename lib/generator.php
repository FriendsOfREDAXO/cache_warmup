<?php

namespace FriendsOfRedaxo\CacheWarmup;

/**
 * Class CacheWarmupGenerator.
 */
abstract class CacheWarmupGenerator
{
    /**
     * Prepare items in query string
     * query string pattern: v1.v2,v1.v2,….
     *
     * @param string $items
     *
     * @return array
     */
    public static function prepareItems(string $items): array
    {
        $itemsArray = explode(',', $items);
        $filteredItemsArray = [];

        foreach ($itemsArray as $item) {
            $filteredItemsArray[] = explode('.', $item);
        }

        return $filteredItemsArray;
    }

    /**
     * @return mixed
     */
    abstract protected function generateCache(array $items);
}
