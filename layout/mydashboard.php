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
require_once('mydashboard_background_form.php');
defined('MOODLE_INTERNAL') || die();

user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);
require_once($CFG->libdir . '/behat/lib.php');
// $hasfhsdrawer = isset($PAGE->theme->settings->shownavdrawer) && $PAGE->theme->settings->shownavdrawer == 1;
// if (isloggedin() && $hasfhsdrawer && isset($PAGE->theme->settings->shownavclosed) && $PAGE->theme->settings->shownavclosed == 0) {
//     $navdraweropen = (get_user_preferences('drawer-open-nav', 'true') == 'true');
// } else {
//     $navdraweropen = false;
// }

    /**
     * Returns an array of courses the user is enrolled, and for each course all of the assignments that the user can
     * view within that course.
     *
     * @param array $courseids An optional array of course ids. If provided only assignments within the given course
     * will be returned. If the user is not enrolled in or can't view a given course a warning will be generated and returned.
     * @param array $capabilities An array of additional capability checks you wish to be made on the course context.
     * @param bool $includenotenrolledcourses Wheter to return courses that the user can see even if is not enroled in.
     * This requires the parameter $courseids to not be empty.
     * @return An array of courses and warnings.
     * @since  Moodle 2.4
     */

/**
 * Configuration array for getting course activities
 * key - activity name
 * value - extra fields
 */

 $activitiesconf = array (
    'assign' => 'm.id as activityid, m.course, m.duedate, m.cutoffdate',
    // 'assignment' => '',
    // 'book' => '',
    // 'chat' => '',
    // 'choice' => '',
    // 'data' => '',
    // 'feedback' => '',
    // 'folder' => '',
    // 'forum' => '',
    // 'glossary' => '',
    // 'imscp' => '',
    // 'label' => '',
    // 'lesson' => '',
    // 'lti' => '',
    // 'page' => '',
    'quiz' => '',
    'questionnaire' => 'm.closedate as cutoffdate',
    // 'resource' => '',
    // 'scorm' => '',
    // 'survey' => '',
    // 'url' => '',
    // 'wiki' => '',
    // 'workshop' => ''
 );

 // Add form to update user background img at mydashboardpage
$userbackgroundform = new mydashboard_background_form(new moodle_url($PAGE->url), array(
    //'editoroptions' => $editoroptions,
    //'filemanageroptions' => $filemanageroptions,
    'user' => $USER));
if ($userbackgroundformdata = $userbackgroundform->get_data()) {
    update_background_img($userbackgroundformdata);
    echo '<meta http-equiv="refresh" content="0; url='.$CFG->wwwroot.'/my/" />';
}

