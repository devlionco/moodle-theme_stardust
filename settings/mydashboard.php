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

$page = new admin_settingpage('theme_stardust_mydashboardsettigs', get_string('mydashboardsettigs', 'theme_stardust'));

// My dahsboard heading
$name = 'theme_stardust/mydashboardsettigs';
$heading = get_string('mydashboardsettigs', 'theme_stardust');
$information = get_string('mydashboardsettigs_desc', 'theme_stardust');
$setting = new admin_setting_heading($name, $heading, $information);
$page->add($setting);

// Background file
$name = 'theme_stardust/mydashboardbgimage';
$title = get_string('mydashboardbgimage', 'theme_stardust');
$description = get_string('mydashboardbgimage_desc', 'theme_stardust');
$setting = new admin_setting_configstoredfile($name, $title, $description, 'mydashboardbgimage');
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Must add the page after definiting all the settings!
$settings->add($page);
