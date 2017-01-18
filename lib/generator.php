<?php

/**
 * Class cache_warmup_generator
 */
abstract class cache_warmup_generator
{

    /**
     * Prepare items in query string
     * query string pattern: v1.v2,v1.v2,…
     * 
     * @param string $items
     * @return array
     */
    public static function prepareItems($items)
    {
        $itemsArray = explode(',', $items);
        $filteredItemsArray = array();

        if (count($itemsArray) > 0) {
            foreach ($itemsArray as $item) {
                $filteredItemsArray[] = explode('.', $item);
            }
        }

        return $filteredItemsArray;
    }

    /**
     * @param array $items
     * @return mixed
     */
    abstract protected function generateCache(array $items);
}
