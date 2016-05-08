<?php

/**
 * Class cache_warmup_generator
 */
abstract class cache_warmup_generator
{

    /**
     * @param string $items
     * @return array
     */
    public static function prepareItems($items)
    {
        $itemsArray = explode(',', $items);
        $filteredItemsArray = array();

        // filter integers
        if (count($itemsArray) > 0) {
            foreach ($itemsArray as $item) {
                if ((int)$item > 0 || $item === '0') {
                    $filteredItemsArray[] = (int)$item;
                }
            }
        }

        if (count($filteredItemsArray) > 0) {
            return $filteredItemsArray;
        }

        return array();
    }

    /**
     * @param array $items
     * @return mixed
     */
    abstract protected function generateCache(array $items);
}