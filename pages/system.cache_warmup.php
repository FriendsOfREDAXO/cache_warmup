<?php

$content = '';

$content .= '<p>' . rex_i18n::rawMsg('cache_warmup_description') . '</p>';
$content .= '<p><a class="btn btn-primary cache-warmup__button-start" href="' . rex_url::backendPage("cache_warmup/warmup") . '" target="CacheWarmupWindow">' . rex_i18n::rawMsg('cache_warmup_button_start') . ' <i class="fa fa-external-link" aria-hidden="true"></i></a></p>';


$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::rawMsg('cache_warmup_title'));
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');


if (rex_post('btn_save', 'string') != '') {
    $this->setConfig(rex_post('settings', [
        ['cache_warmup_debug', 'string'],
        ['dsgvo_consent_css', 'string']
    ]));

    $message = $this->i18n('cache_warmup_settings_save_success');
}


/* Einstellungen */

$form = rex_config_form::factory( $this->name );

$field = $form->addTextField("max_execution_time");
$field->setLabel($this->i18n("cache_warmup_settings_max_execution_time_label"));
$field->setNotice($this->i18n("cache_warmup_settings_max_execution_time_notice"));
$field->getValidator()->add("type", $this->i18n("cache_warmup_settings_validate_default"), "int");

$field = $form->addSelectField('debug',NULL, ['class'=>'form-control selectpicker']);
$field->setLabel($this->i18n("cache_warmup_settings_debug_label"));
$select = $field->getSelect();
$select->setSize(2);
$select->addOption($this->i18n("cache_warmup_settings_debug_option_off"), 0);
$select->addOption($this->i18n("cache_warmup_settings_debug_option_on"), 1);
$field->setNotice($this->i18n("cache_warmup_settings_debug_notice"));

$field = $form->addTextField("image_ratio");
$field->setLabel($this->i18n("cache_warmup_settings_image_ratio_label"));
$field->setNotice($this->i18n("cache_warmup_settings_image_ratio_notice"));
$field->getValidator()->add("type", $this->i18n("cache_warmup_settings_validate_default"), "float");

$field = $form->addTextField("image_min");
$field->setLabel($this->i18n("cache_warmup_settings_image_min_label"));
$field->setNotice($this->i18n("cache_warmup_settings_image_min_notice"));
$field->getValidator()->add("type", $this->i18n("cache_warmup_settings_validate_default"), "int");

$field = $form->addTextField("image_max");
$field->setLabel($this->i18n("cache_warmup_settings_image_max_label"));
$field->setNotice($this->i18n("cache_warmup_settings_image_max_notice"));
$field->getValidator()->add("type", $this->i18n("cache_warmup_settings_validate_default"), "int");

$field = $form->addTextField("article_ratio");
$field->setLabel($this->i18n("cache_warmup_settings_article_ratio_label"));
$field->setNotice($this->i18n("cache_warmup_settings_article_ratio_notice"));
$field->getValidator()->add("type", $this->i18n("cache_warmup_settings_validate_default"), "float");

$field = $form->addTextField("article_min");
$field->setLabel($this->i18n("cache_warmup_settings_article_min_label"));
$field->setNotice($this->i18n("cache_warmup_settings_article_min_notice"));
$field->getValidator()->add("type", $this->i18n("cache_warmup_settings_validate_default"), "int");

$field = $form->addTextField("article_max");
$field->setLabel($this->i18n("cache_warmup_settings_article_max_label"));
$field->setNotice($this->i18n("cache_warmup_settings_article_max_notice"));
$field->getValidator()->add("type", $this->i18n("cache_warmup_settings_validate_default"), "int");

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $this->i18n("cache_warmup_settings_title"), false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');