<?php

/**
 * Class cache_warmup_generator.
 */
abstract class cache_warmup_generator
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
            $parts = explode('.', $item);
            if ((2 !== count($parts) && 3 !== count($parts))
                || !ctype_digit($parts[0]) || (int) $parts[0] < 1
                || '' === trim($parts[1]) || preg_match('/[\x00-\x1F\x7F]/', $parts[1])
                || false !== strpos($parts[1], '/') || false !== strpos($parts[1], '\\')
                || (isset($parts[2]) && !in_array($parts[2], ['original', 'default', 'webp', 'avif'], true))) {
                throw new InvalidArgumentException('Invalid cache warmup item: ' . $item);
            }
            $filteredItemsArray[] = $parts;
        }

        return $filteredItemsArray;
    }

    /**
     * @return mixed
     */
    abstract protected function generateCache(array $items);
}
