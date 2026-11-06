<?php

// Work items: articleId.clang[.profile] / mediaId.mediaType[.profile].
$status = rex_response::HTTP_OK;
$result = ['success' => false, 'processed' => 0];

try {
    if (class_exists('rex_csrf_token')) {
        $mode = rex::getProperty('redaxo');
        rex::setProperty('redaxo', true);
        try {
            $validToken = rex_csrf_token::factory('cache_warmup_generator')->isValid();
        } finally {
            rex::setProperty('redaxo', $mode);
        }
        if (!$validToken) {
            $status = rex_response::HTTP_FORBIDDEN;
            throw new RuntimeException('Invalid or expired warmup token. Reload the warmup window.');
        }
    }

    $pages = rex_get('pages', 'string');
    if ('' !== $pages) {
        $items = cache_warmup_generator::prepareItems($pages);
        (new cache_warmup_generator_pages())->generateCache($items);
        $result['processed'] += count($items);
    }

    $images = rex_get('images', 'string');
    if ('' !== $images) {
        $items = cache_warmup_generator::prepareItems($images);
        $items = cache_warmup_selector::getImageNames($items);
        (new cache_warmup_generator_images())->generateCache($items);
        $result['processed'] += count($items);
    }

    if (0 === $result['processed']) {
        throw new InvalidArgumentException('No warmup items supplied');
    }
    $result['success'] = true;
} catch (Throwable $exception) {
    if (rex_response::HTTP_OK === $status) {
        $status = $exception instanceof InvalidArgumentException ? '400 Bad Request' : rex_response::HTTP_INTERNAL_ERROR;
    }
    $result['message'] = $exception->getMessage();
    if (rex_response::HTTP_INTERNAL_ERROR === $status) {
        rex_logger::logException($exception);
    }
}

// Direct JSON response: template output must not be mistaken for success.
rex_response::cleanOutputBuffers();
rex_response::setStatus($status);
rex_response::sendContent(json_encode($result, JSON_INVALID_UTF8_SUBSTITUTE), 'application/json; charset=utf-8');
exit;
