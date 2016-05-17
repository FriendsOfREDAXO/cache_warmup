<?php

/**
 * @package redaxo\media-manager
 */
class cache_warmup_managed_media extends rex_managed_media
{

    public function sendMedia($sourceCacheFilename, $headerCacheFilename, $save = false)
    {
        if ($this->asImage) {
            $src = $this->getImageSource();
        } else {
            $src = rex_file::get($this->getMediapath());
        }

        $this->setHeader('Content-Length', rex_string::size($src));
        $header = $this->getHeader();
        if (!array_key_exists('Content-Type', $header)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $content_type = finfo_file($finfo, $this->getMediapath());
            if ($content_type != '') {
                $this->setHeader('Content-Type', $content_type);
            }
        }
        if (!array_key_exists('Content-Disposition', $header)) {
            $this->setHeader('Content-Disposition', 'inline; filename="' . $this->getMediaFilename() . '";');
        }
        if (!array_key_exists('Last-Modified', $header)) {
            $this->setHeader('Last-Modified', date('r'));
        }

        rex_file::putCache($headerCacheFilename, $this->header);
        rex_file::put($sourceCacheFilename, $src);
    }
}
