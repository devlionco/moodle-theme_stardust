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
    // 'resource' => '',
    // 'scorm' => '',
    // 'survey' => '',
    // 'url' => '',
    // 'wiki' => '',
    // 'workshop' => ''
 );

$hasfhsdrawer = true;
$extraclasses = [];
if (isset($navdraweropen)) {
    $extraclasses[] = 'drawer-open-left';
}
$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks = strpos($blockshtml, 'data-block=') !== false;
$regionmainsettingsmenu = $OUTPUT->region_main_settings_menu();
$urlbackground = $PAGE->theme->setting_file_url('mydashboardbgimage', 'mydashboardbgimage');
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
    'sitesettingsbutton' => false,
    'username' => $USER->firstname.' '.$USER->lastname,
    'allactivities' => get_activities_mydashboard($activitiesconf, 3), // second argument is for num of relevant activities for course cards
    'defaultbg' => $OUTPUT->image_url('banner', 'theme'),
    'imagenocourse' => $OUTPUT->image_url('courses', 'theme'),
    'bgcolor'=> isset($PAGE->theme->settings->mydashboardbgcolor) ? $PAGE->theme->settings->mydashboardbgcolor : null,
    'bgimage'=> $urlbackground ? $urlbackground : $OUTPUT->image_url('banner', 'theme'),
    'time' => time(),
    'helplink' => true
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
