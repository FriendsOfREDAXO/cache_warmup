<?php

/** @var rex_addon $this */

// set/update config on warmup page (popup)
if ('cache_warmup/warmup' === rex_be_controller::getCurrentPage()) {
    // A cold page can synchronously generate dozens of image sizes. Keep each
    // article/profile and image/type/profile in its own serial HTTP request.
    $this->setConfig('chunkSizePages', 1);
    $this->setConfig('chunkSizeImages', 1);
}

// inject addon ressources
if (rex::isBackend() && !is_null(rex::getUser()) && false !== strpos(rex_be_controller::getCurrentPage(), 'cache_warmup')) {
    if ('warmup' == rex_be_controller::getCurrentPagePart(2)) {
        rex_view::addJsFile($this->getAssetsUrl('js/handlebars.min.js'));
        rex_view::addJsFile($this->getAssetsUrl('js/timer.jquery.min.js'));
    }

    rex_view::addCssFile($this->getAssetsUrl('css/cache-warmup.css'));
    rex_view::addJsFile($this->getAssetsUrl('js/cache-warmup.js'));
}

// switch REDAXO to frontend mode before generating cache files
// this is essential to include content modification by addons, e.g. slice status on/off
rex_extension::register('PACKAGES_INCLUDED', static function () {
    if ('cache_warmup/generator' === rex_be_controller::getCurrentPage()) {
        rex::setProperty('redaxo', false);
    }
}, rex_extension::EARLY);
