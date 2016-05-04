<?php
if (rex::isBackend() && rex::getUser()) {
    rex_view::addJsFile($this->getAssetsUrl('js/cache-warmup.js'));
}
