<?php

/**
 * Class cache_warmup_generator_images
 */
class cache_warmup_generator_images extends cache_warmup_generator
{
    public function generateCache(array $items)
    {

        if (rex_addon::get('media_manager')->isAvailable()) {
//            echo 'images';
        }
    }
}