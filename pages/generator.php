<?php

// generate page cache
$pages = rex_get('pages', 'string');
cache_warmup_generator_pages::generateCache(cache_warmup_generator::prepareItems($pages));    

// generate image cache
$images = rex_get('images', 'string');
cache_warmup_generator_images::generateCache(cache_warmup_generator::prepareItems($images));


//$images = cache_warmup_selector::getImages();
//var_dump($images);
//exit;

echo '<pre>';

$images = cache_warmup_selector::getChunkedImagesArray();
print_r($images);

$mediaTypes = cache_warmup_selector::getMediaTypesArray();
print_r($mediaTypes);

$pages = cache_warmup_selector::getChunkedPagesArray();
print_r($pages);

$languages = cache_warmup_selector::getLanguagesArray();
print_r($languages);

exit;


// clear output
cache_warmup_writer::clearOutput();