<?php

// number of images to generate per single request
// increase to speed up (reduces number of requests but extends script time)
$this->setConfig('chunkSizeImages', 3);

// number of pages to generate per single request
// increase to speed up (reduces number of requests but extends script time)
$this->setConfig('chunkSizePages', 50);

// inject addon ressources
if (rex::isBackend() && rex::getUser()) {
    rex_view::addCssFile($this->getAssetsUrl('css/cache-warmup.css'));
    rex_view::addJsFile($this->getAssetsUrl('js/cache-warmup.js'));
}