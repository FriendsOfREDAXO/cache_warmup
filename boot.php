<?php

/** @var rex_addon $this */

// set/update config on warmup page (popup)
if ('cache_warmup/warmup' === rex_be_controller::getCurrentPage()) {
    // chunk size config
    // min: min number of items to generate per request
    // max: max number of items to generate per request
    // ratio: multiplies with execution time to define number of items to generate per request
    $chunksConfig = [
        'chunkSizeImages' => ['min' => 10, 'max' => 50, 'ratio' => 0.4],
        'chunkSizePages' => ['min' => 100, 'max' => 1000, 'ratio' => 6],
    ];

    // get `max_execution_time`
    // if it’s 0 (false), set to a low value
    $executionTime = (int) ini_get('max_execution_time');
    if (0 === $executionTime) {
        $executionTime = 30;
    }

    // define number of items to generate per single request based on `max_execution_time`
    // higher values reduce number of requests but extend script time
    // (hint: enable debug mode in REDAXO to report execution times)
    foreach ($chunksConfig as $k => $v) {
        $numOfItems = round($executionTime * $v['ratio']);

        if ($numOfItems > $v['max']) {
            // limit to max value
            // hint: executionTime === 0 equates to infinite!
            $this->setConfig($k, $v['max']);
        } elseif ($numOfItems < $v['min']) {
            // limit to min value
            $this->setConfig($k, $v['min']);
        } else {
            // set to calculated number of items
            $this->setConfig($k, $numOfItems);
        }
    }
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
