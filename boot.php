<?php

// number of images to generate per single request
// increase to speed up (reduces number of requests but extends script time)
// (hint: enable debug mode in cache-warmup.js to report execution times)
$this->setConfig('chunkSizeImages', 4);

// number of pages to generate per single request
// increase to speed up (reduces number of requests but extends script time)
// (hint: enable debug mode in cache-warmup.js to report execution times)
$this->setConfig('chunkSizePages', 42); // magic redaxo number

// inject addon ressources
if (rex::isBackend() && rex::getUser()) {

    if (rex_be_controller::getCurrentPagePart(2) == 'warmup') {
        rex_view::addJsFile($this->getAssetsUrl('js/handlebars.min.js?v=' . $this->getVersion()));
        rex_view::addJsFile($this->getAssetsUrl('js/timer.jquery.min.js?v=' . $this->getVersion()));
    }

    rex_view::addCssFile($this->getAssetsUrl('css/cache-warmup.css?v=' . $this->getVersion()));
    rex_view::addJsFile($this->getAssetsUrl('js/cache-warmup.js?v=' . $this->getVersion()));
}
