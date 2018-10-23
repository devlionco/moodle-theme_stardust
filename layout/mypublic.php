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
 * A two column layout for the boost theme.
 *
 * @package   theme_boost
 * @copyright 2016 Damyon Wiese
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use core_completion\progress;

require_once('mypublic_avatar_form.php');
require_once('mypublic_background_form.php');

defined('MOODLE_INTERNAL') || die();

global $user;
$canedit = '';
if ($user->id != $USER->id and !is_siteadmin($USER)) {  // Only admins may edit others .
    $canedit = "disabled";
}
$userpicture = new user_picture($user);
$userpicture->size = 150;
$userpictureurl = $userpicture->get_url($PAGE);

/**
 * Get user progress in courses for grades tab
 */
$usercoursesprogress = array_values(enrol_get_users_courses($USER->id, true));
foreach ($usercoursesprogress as $course) {
    //get course progress info
    $course->percentage = round(progress::get_course_progress_percentage($course));
}

// get background image for mypublic page
$usercontext = context_user::instance($user->id);
$fs = get_file_storage();
if ($files = $fs->get_area_files($usercontext->id, 'theme_stardust', 'backgroundimg', $user->id)) {
    foreach ($files as $file) {
        if ($file->get_filename() != '.'){
            $backgroundimg = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
        }
    }
}

// get user's interests - tags
$interests = core_tag_tag::get_item_tags_array('core', 'user', $user->id, core_tag_tag::BOTH_STANDARD_AND_NOT, 0, false);
if ($interests) {
    $user->interests = array_values($interests);
}

// upload custom user fields (birthday and knowledge) to user object
profile_load_data($user);

// add knowledge as array for convenience
if (!empty($user->profile_field_knowledge)) {
    $user->knowledge = json_decode($user->profile_field_knowledge['text']);
    unset($user->profile_field_knowledge);
}

// get profile fields, that are locked by auth plugins and set them disabled status
$authplugin = get_auth_plugin($user->auth);
$fields = get_user_fieldnames();
$locked = array();
$unlockedifempty = array();
// second realization - as array for $jscontext->restrictions
foreach ($fields as $field) {
    // usercannot do much if he is not an admin
    if (!is_siteadmin($USER)) {
        // cannot modify other user's info at all
        if ($user->id != $USER->id) {
            $locked[] = $field;
            // lock also custom fields
            if (!in_array('knowledge', $locked)) $locked[] = 'knowledge';
            if (!in_array('interests', $locked)) $locked[] = 'interests';
            if (!in_array('birthday', $locked)) $locked[] = 'birthday';
        } else {
            // check auth plugin locks
            $configvariable = 'field_lock_' . $field;
            if (isset($authplugin->config->{$configvariable})) {
                if ($authplugin->config->{$configvariable} === 'locked') {
                    $locked[] = $field;
                } else if ($authplugin->config->{$configvariable} === 'unlockedifempty' and $user->{$field}!= '') {
                    $unlockedifempty[] = $field;
                }
            }
        }
        // anyway - if not admin - cannot modify username, idnumber (passport) and fullname (firstname + lastname)
        if (!in_array('username', $locked)) $locked[] = 'username';
        // if (!in_array('idnumber', $locked)) $locked[] = 'idnumber'; // lock unlock manually
        if (!in_array('fullname', $locked)) $locked[] = 'fullname';
    } // !site admin
}

// first realization (for form) with disabled status -- SG TOREMOVE lately if jscontext works fine
// foreach ($fields as $field) {
//     $configvariable = 'field_lock_' . $field;
//     if (isset($authplugin->config->{$configvariable})) {
//         if ($authplugin->config->{$configvariable} === 'locked') {
//             $fieldstatus = "{$field}_status";
//             $user->{$fieldstatus} = 'disabled';
//         } else if ($authplugin->config->{$configvariable} === 'unlockedifempty' and $user->{$field}!= '') {
//             $fieldstatus = "{$field}_status";
//             $user->{$fieldstatus} = 'disabled';
//         }
//     }
// }



