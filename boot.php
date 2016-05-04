<?php
if (rex::isBackend() && rex::getUser()) {
    rex_view::addCssFile($this->getAssetsUrl('css/cache-warmup.css'));
    rex_view::addJsFile($this->getAssetsUrl('js/cache-warmup.js'));
}
