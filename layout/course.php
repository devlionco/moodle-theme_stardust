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
require_once($CFG->dirroot . '/mod/attendance/classes/summary.php');
require_once($CFG->libdir . "/completionlib.php");
global $DB,$COURSE, $USER;
$course = $PAGE->course;

//get attednance info
$attmodid = $DB->get_record('modules', array('name' => 'attendance'), 'id')->id; // get attendance module id in system
$attid = $DB->get_record('course_modules', array('course' => $course->id, 'module' => $attmodid), 'instance', IGNORE_MULTIPLE)->instance; // get first attedndance instance on current course
if (!$attid) {
    // don't get attendance info
} else {
    $attsummaryobj = new mod_attendance_summary($attid, $USER->id); // get attendance summary object for current user
    $attendanceinfo = $attsummaryobj->get_all_sessions_summary_for($USER->id);

    $attendanceinfo->takensessionspoints = round($attendanceinfo->takensessionspoints);
    $attendanceinfo->allsessionsmaxpoints = round($attendanceinfo->allsessionsmaxpoints);

    $attendanceinfo->percent = 0;
    $attendanceinfo->angle = 0;
    if ($attendanceinfo->allsessionsmaxpoints) {
      $attendanceinfo->percent = round($attendanceinfo->takensessionspoints/ $attendanceinfo->allsessionsmaxpoints, 2 ,PHP_ROUND_HALF_UP);
      $attendanceinfo->angle = round(M_PI * 2 * $attendanceinfo->percent, 2, PHP_ROUND_HALF_UP);
    }

}

/**
 *  get studied units copmletion info
 */

