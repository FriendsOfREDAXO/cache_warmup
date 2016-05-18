<?php

/**
 * Class cache_warmup_media_manager
 */
class cache_warmup_media_manager extends rex_media_manager
{

    public static function init($file, $type)
    {
        $rex_media_manager_file = $file;
        $rex_media_manager_type = $type;

        if ($rex_media_manager_file != '' && $rex_media_manager_type != '') {
            $media_path = rex_path::media($rex_media_manager_file);
            $cache_path = rex_path::addonCache('media_manager');

            $media = new rex_managed_media($media_path);
            $media_manager = new self($media);
            $media_manager->setCachePath($cache_path);
            $media_manager->applyEffects($rex_media_manager_type);
            $media_manager->sendMedia();
        }
    }

    public function sendMedia()
    {
        $headerCacheFilename = $this->getHeaderCacheFilename();
        $CacheFilename = $this->getCacheFilename();

        if (!$this->isCached()) {
            $this->media->sendMedia($CacheFilename, $headerCacheFilename, true);
        }
    }
}