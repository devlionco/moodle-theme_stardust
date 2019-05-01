<?php
/**
 * Returns URL to the stored file via pluginfile.php.
 *
 * Note the theme must also implement pluginfile.php handler,
 * theme revision is used instead of the itemid.
 *
 * @param string $setting
 * @param string $filearea
 * @return string protocol relative URL or null if not present
 */
 function setting_file_url($setting, $filearea) {
    global $CFG;

    if (empty($this->settings->$setting)) {
        return null;
    }

    $component = 'theme_'.$this->name;
    $itemid = theme_get_revision();
    $filepath = $this->settings->$setting;
    $syscontext = context_system::instance();

    $url = moodle_url::make_file_url("$CFG->wwwroot/pluginfile.php", "/$syscontext->id/$component/$filearea/$itemid".$filepath);

    // Now this is tricky because the we can not hardcode http or https here, lets use the relative link.
    // Note: unfortunately moodle_url does not support //urls yet.

    // $url = preg_replace('|^https?://|i', '//', $url->out(false));

    return $url;
}
