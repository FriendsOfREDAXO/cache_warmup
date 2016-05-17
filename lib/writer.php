<?php

/**
 * Class cache_warmup_writer
 */
abstract class cache_warmup_writer
{

    /**
     * Clear output (show blank page)
     */
    public static function clearOutput()
    {
        rex_extension::register('OUTPUT_FILTER', function (rex_extension_point $ep) {
            $ep->setSubject(false);
        });
    }


    /**
     * Replace page output with given content
     *
     * @param string $output
     */
    public static function replaceOutputWith($output = '')
    {
        rex_extension::register('OUTPUT_FILTER', function (rex_extension_point $ep) use ($output) {
            $ep->setSubject($output);
        });
    }


    /**
     * Build JSON object from array
     *
     * @param array $items
     * @return string
     */
    public static function buildJSON(array $items)
    {
        return json_encode($items);
    }
}