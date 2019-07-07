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

$page = new admin_settingpage('theme_stardust_login', get_string('loginheading', 'theme_stardust'));

// Enable/disable custom message
$name = 'theme_stardust/showloginmessage';
$title = get_string('showloginmessage', 'theme_stardust');
$description = get_string('showloginmessage_desc', 'theme_stardust');
$default = 0;
$setting = new admin_setting_configcheckbox($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Message EN language
$name = 'theme_stardust/loginmessageen';
$title = get_string('loginmessageen', 'theme_stardust');
$description = get_string('loginmessageen_desc', 'theme_stardust');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Message HE language
$name = 'theme_stardust/loginmessagehe';
$title = get_string('loginmessagehe', 'theme_stardust');
$description = get_string('loginmessagehe_desc', 'theme_stardust');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Message AR language
$name = 'theme_stardust/loginmessagear';
$title = get_string('loginmessagear', 'theme_stardust');
$description = get_string('loginmessagear_desc', 'theme_stardust');
$default = '';
$setting = new admin_setting_configtextarea($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Must add the page after definiting all the settings!
$settings->add($page);
