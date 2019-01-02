<?php

$content = '';

$content .= '<p>' . rex_i18n::rawMsg('cache_warmup_description') . '</p>';
$content .= '<p><a class="btn btn-primary cache-warmup__button-start" href="' . rex_url::backendPage("cache_warmup/warmup") . '" target="CacheWarmupWindow">' . rex_i18n::rawMsg('cache_warmup_button_start') . ' <i class="fa fa-external-link" aria-hidden="true"></i></a></p>';


$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::rawMsg('cache_warmup_title'));
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');