user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);
require_once($CFG->libdir . '/behat/lib.php');
// $hasfhsdrawer = isset($PAGE->theme->settings->shownavdrawer) && $PAGE->theme->settings->shownavdrawer == 1;
// if (isloggedin() && $hasfhsdrawer && isset($PAGE->theme->settings->shownavclosed) && $PAGE->theme->settings->shownavclosed == 0) {
//     $navdraweropen = (get_user_preferences('drawer-open-nav', 'true') == 'true');
// } else {
//     $navdraweropen = false;
// }
$hasfhsdrawer = true;
$extraclasses = [];
if ($navdraweropen) {
    $extraclasses[] = 'drawer-open-left';
}
$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks = strpos($blockshtml, 'data-block=') !== false;
$regionmainsettingsmenu = $OUTPUT->region_main_settings_menu();
$mypublicdefaultbgimgurl = $OUTPUT->image_url('profilebg', 'theme');
$templatecontext = [
	'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID) , "escape" => false]) ,
    'output' => $OUTPUT,
    'showbacktotop' => isset($PAGE->theme->settings->showbacktotop) && $PAGE->theme->settings->showbacktotop == 1,
    'sidepreblocks' => $blockshtml,
    'hasblocks' => $hasblocks,
    'bodyattributes' => $bodyattributes,
    'navdraweropen' => $navdraweropen,
    'hasfhsdrawer' => $hasfhsdrawer,
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    'userinfo' => $user,
    'canedit' => $canedit,
    'userpictureurl' => $userpictureurl,
    'usercoursesprogress' => $usercoursesprogress,
    'helplink' => true,
    'backgroundimg' => isset($backgroundimg) ? $backgroundimg : $mypublicdefaultbgimgurl
];

// create $jscontext, which later send as param to js_call_amd (mypublicpage)
$jsuser = clone($user);                         // clone user object to avoid its modification
$jsuser->locked = $locked;                      // add locked fields array
$jsuser->unlockedifempty = $unlockedifempty;    // add unlockedifempty fields array
unset ($jsuser->password);                      // remove password hash from the object
$jscontext = json_encode($jsuser);              // make JSON

// echo '<pre>'.print_r($jsuser,1).'</pre>'; exit();
$PAGE->requires->jquery();
if (isset($PAGE->theme->settings->showbacktotop) && $PAGE->theme->settings->showbacktotop == 1) {
    $PAGE->requires->js('/theme/fordson/javascript/scrolltotop.js');
}
$PAGE->requires->js('/theme/fordson/javascript/scrolltotop.js');
$PAGE->requires->js('/theme/fordson/javascript/tooltipfix.js');
$PAGE->requires->js_call_amd('theme_stardust/mypublicpage', 'init', array($jscontext));

$templatecontext['flatnavigation'] = $PAGE->flatnav;
echo $OUTPUT->render_from_template('theme_stardust/mypublic', $templatecontext);

// Add form to update user avatar
$useravatarform = new mypublic_avatar_form(new moodle_url($PAGE->url), array(
    //'editoroptions' => $editoroptions,
    'filemanageroptions' => $filemanageroptions,
    'user' => $user));

if ($useravatarformdata = $useravatarform->get_data()) {
    core_user::update_picture($useravatarformdata);
    echo "<meta http-equiv='refresh' content='0; url=/user/profile.php' />";
}
$useravatarform->display();

// Add form to update user background img at mypublic page
$userbackgroundform = new mypublic_background_form(new moodle_url($PAGE->url), array(
    //'editoroptions' => $editoroptions,
    //'filemanageroptions' => $filemanageroptions,
    'user' => $user));

if ($userbackgroundformdata = $userbackgroundform->get_data()) {
    update_background_img($userbackgroundformdata);
    echo "<meta http-equiv='refresh' content='0; url=/user/profile.php' />";
    //redirect(new moodle_url('/user/profile.php', array('id' => $user->id)));
}
$userbackgroundform->display();


/**
 * Updates the provided users backround image at mypublic page
 *
 * @param stdClass $formdata An object that contains  information from form
 * @param array $filemanageroptions
 * @return bool True if the user was updated, false if it stayed the same.
 */
function update_background_img(stdClass $formdata, $filemanageroptions = array()) {
    global $CFG, $DB;

    $context = context_user::instance($formdata->id, MUST_EXIST);
    $user = core_user::get_user($formdata->id, 'id', MUST_EXIST);

    // Get file_storage to process files.
    $fs = get_file_storage();
    if (!empty($formdata->deletebackgroundimg)) {
        // The user has chosen to delete the selected background
        $fs->delete_area_files($context->id, 'theme_stardust', 'backgroundimg', $formdata->id); // Drop all images in area.
    } else {
        // Save newly uploaded file, this will avoid context mismatch for newly created users.
        $fs->delete_area_files($context->id, 'theme_stardust', 'backgroundimg', $formdata->id); // Drop all images in area.
        file_save_draft_area_files($formdata->backgroundimg, $context->id, 'theme_stardust', 'backgroundimg', $formdata->id, $filemanageroptions);
    }
}
