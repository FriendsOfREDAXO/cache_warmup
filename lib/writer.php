<?php

/**
 * Class cache_warmup_writer
 */
abstract class cache_warmup_writer
{

    /**
     * 
     */
    public static function clearOutput()
    {
        rex_extension::register('OUTPUT_FILTER', function (rex_extension_point $ep) {
            $ep->setSubject(false);
        });
    }

    /**
     * @param string $output
     */
    public static function replaceOutputWith($output = '')
    {
        rex_extension::register('OUTPUT_FILTER', function (rex_extension_point $ep) use ($output) {
            $ep->setSubject($output);
        });
    }
}