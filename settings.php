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

    require($CFG->dirroot .'/theme/stardust/settings/presets_settings.php');
    require($CFG->dirroot .'/theme/stardust/settings/presets_adjustments_settings.php');
    require($CFG->dirroot .'/theme/stardust/settings/image_settings.php'); 
    require($CFG->dirroot .'/theme/stardust/settings/colours_settings.php');
    require($CFG->dirroot .'/theme/stardust/settings/content_settings.php');
    require($CFG->dirroot .'/theme/stardust/settings/menu_settings.php');
    require($CFG->dirroot .'/theme/stardust/settings/fpicons_settings.php');
    require($CFG->dirroot .'/theme/stardust/settings/modchooser_settings.php');
    require($CFG->dirroot .'/theme/stardust/settings/slideshow_settings.php');
    require($CFG->dirroot .'/theme/stardust/settings/markettiles_settings.php');
    //require($CFG->dirroot .'/theme/stardust/settings/footer_settings.php');
    // OCJ HILLBROOK MOD
    //require($CFG->dirroot .'/theme/stardust/settings/customlogin_settings.php');


    /* My dahsboard setup PAGE */
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

    /* My dahsboard setup PAGE */
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

    /* Custom login */
    $page = new admin_settingpage('theme_stardust_customlogin', get_string('customloginheading', 'theme_stardust'));

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

    /* FOOTER setup PAGE */
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

}
