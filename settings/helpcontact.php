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

$page = new admin_settingpage('theme_stardust_help_contacts', get_string('help_contacts_tab', 'theme_stardust'));

// Help contacts roles heading
$name = 'theme_stardust/help_contacts_header';
$heading = get_string('help_contacts_header', 'theme_stardust');
$information = get_string('help_contacts_header_desc', 'theme_stardust');
$setting = new admin_setting_heading($name, $heading, $information);
$page->add($setting);


// Add roles, whos contacs will be set in teacher contact block.
// $roles = get_roles_with_capability('moodle/course:update'); // Add only roles with some capability.
// $roles = role_fix_names($roles);
$roles = role_get_names(); // Get all system roles.
$choices = array();
foreach ($roles as $id => $role) {
    if ($id != 16) { // Do not show Supporter role. It is used by default.
        $choices[$id] = $role->localname;
    }
}
$defaultchoices = [3 => 'editingteacher']; // By defaut - editingteacher role is defined.
$page->add(new admin_setting_configmulticheckbox('theme_stardust/help_contact_roles',
     get_string('help_contact_roles','theme_stardust'),
     get_string('help_contact_roles_desc', 'theme_stardust'),
     $defaultchoices,
     $choices));

$name = 'theme_stardust/technical_support_email';
$title = get_string('technical_support_email', 'theme_stardust');
$description = get_string('technical_support_email_description', 'theme_stardust');
$default = 'moodle.davidson@weizmann.ac.il';
$setting = new admin_setting_configtext($name, $title, $description, $default);
$setting->set_updatedcallback('theme_reset_all_caches');
$page->add($setting);

// Must add the page after definiting all the settings!
$settings->add($page);
