<?php

/*
 * TODO
 * 
 * Alle Frontendbausteine schonmal vorab entwickelt
 */


$content1 = '

<h3>' . $this->i18n('step-1-title') . '</h3>

<hr>

<div class="row">
    <div class="col-xs-6">

        <p class="cache-warmup__task">' . $this->i18n('step-1-progress') . '</p>

    </div>
    <div class="col-xs-6 text-right">

        <p class="cache-warmup__elapsed">' . $this->i18n('time-elapsed') . ': 00:01:23</p>

    </div>
</div>

<div class="progress">
    <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100" style="width: 45%">
        <span class="sr-only">45% Complete</span>
    </div>
</div>';

$footer1 = '

<div class="row">
    <div class="col-xs-12 text-right">

        <button class="btn btn-danger">' . $this->i18n('button-cancel') . '</button>

    </div>
</div>';



$content2 = '

<h3>' . $this->i18n('step-2-title') . '</h3>

<hr>

<div class="row">
    <div class="col-xs-6">

        <p class="cache-warmup__task">' . $this->i18n('step-2-progress') . '</p>

    </div>
    <div class="col-xs-6 text-right">

        <p class="cache-warmup__elapsed">' . $this->i18n('time-elapsed') . ': 00:01:23</p>

    </div>
</div>

<div class="progress">
    <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="73" aria-valuemin="0" aria-valuemax="100" style="width: 73%">
        <span class="sr-only">73% Complete</span>
    </div>
</div>';

$footer2 = '

<div class="row">
    <div class="col-xs-12 text-right">

        <button class="btn btn-danger">' . $this->i18n('button-cancel') . '</button>

    </div>
</div>';



$content3 = '

<h3>' . $this->i18n('finished-title') . '</h3>

<hr>

<div class="row">
    <div class="col-xs-2 text-right">

        <i class="fa fa-hand-peace-o fa-5x" aria-hidden="true"></i>

    </div>
    <div class="col-xs-10">

        <p>' . rex_i18n::rawMsg('cache-warmup_finished-text') . '</p>

    </div>
</div>';

$footer3 = '

<div class="row">
    <div class="col-xs-12">

        <button class="btn btn-success">' . $this->i18n('button-success') . '</button>

    </div>
</div>';



$content4 = '

<div class="alert alert-danger" role="alert">
    <i class="fa fa-exclamation-triangle fa-1x" aria-hidden="true"></i> ' . $this->i18n('error-title') . '
</div>

<div class="row">
    <div class="col-xs-2 text-right">

        <i class="fa fa-meh-o fa-5x" aria-hidden="true"></i>

    </div>
    <div class="col-xs-10">

        <p>' . rex_i18n::rawMsg('cache-warmup_error-text-1') . '</p>
        <p>' . rex_i18n::rawMsg('cache-warmup_error-text-2') . '</p>
        
    </div>
</div>';

$footer4 = '

<div class="row">
    <div class="col-xs-12 text-right">

        <button class="btn btn-link">' . $this->i18n('button-again') . '</button>
        <button class="btn btn-danger">' . $this->i18n('button-cancel') . '</button>

    </div>
</div>';


$fragment = new rex_fragment();
$fragment->setVar('class', 'cache-warmup');
$fragment->setVar('body', $content1, false);
$fragment->setVar('footer', $footer1, false);
echo $fragment->parse('core/page/section.php');



/* inject cache warmup items JSON */

echo '<script>var cacheWarmup = ' . cache_warmup_writer::buildJSON(cache_warmup_selector::prepareCacheItems()) . ';</script>';