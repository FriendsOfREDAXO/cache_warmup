<?php

/**
 * @var rex_addon $this
 */

if (rex_string::versionCompare($this->getVersion(), '3.4.1', '<')) {
    $this->setConfig([
        'image_min' => 10,
        'image_max' => 50,
        'image_ratio' => 0.4,
        'article_min' => 100,
        'article_max' => 1000,
        'article_ratio' => 6,
        'max_execution_time' => 30
    ]);
}