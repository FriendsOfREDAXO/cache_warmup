<?php

$content = '';

$content .= '<p>' . $this->i18n('description') . '</p>';
$content .= '<p><a class="btn btn-primary cache-warmup__button-start" href="' . rex_url::backendPage("cache-warmup/warmup") . '" target="CacheWarmupWindow">' . $this->i18n('button-start') . ' <i class="fa fa-external-link" aria-hidden="true"></i></a></p>';


$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('title'));
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');