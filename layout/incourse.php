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
require_once($CFG->dirroot.'/course/lib.php');

/**
 * Returns info about course sections. Is used in header qick navigation
 *
 * @param course_modinfo $courseinfo
 * @param int $currentsectionnum
 *
 * @return array $sectionsinfo - multilevel array with all course sections and pinned sections info (name, url and current if provided)
 */
function get_all_course_sections_info($courseinfo, $currentsectionnum = null) {
    global $PAGE;
    $allcoursesectionsinfo = $courseinfo->get_section_info_all();
    $sectionsinfo = array();
    $courseformat = $courseinfo->get_course()->format;

    // get pinned sctions array for picturelink course format
    if ($courseformat == "picturelink") {
        $plpinnedsecsraw = json_decode(course_get_format($PAGE->course)->get_course()->picturelinkpinnedsections);
        $plpinnedsecs = array();
        foreach ($plpinnedsecsraw as $num => $psec) {
            if ($psec[1] == 1) {
                $plpinnedsecs[] = substr($psec[0], 1);
            }
        }
    }

    foreach ($allcoursesectionsinfo as $secnum => $secinfo) {
        if (!$secinfo->uservisible) continue;   // SG - T-279 - skip not visible for user sections
        $secname = course_get_format($PAGE->course)->get_section_name($secnum);
        $seccustomnum = $secinfo->customnumber;
        if ($courseformat == "stardust") {
          $securl = new moodle_url('/course/view.php', array('id' => $PAGE->course->id, 'sectionid' => $secinfo->id));
        }else {
          $securl = new moodle_url('/course/view.php', array('id' => $PAGE->course->id));
          $securl->set_anchor('section-'.$secnum);
        }
        if (empty($secinfo->pinned)) {
            $sectionsinfo['allcoursesections'][$secnum]['name'] = $secname;
            $sectionsinfo['allcoursesections'][$secnum]['customnumber'] = $seccustomnum;
            $sectionsinfo['allcoursesections'][$secnum]['url'] = $securl;
            if ($secnum == $currentsectionnum) {
                $sectionsinfo['allcoursesections'][$secnum]['current'] = $secname;
            }
        }
        if ($secinfo->pinned) {
            $sectionsinfo['allcoursesectionspinned'][$secnum]['name'] = $secname;
            $sectionsinfo['allcoursesectionspinned'][$secnum]['customnumber'] = $seccustomnum;
            $sectionsinfo['allcoursesectionspinned'][$secnum]['url'] = $securl;
            if ($secnum == $currentsectionnum) {
                $sectionsinfo['allcoursesectionspinned'][$secnum]['current'] = $secname;
            }
        }
        // pinned sections for picturelink format
        if ($courseformat == "picturelink" && in_array($secinfo->id, $plpinnedsecs)) {
            $sectionsinfo['allcoursesectionspinned'][$secnum]['name'] = $secname;
            $sectionsinfo['allcoursesectionspinned'][$secnum]['customnumber'] = $seccustomnum;
            $sectionsinfo['allcoursesectionspinned'][$secnum]['url'] = $securl;
            if ($secnum == $currentsectionnum) {
                $sectionsinfo['allcoursesectionspinned'][$secnum]['current'] = $secname;
            }
        }
    }

    return $sectionsinfo;
}

// get course back link
$courseformat = course_get_format($PAGE->course);
$courselink = $courseformat->get_view_url(0);

// get info for header in course level pages
if ($PAGE->context && $PAGE->context->contextlevel == CONTEXT_COURSE) {
    // get all course sections info here
    $allcoursesectionsinfo = get_all_course_sections_info(get_fast_modinfo($PAGE->course));
}

// get info for header in cm level pages
if ($PAGE->context && $PAGE->context->contextlevel == CONTEXT_MODULE && $PAGE->cm) {
    // define current section
    $currentsectionnum = $PAGE->cm->sectionnum;
    $sectionlink = $courseformat->get_view_url($currentsectionnum);
    $textbacktosection = new lang_string('backtosection', 'theme_stardust', $courseformat->get_section_name($currentsectionnum));
    $activitylink = $PAGE->cm->url;
    $textbacktoactivity = new lang_string('backtoactivity', 'theme_stardust');

    // get all course sections info here
    $courseinfo = $PAGE->cm->get_modinfo();
    $allcoursesectionsinfo = get_all_course_sections_info($courseinfo, $currentsectionnum);

    // get activities in current section
    $allactivitiesarr = $courseinfo->get_sections();
    $allsectionactivities = array();
    foreach ($allactivitiesarr[$currentsectionnum] as $key => $activid) {
        $activinfo = $courseinfo->cms[$activid];
        if (!$activinfo->uservisible) continue;         // SG - T-308 - skip not visible for user activities
        $allsectionactivities[$key]['name'] = $activinfo->name;
        $allsectionactivities[$key]['type'] = $activinfo->modname;
        $allsectionactivities[$key]['url'] = $activinfo->url ? $activinfo->url->out() : '';
        if ($activinfo->modname == 'label') {
            $allsectionactivities[$key]['url'] = 'javascript:void(0)';
            $allsectionactivities[$key]['title'] = get_string('title_no_url', 'theme_stardust');
        }
        if ($activid == $PAGE->cm->id) {
            $allsectionactivities[$key]['current'] = $activinfo->name;
        }
    }
}

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
    'sitesettingsbutton' => true,
    'backtocourseurl' => $courselink,
    'backtosection' => html_writer::link($sectionlink, $textbacktosection),
    'backtoactivity' => html_writer::link($activitylink, $textbacktoactivity),
    'allcoursesections' => $allcoursesectionsinfo['allcoursesections'] = array_values($allcoursesectionsinfo['allcoursesections']),
    'allcoursesectionspinned' => $allcoursesectionsinfo['allcoursesectionspinned'] = array_values($allcoursesectionsinfo['allcoursesectionspinned']),
    'allsectionactivities' => $allsectionactivities = array_values($allsectionactivities),
    'coursecoverimg' => get_courses_cover_images ($PAGE->course)
];

$PAGE->requires->jquery();
if (isset($PAGE->theme->settings->showbacktotop) && $PAGE->theme->settings->showbacktotop == 1) {
    $PAGE->requires->js('/theme/fordson/javascript/scrolltotop.js');
    $PAGE->requires->js('/theme/fordson/javascript/scrollspy.js');
}
$PAGE->requires->js('/theme/fordson/javascript/tooltipfix.js');

$templatecontext['flatnavigation'] = $PAGE->flatnav;

echo $OUTPUT->render_from_template('theme_stardust/incourse', $templatecontext);