/**
 * Updates the provided users backround image at mydashboard page
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
        $fs->delete_area_files($context->id, 'theme_stardust', 'dashbackgroundimg', $formdata->id); // Drop all images in area.
    } else {
        // Save newly uploaded file, this will avoid context mismatch for newly created users.
        $fs->delete_area_files($context->id, 'theme_stardust', 'dashbackgroundimg', $formdata->id); // Drop all images in area.
        file_save_draft_area_files($formdata->dashbackgroundimg, $context->id, 'theme_stardust', 'dashbackgroundimg', $formdata->id, $filemanageroptions);
    }
}

$hasfhsdrawer = true;
$extraclasses = [];
if (isset($navdraweropen)) {
    $extraclasses[] = 'drawer-open-left';
}
$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks = strpos($blockshtml, 'data-block=') !== false;
$regionmainsettingsmenu = $OUTPUT->region_main_settings_menu();

// is teacher marker
$coursecontext = context_course::instance(SITEID);
$isteacher = (has_capability('moodle/course:update', $coursecontext)) ? true : false;

// block from fordson
$blockshtmla = $OUTPUT->blocks('fp-a');
$blockshtmlb = $OUTPUT->blocks('fp-b');
$blockshtmlc = $OUTPUT->blocks('fp-c');
$checkblocka = strpos($blockshtmla, 'data-block=') !== false;
$checkblockb = strpos($blockshtmlb, 'data-block=') !== false;
$checkblockc = strpos($blockshtmlc, 'data-block=') !== false;
//TODO  add to theme_stardust settings
// $hasfpblockregion = ($PAGE->theme->settings->blockdisplay == 1) !== false;
$hasfpblockregion = 1;

$hascourseblocks = false;
if ($checkblocka || $checkblockb || $checkblockc && $isteacher) {
    $hascourseblocks = true;
}

// get background image for mypublic page
$themebackgroundimg = $PAGE->theme->setting_file_url('mydashboardbgimage', 'mydashboardbgimage');
$usercontext = context_user::instance($USER->id);
$fs = get_file_storage();
if ($files = $fs->get_area_files($usercontext->id, 'theme_stardust', 'dashbackgroundimg', $USER->id)) {
    foreach ($files as $file) {
        if ($file->get_filename() != '.'){
            $userbackgroundimg = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename());
        }
    }
}
// define which image will be rendered as background
if (isset($userbackgroundimg)) {
    $mydahboardbackgroundimg = $userbackgroundimg;
} else if (isset($themebackgroundimg)) {
    $mydahboardbackgroundimg = $themebackgroundimg;
} else {
    $mydahboardbackgroundimg = $OUTPUT->image_url('default-bg', 'theme');
}

// get course filter settings from user preferences
$filterstate =  get_user_preferences(null, null, $USER->id);
// get filter direction
if (isset($filterstate['pagemy_filterdirection'])) {
    if ($filterstate['pagemy_filterdirection'] == 1) {
        $direction = 'az';
    } else if ($filterstate['pagemy_filterdirection'] == 0) {
        $direction = 'za';
    }
}
    // get filers classes
if (isset($filterstate['pagemy_filterstate'])) {
    if ($filterstate['pagemy_filterstate'] === 'filter-date') {
        $filtersmy['filter-date'] = 'filter-date '.$direction;
        $filtersmy['filter-abc'] = 'filter-abc';
    } else if ($filterstate['pagemy_filterstate'] === 'filter-abc') {
        $filtersmy['filter-date'] = 'filter-date';
        $filtersmy['filter-abc'] = 'filter-abc '.$direction;
    }
} else {
    $filtersmy['filter-date'] = 'filter-date';
    $filtersmy['filter-abc'] = 'filter-abc';
}

$templatecontext = [
	'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID) , "escape" => false]) ,
    'output' => $OUTPUT,
    'showbacktotop' => isset($PAGE->theme->settings->showbacktotop) && $PAGE->theme->settings->showbacktotop == 1,
    'sidepreblocks' => $blockshtml,
    'hasblocks' => $hasblocks,
    'bodyattributes' => $bodyattributes,
    'navdraweropen' => isset($navdraweropen) ? $navdraweropen : '',
    'hasfhsdrawer' => $hasfhsdrawer,
    // 'hasfhsdrawer' => false,
    'hascourseblocks' => $hascourseblocks, // block in course
    'hasfpblockregion' => $hasfpblockregion,
    'fpablocks' => $blockshtmla,
    'fpbblocks' => $blockshtmlb,
    'fpcblocks' => $blockshtmlc,
    'sitesettingsbutton' => false,
    'username' => $USER->firstname.' '.$USER->lastname,
    'allactivities' => get_activities_mydashboard($activitiesconf, 3), // second argument is for num of relevant activities for course cards
    'defaultbg' => $OUTPUT->image_url('default-bg', 'theme'),
    'imagenocourse' => $OUTPUT->image_url('courses', 'theme'),
    'bgcolor'=> isset($PAGE->theme->settings->mydashboardbgcolor) ? $PAGE->theme->settings->mydashboardbgcolor : null,
    'bgimage'=> $mydahboardbackgroundimg,
    'time' => time(),
    'helplink' => true,
    'filtersmy' => $filtersmy,
    // 'regionmainsettingsmenu' => $regionmainsettingsmenu,
    // 'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu)
];

$PAGE->requires->jquery();
if (isset($PAGE->theme->settings->showbacktotop) && $PAGE->theme->settings->showbacktotop == 1) {
    $PAGE->requires->js('/theme/fordson/javascript/scrolltotop.js');
}
$PAGE->requires->js('/theme/fordson/javascript/scrolltotop.js');
$PAGE->requires->js('/theme/fordson/javascript/tooltipfix.js');

// $PAGE->requires->js_call_amd('theme_stardust/lightslider', 'init');
$PAGE->requires->js_call_amd('theme_stardust/tabweek', 'init');
$PAGE->requires->js_call_amd('theme_stardust/filter', 'init');

$templatecontext['flatnavigation'] = $PAGE->flatnav;
echo $OUTPUT->render_from_template('theme_stardust/mydashboard', $templatecontext);
// always show form to upload user's background
$userbackgroundform->display();
