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
 * Main settings file.
 *
 * @package    theme_stardust
 * @copyright  2016 Chris Kenniburg
 * @credits    theme_boost - MoodleHQ
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* THEME_fordson BUILDING NOTES
 * =============================
 * Settings have been split into separate files, which are called from
 * this central file. This is to aid ongoing development as I find it
 * easier to work with multiple smaller function-specific files than
 * with a single monolithic settings file.
 * This may be a personal preference and it would be quite feasible to
 * bring all lib functions back into a single central file if another
 * developer prefered to work in that way.
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    // Note new tabs layout for admin settings pages.
    $settings = new theme_boost_admin_settingspage_tabs('themesettingstardust', get_string('configtitle', 'theme_stardust'));

    require($CFG->dirroot .'/theme/fordson/settings/presets_settings.php');
    require($CFG->dirroot .'/theme/fordson/settings/colours_settings.php');
    require($CFG->dirroot .'/theme/fordson/settings/menu_settings.php');
    require($CFG->dirroot .'/theme/fordson/settings/content_settings.php');
    require($CFG->dirroot .'/theme/fordson/settings/image_settings.php');
    require($CFG->dirroot .'/theme/fordson/settings/footer_settings.php');
    require($CFG->dirroot .'/theme/fordson/settings/fpicons_settings.php');
    require($CFG->dirroot .'/theme/fordson/settings/markettiles_settings.php');
    require($CFG->dirroot .'/theme/fordson/settings/slideshow_settings.php');
    // OCJ HILLBROOK MOD
    require($CFG->dirroot .'/theme/fordson/settings/modchooser_settings.php');
    require($CFG->dirroot .'/theme/fordson/settings/customlogin_settings.php');


    // My dahsboard setup
    $page = new admin_settingpage('theme_stardust_mydashboardsettigs', get_string('mydashboardsettigs', 'theme_stardust'));

    // Description
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

//    // Show / hide the "Units (study units)" box (default = display)
//    $page->add(new admin_setting_configcheckbox('theme_stardust/display_units', get_string('display_units','theme_stardust'),
//    get_string('display_units_desc', 'theme_stardust'), 1));
//
//    // Show / hide message block (default = display)
//    $page->add(new admin_setting_configcheckbox('theme_stardust/display_messages', get_string('display_messages','theme_stardust'),
//    get_string('display_messages_desc', 'theme_stardust'), 1));
//
//    // Show / hide the "Grades - Medals - Certificates" box (default = display)
//    $page->add(new admin_setting_configcheckbox('theme_stardust/display_grades', get_string('display_grades','theme_stardust'),
//    get_string('display_grades_desc', 'theme_stardust'), 1));
    
    
    

    // Background color.
    // $name = 'theme_stardust/mydashboardbgcolor';
    // $title = get_string('mydashboardbgcolor', 'theme_stardust');
    // $description = get_string('mydashboardbgcolor_desc', 'theme_stardust');
    // $setting = new admin_setting_configcolourpicker($name, $title, $description, '#87CEFA'); //lightskyblue - default color
    // $setting->set_updatedcallback('theme_reset_all_caches');
    // $page->add($setting);

    // Must add the page after definiting all the settings!
    $settings->add($page);

}
