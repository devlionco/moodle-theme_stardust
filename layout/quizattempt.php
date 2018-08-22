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
//if we are on module view page, return links 'back to course, section and activity'

if ($PAGE->context && $PAGE->context->contextlevel == CONTEXT_MODULE && $PAGE->cm) {
    $courseformat = course_get_format($PAGE->course);
    $courselink = $courseformat->get_view_url(0);
    $textquizattemptbacktocourse = new lang_string('quizattemptbacktocourse', 'theme_stardust');
    $sectionlink = $courseformat->get_view_url($PAGE->cm->sectionnum);
    $textquizattemptbacktosection = new lang_string('quizattemptbacktosection', 'theme_stardust', $courseformat->get_section_name($PAGE->cm->sectionnum));
    $activitylink = $PAGE->cm->url;
    $textquizattemptbacktoactivity = new lang_string('quizattemptbacktocourse', 'theme_stardust');
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
    'backtocourse' => html_writer::link($courselink, $textquizattemptbacktocourse),
    'backtosection' => html_writer::link($sectionlink, $textquizattemptbacktosection),
    'backtoactivity' => html_writer::link($activitylink, $textquizattemptbacktoactivity)
];

$PAGE->requires->jquery();
if (isset($PAGE->theme->settings->showbacktotop) && $PAGE->theme->settings->showbacktotop == 1) {
    $PAGE->requires->js('/theme/fordson/javascript/scrolltotop.js');
    $PAGE->requires->js('/theme/fordson/javascript/scrollspy.js');
}
$PAGE->requires->js('/theme/fordson/javascript/tooltipfix.js');

$templatecontext['flatnavigation'] = $PAGE->flatnav;
echo $OUTPUT->render_from_template('theme_stardust/quizattempt', $templatecontext);