// get all current user's completions on current course
$usercourseallcmcraw = $DB->get_records_sql("
SELECT
    cmc.*
FROM
    {course_modules} cm
    INNER JOIN {course_modules_completion} cmc ON cmc.coursemoduleid=cm.id
WHERE
    cm.course=? AND cmc.userid=?", array($course->id, $USER->id));
$usercmscompletions = array();
foreach ($usercourseallcmcraw as $record) {
    //$usercourseallcmc[$record->coursemoduleid] = (array)$record;
    if ($record->completionstate <> 0) {
        $usercmscompletions[] = $record->coursemoduleid;
    }
}

// get current course's completable cms
$ccompetablecms = array();
$coursefminfo = get_fast_modinfo($course);
foreach ($coursefminfo->get_cms() as $cm) {
    if ($cm->completion != COMPLETION_TRACKING_NONE && !$cm->deletioninprogress) {
        $ccompetablecms[] = $cm->id;
    }
}
//print_object($ccompetablecms);


$sections = $coursefminfo->get_sections(); // get current course's sections
// remove pinned sections and subsections from all sections array
foreach ($sections as $sid => $sval) {
    $secinfo = course_get_format($course->id)->get_section($sid);
    if (!empty($secinfo->pinned)) {
        unset($sections[$sid]);
    }
    if (!empty($secinfo->parent)) {
        unset($sections[$sid]);
    }
}
$sectionscount = count($sections); // count all sections in the course

$completedsectionscount = 0; // zero competed section in the course

// iterate every section in the course
foreach ($sections as $secid=>$scms) {
    $completedactivitiescount = 0;

    // iterate every cm in current section to remove uncompetable items
    foreach ($scms as $arid=>$scmid) {
        if (!in_array($scmid, $ccompetablecms)) {
        unset($scms[$arid]); // unset cms that are not  completable
        } else {
            if (in_array($scmid, $usercmscompletions)) {
                $completedactivitiescount++; // if cm is compledted - count it
             }
        }
    }
    $cmsinsectioncount = count($scms);

    if ($cmsinsectioncount == $completedactivitiescount) {
        $completedsectionscount++; // if competable cms are all competed - count section as competed
    }
}

$percent = 0;
$angle = 0;
if ($sectionscount) {
  $percent = round($completedsectionscount/$sectionscount, 2, PHP_ROUND_HALF_UP);
  $angle = round(M_PI * 2 * $percent, 2, PHP_ROUND_HALF_UP);
}

$sectionscompletion = array (
    "completed" => $completedsectionscount,
    "allsections" => $sectionscount,
    "percent" => $percent,
    "angle" => $angle
);

$hasfhsdrawer = isset($PAGE->theme->settings->shownavdrawer) && $PAGE->theme->settings->shownavdrawer == 1;
if (isloggedin() && $hasfhsdrawer && isset($PAGE->theme->settings->shownavclosed) && $PAGE->theme->settings->shownavclosed == 0) {
    $navdraweropen = (get_user_preferences('drawer-open-nav', 'true') == 'true');
} else {
    $navdraweropen = false;
}
$extraclasses = [];
if ($navdraweropen) {
    $extraclasses[] = 'drawer-open-left';
}
$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$blockshtml = $OUTPUT->blocks('side-pre');
$hasblocks = strpos($blockshtml, 'data-block=') !== false;
$regionmainsettingsmenu = $OUTPUT->region_main_settings_menu();
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
if ($checkblocka || $checkblockb || $checkblockc) {
    $hascourseblocks = true;
}
//get course format
$courseformat = course_get_format($course->id)->get_format_options();

// get teacher's course message
$coursemessage = $DB->get_record('theme_stardust_messages', array ('courseid' => $course->id));
$coursemessage->buttonstatus = ($coursemessage->status == 1) ? 'show' : '';                         // define teacher's show/hide button (eye) class
$coursemessage->buttontitle= ($coursemessage->status == 1) ? 'To hide message' : 'To show message'; // define teacher's show/hide button (eye) title
$coursemessage->teachmessageshowhide= ($coursemessage->status == 1) ? '' : 'style = opacity:0.5;';  // define teacher's message box style with opacity
$coursemessage->studmessageshowhide= ($coursemessage->status == 1) ? '' : 'style = display:none;';  // define student's message box style: display or not
//$coursemessage->messageboxstatus = ($coursemessage->status == 1) ? '' : 'hide';                   // define student's message box status class

// is teacher marker
$coursecontext = context_course::instance($course->id);
$isteacher = (has_capability('moodle/course:update', $coursecontext)) ? true : false;

$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID) , "escape" => false]) ,
    'output' => $OUTPUT,
    'showbacktotop' => isset($PAGE->theme->settings->showbacktotop) && $PAGE->theme->settings->showbacktotop == 1,
    'sidepreblocks' => $blockshtml,
    'hasblocks' => $hasblocks,
    'bodyattributes' => $bodyattributes,
    'navdraweropen' => $navdraweropen,
    'hasfhsdrawer' => $hasfhsdrawer,
    'hascourseblocks' => $hascourseblocks, // block in course
    'hasfpblockregion' => $hasfpblockregion,
    'fpablocks' => $blockshtmla,
    'fpbblocks' => $blockshtmlb,
    'fpcblocks' => $blockshtmlc,
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    'sitesettingsbutton' => true,
    'coursename' => $course->shortname,
    'coursfullname' => $course->fullname,
    'display_units' => (isset($courseformat['displayunits'])) ? $courseformat['displayunits'] : false,
    'display_messages' => (isset($courseformat['displaymessages'])) ? $courseformat['displaymessages'] : false,
    'display_grades' => (isset($courseformat['displaygrades'])) ? $courseformat['displaygrades'] : false,
    'showbagestag' => (isset($courseformat['showbagestag'])) ? $courseformat['showbagestag'] : false,
    'showcertificatestag' => (isset($courseformat['showcertificatestag'])) ? $courseformat['showcertificatestag'] : false,
    'attendanceinfo' => isset($attendanceinfo) ? $attendanceinfo : null,
    'sectionscompletion' => $sectionscompletion,
    'showgrades' => isset($course->showgrades) ? $course->showgrades: false,
    'coursemessage' => $coursemessage,
    'isteacher' => $isteacher,
    'userid' => $USER->id,
    'courseid' => $course->id
];

$PAGE->requires->jquery();
if (isset($PAGE->theme->settings->showbacktotop) && $PAGE->theme->settings->showbacktotop == 1) {
    $PAGE->requires->js('/theme/fordson/javascript/scrolltotop.js');
    $PAGE->requires->js('/theme/fordson/javascript/scrollspy.js');
}
$PAGE->requires->js('/theme/fordson/javascript/tooltipfix.js');

$templatecontext['flatnavigation'] = $PAGE->flatnav;
// echo $OUTPUT->render_from_template('theme_stardust/columns-course', $templatecontext);
echo $OUTPUT->render_from_template('theme_stardust/course', $templatecontext);
