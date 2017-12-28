<?php
if (rex_be_controller::getCurrentPage() == 'system/cache_warmup') {

    // chunk size config
    // min: min number of items to generate per request
    // max: max number of items to generate per request
    // ratio: multiplies with execution time to define number of items to generate per request
    $chunksConfig = array(
        'chunkSizeImages' => array('min' => 10, 'max' => 50, 'ratio' => 0.4),
        'chunkSizePages' => array('min' => 100, 'max' => 1000, 'ratio' => 6)
    );

    // get `max_execution_time`
    // if it’s false, set to a low value
    $executionTime = ini_get('max_execution_time');
    if ($executionTime === false) {
        $executionTime = 30;
    }

    // define number of items to generate per single request based on `max_execution_time`
    // higher values reduce number of requests but extend script time
    // (hint: enable debug mode in cache-warmup.js to report execution times)
    foreach ($chunksConfig as $k => $v) {
        $numOfItems = round($executionTime * $v['ratio']);

        if ($numOfItems > $v['max'] || $executionTime === 0) {
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


// switch REDAXO to frontend mode before generating cache files
// this is essential to include content modification by addons, e.g. slice status on/off
rex_extension::register('PACKAGES_INCLUDED', function (rex_extension_point $ep) {
    if (rex_be_controller::getCurrentPage() == 'cache_warmup/generator') {
        rex::setProperty('redaxo', false);
    }
}, rex_extension::EARLY);

// inject addon ressources, for both cache_warmup/warmup and system/cache_warmup
if (rex::isBackend() && rex::getUser() && strpos(rex_be_controller::getCurrentPage(), 'cache_warmup') !== false) {

    if (rex_be_controller::getCurrentPagePart(2) == 'warmup') {
        rex_view::addJsFile($this->getAssetsUrl('js/handlebars.min.js'));
        rex_view::addJsFile($this->getAssetsUrl('js/timer.jquery.min.js'));
    }

    rex_view::addCssFile($this->getAssetsUrl('css/cache-warmup.css'));
    rex_view::addJsFile($this->getAssetsUrl('js/cache-warmup.js'));
}
