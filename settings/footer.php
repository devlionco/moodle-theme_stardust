<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Heading and course images settings page file.
 *
 * @packagetheme_stardust
 * @copyright  2016 Chris Kenniburg
 * @creditstheme_boost - MoodleHQ
 * @licensehttp://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$page = new admin_settingpage('theme_stardust_footersettings', get_string('footersettigs', 'theme_stardust'));

// Footer setup heading
$name = 'theme_stardust/footersettigs_header';
$heading = get_string('footersettigs_header', 'theme_stardust');
$information = get_string('footersettigs_header_desc', 'theme_stardust');
$setting = new admin_setting_heading($name, $heading, $information);
$page->add($setting);

// Main logo URL
$name = 'theme_stardust/footersettigs_mainlogo_url';
$title = get_string('footersettigs_mainlogo_url', 'theme_stardust');
$description = get_string('footersettigs_mainlogo_url_desc', 'theme_stardust');
$default = $CFG->wwwroot . '/my/';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Youtube URL
$name = 'theme_stardust/footersettigs_youtube_url';
$title = get_string('footersettigs_youtube_url', 'theme_stardust');
$description = get_string('footersettigs_youtube_url_desc', 'theme_stardust');
$default = 'https://www.youtube.com/user/davidsonweb';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Facebook URL
$name = 'theme_stardust/footersettigs_facebook_url';
$title = get_string('footersettigs_facebook_url', 'theme_stardust');
$description = get_string('footersettigs_facebook_url_desc', 'theme_stardust');
$default = 'https://www.facebook.com/DavidsonOnLine';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Twitter URL
$name = 'theme_stardust/footersettigs_twitter_url';
$title = get_string('footersettigs_twitter_url', 'theme_stardust');
$description = get_string('footersettigs_twitter_url_desc', 'theme_stardust');
$default = 'https://twitter.com/DavidsonOnline';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Instagram URL
$name = 'theme_stardust/footersettigs_instagram_url';
$title = get_string('footersettigs_instagram_url', 'theme_stardust');
$description = get_string('footersettigs_instagram_url_desc', 'theme_stardust');
$default = 'https://www.instagram.com/davidsononline';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Home page URL
$name = 'theme_stardust/footersettigs_homepage_url';
$title = get_string('footersettigs_homepage_url', 'theme_stardust');
$description = get_string('footersettigs_homepage_url_desc', 'theme_stardust');
$default = $CFG->wwwroot;
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// About us page URL
$name = 'theme_stardust/footersettigs_aboutus_url';
$title = get_string('footersettigs_aboutus_url', 'theme_stardust');
$description = get_string('footersettigs_aboutus_url_desc', 'theme_stardust');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Contact us page URL
$name = 'theme_stardust/footersettigs_contactus_url';
$title = get_string('footersettigs_contactus_url', 'theme_stardust');
$description = get_string('footersettigs_contactus_url_desc', 'theme_stardust');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Privacy policy page URL
$name = 'theme_stardust/footersettigs_privacypolicy_url';
$title = get_string('footersettigs_privacypolicy_url', 'theme_stardust');
$description = get_string('footersettigs_privacypolicy_url_desc', 'theme_stardust');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Terms of use page URL
$name = 'theme_stardust/footersettigs_termsofuse_url';
$title = get_string('footersettigs_termsofuse_url', 'theme_stardust');
$description = get_string('footersettigs_termsofuse_url_desc', 'theme_stardust');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Accessibility statement page URL
$name = 'theme_stardust/footersettigs_accessibilitystatement_url';
$title = get_string('footersettigs_accessibilitystatement_url', 'theme_stardust');
$description = get_string('footersettigs_accessibilitystatement_url_desc', 'theme_stardust');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// List of courses page URL
$name = 'theme_stardust/footersettigs_listofcourses_url';
$title = get_string('footersettigs_listofcourses_url', 'theme_stardust');
$description = get_string('footersettigs_listofcourses_url_desc', 'theme_stardust');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Davidson Institute Website URL
$name = 'theme_stardust/footersettigs_davidsonsite_url';
$title = get_string('footersettigs_davidsonsite_url', 'theme_stardust');
$description = get_string('footersettigs_davidsonsite_url_desc', 'theme_stardust');
$default = 'https://davidson.weizmann.ac.il/';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Apple Appstore application URL
$name = 'theme_stardust/footersettigs_appstore_url';
$title = get_string('footersettigs_appstore_url', 'theme_stardust');
$description = get_string('footersettigs_appstore_url_desc', 'theme_stardust');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Google Play application URL
$name = 'theme_stardust/footersettigs_googleplay_url';
$title = get_string('footersettigs_googleplay_url', 'theme_stardust');
$description = get_string('footersettigs_googleplay_url_desc', 'theme_stardust');
$default = '';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Must add the page after definiting all the settings!
$settings->add($page);
