<?php

$content = '';

$content .= '<p>' . rex_i18n::rawMsg('cache-warmup_description') . '</p>';
$content .= '<p><a class="btn btn-primary cache-warmup__button-start" href="' . rex_url::backendPage("cache-warmup/warmup") . '" target="CacheWarmupWindow">' . rex_i18n::rawMsg('cache-warmup_button-start') . ' <i class="fa fa-external-link" aria-hidden="true"></i></a></p>';


$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::rawMsg('cache-warmup_title'));
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');