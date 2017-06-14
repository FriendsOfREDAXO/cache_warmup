<?php

/**
 * Class cache_warmup_media_manager
 * @deprecated since media_manager 2.3.0
 */
class cache_warmup_media_manager extends rex_media_manager
{

    /**
     * @deprecated since media_manager 2.3.0, use `rex_media_manager::create()` instead
     */
    public static function init()
    {
        list($file, $type) = func_get_args();

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

    /**
     * @deprecated since media_manager 2.3.0
     */
    public function sendMedia()
    {
        $headerCacheFilename = $this->getHeaderCacheFilename();
        $CacheFilename = $this->getCacheFilename();

        if (!$this->isCached()) {
            // clean buffer to avoid warnings caused by headers already sent
            // https://github.com/FriendsOfREDAXO/cache_warmup/issues/24
            ob_start();
            $this->media->sendMedia($CacheFilename, $headerCacheFilename, true);
            ob_end_clean();
        }
    }
}