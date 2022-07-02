<?php
/**
 * Generator
 * generates images and/or pages by ID provided in query string.
 *
 * URL pattern: index.php?page=cache_warmup/generator&pages=pageId.langId,pageId.langId&images=imageId.mediaTypeName,imageId.mediaTypeName
 *
 * Hints:
 * - You may run both pages job and images job at once, but you’re better off using just one job at a time.
 * - Do not generate too many images at once to avoid running out of script execution time!
 * - Page will return blank if stuff works out as expected.
 */

$proceed = true;

if (class_exists('rex_csrf_token')) {
    $mode = rex::getProperty('redaxo');
    rex::setProperty('redaxo', true); // switch REDAXO to backend mode
    if (!rex_csrf_token::factory('cache_warmup_generator')->isValid()) {
        $proceed = false; // token not valid, do not proceed!
    }
    rex::setProperty('redaxo', $mode); // reset to previous mode
}

if (!$proceed) {
    // do not proceed but send a 403 forbidden
    rex_response::cleanOutputBuffers();
    rex_response::setStatus(rex_response::HTTP_FORBIDDEN);
} else {
    // generate page cache
    $pages = rex_get('pages', 'string');
    if (strlen($pages) > 0) {
        $generator = new cache_warmup_generator_pages();
        $items = cache_warmup_generator::prepareItems($pages);
        $generator->generateCache($items);
    }

    // generate image cache
    $images = rex_get('images', 'string');
    if (strlen($images) > 0) {
        $generator = new cache_warmup_generator_images();
        $items = cache_warmup_generator::prepareItems($images);
        $items = cache_warmup_selector::getImageNames($items);
        $generator->generateCache($items);
    }

    // clear output
    cache_warmup_writer::clearOutput();
    rex_response::setStatus(rex_response::HTTP_OK);
}
