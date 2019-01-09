<?php

/* set up base structure */

$body = '
    <h3 class="cache-warmup__target__title"></h3>
    <hr>
    <div class="cache-warmup__target__content row"></div>
    <div class="cache-warmup__target__progressbar"></div>';

$footer = '
    <div class="row">
        <div class="col-xs-12 cache-warmup__target__footer"></div>
    </div>';

$fragment = new rex_fragment();
$fragment->setVar('class', 'cache-warmup');
$fragment->setVar('body', $body, false);
$fragment->setVar('footer', $footer, false);
echo $fragment->parse('core/page/section.php');

/* cache warmup items JSON */
echo '<script>var cacheWarmupItems = ' . cache_warmup_writer::buildJSON(cache_warmup_selector::prepareCacheItems(true, true)) . ';</script>';

/* CSRF token (REX 5.5+) */
if (class_exists('rex_csrf_token')) {
    echo '<script>var cacheWarmupToken = "' . rex_csrf_token::factory('cache_warmup_generator')->getValue() . '";</script>';
}

/* disable minibar (REX 5.7+) */
if (class_exists('rex_minibar') && rex_minibar::getInstance()->isActive() === null) {
    rex_minibar::getInstance()->setActive(false);
}
?>


<?php /* templates: content */ ?>

<script id="cache_warmup_tpl_content_task" type="text/x-handlebars-template">
    <div class="col-xs-6">
        <p class="cache-warmup__target__task"></p>
    </div>
    <div class="col-xs-6 text-right">
        <p class="cache-warmup__target__elapsed"></p>
    </div>
</script>

<script id="cache_warmup_tpl_content_info" type="text/x-handlebars-template">
    <div class="col-xs-2 text-right cache-warmup__target__icon">
    </div>
    <div class="col-xs-10 cache-warmup__target__text">
    </div>
</script>


<?php /* templates: components */ ?>

<script id="cache_warmup_tpl_stopwatch" type="text/x-handlebars-template">
    <?php echo rex_i18n::rawMsg('cache_warmup_time_elapsed') ?>: <span id="cache_warmup_time"></span>
</script>


<script id="cache_warmup_tpl_progressbar" type="text/x-handlebars-template">
    <div class="progress cache-warmup__progressbar">
        <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
    </div>
</script>


<?php /* templates: titles */ ?>

<script id="cache_warmup_tpl_title_pages" type="text/x-handlebars-template">
    <?php echo rex_i18n::rawMsg('cache_warmup_pages_title') ?>
</script>


<script id="cache_warmup_tpl_title_images" type="text/x-handlebars-template">
    <?php echo rex_i18n::rawMsg('cache_warmup_images_title') ?>
</script>


<script id="cache_warmup_tpl_title_finished" type="text/x-handlebars-template">
    <?php echo rex_i18n::rawMsg('cache_warmup_finished_title') ?>
</script>


<script id="cache_warmup_tpl_title_error" type="text/x-handlebars-template">
    <?php echo rex_i18n::rawMsg('cache_warmup_error_title') ?>
</script>


<script id="cache_warmup_tpl_title_nothing" type="text/x-handlebars-template">
    <?php echo rex_i18n::rawMsg('cache_warmup_nothing_title') ?>
</script>


<?php /* templates: progress */ ?>

<script id="cache_warmup_tpl_progress_pages" type="text/x-handlebars-template">
    <?php echo rex_i18n::rawMsg('cache_warmup_pages_progress') ?>
</script>


<script id="cache_warmup_tpl_progress_images" type="text/x-handlebars-template">
    <?php echo rex_i18n::rawMsg('cache_warmup_images_progress') ?>
</script>


<?php /* templates: icons */ ?>

<script id="cache_warmup_tpl_icon_finished" type="text/x-handlebars-template">
    <i class="fa fa-hand-peace-o fa-5x" aria-hidden="true"></i>
</script>


<script id="cache_warmup_tpl_icon_error" type="text/x-handlebars-template">
    <i class="fa fa-meh-o fa-5x" aria-hidden="true"></i>
</script>


<script id="cache_warmup_tpl_icon_nothing" type="text/x-handlebars-template">
    <i class="fa fa-user-md fa-5x" aria-hidden="true"></i>
</script>


<?php /* templates: texts */ ?>

<script id="cache_warmup_tpl_text_finished" type="text/x-handlebars-template">
    <p><?php echo rex_i18n::rawMsg('cache_warmup_finished_text') ?></p>
</script>


<script id="cache_warmup_tpl_text_error" type="text/x-handlebars-template">
    <p><?php echo rex_i18n::rawMsg('cache_warmup_error_text') ?></p>
</script>


<script id="cache_warmup_tpl_text_nothing" type="text/x-handlebars-template">
    <p><?php echo rex_i18n::rawMsg('cache_warmup_nothing_text') ?></p>
</script>


<?php /* templates: links */ ?>

<script id="cache_warmup_tpl_error_link" type="text/x-handlebars-template">
    <?php echo rex_i18n::rawMsg('cache_warmup_error_link') ?>
</script>


<?php /* templates: buttons */ ?>

<script id="cache_warmup_tpl_button_success" type="text/x-handlebars-template">
    <button class="btn btn-success cache-warmup__button--success"><?php echo rex_i18n::rawMsg('cache_warmup_button_success') ?></button>
</script>


<script id="cache_warmup_tpl_button_again" type="text/x-handlebars-template">
    <div class="text-right">
        <button class="btn btn-link cache-warmup__button--again"><?php echo rex_i18n::rawMsg('cache_warmup_button_again') ?></button>
        <button class="btn btn-danger cache-warmup__button--cancel"><?php echo rex_i18n::rawMsg('cache_warmup_button_cancel') ?></button>
    </div>
</script>


<script id="cache_warmup_tpl_button_cancel" type="text/x-handlebars-template">
    <div class="text-right">
        <button class="btn btn-danger cache-warmup__button--cancel"><?php echo rex_i18n::rawMsg('cache_warmup_button_cancel') ?></button>
    </div>
</script>
