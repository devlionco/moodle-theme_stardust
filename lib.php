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
 *
 * @package   theme_stardust
 * @copyright 2013 Moodle, moodle.org
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_completion\progress;
require_once("$CFG->dirroot/mod/assign/locallib.php");
require_once("{$CFG->libdir}/completionlib.php");

/**
 * Parses CSS before it is cached.
 *
 * This function can make alterations and replace patterns within the CSS.
 *
 * @param string $css The CSS
 * @param theme_config $theme The theme config object.
 * @return string The parsed CSS The parsed CSS.
 */
function theme_stardust_process_css($css, $theme) {
    global $OUTPUT;

    // Set the background image for the logo.
    $logo = $OUTPUT->get_logo_url(null, 75);
    $css = theme_stardust_set_logo($css, $logo);

    // Set custom CSS.
    if (!empty($theme->settings->customcss)) {
        $customcss = $theme->settings->customcss;
    } else {
        $customcss = null;
    }
    $css = theme_stardust_set_customcss($css, $customcss);

    return $css;
}

/**
 * Adds the logo to CSS.
 *
 * @param string $css The CSS.
 * @param string $logo The URL of the logo.
 * @return string The parsed CSS
 */
function theme_stardust_set_logo($css, $logo) {
    $tag = '[[setting:logo]]';
    $replacement = $logo;
    if (is_null($replacement)) {
        $replacement = '';
    }

    $css = str_replace($tag, $replacement, $css);

    return $css;
}

/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function theme_stardust_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    //if ($context->contextlevel == CONTEXT_SYSTEM and ($filearea === 'logo' || $filearea === 'smalllogo')) {
    if ($context->contextlevel == CONTEXT_SYSTEM || $context->contextlevel == CONTEXT_USER) {
        $theme = theme_config::load('stardust');
        // By default, theme files must be cache-able by both browsers and proxies.

        // serve background image at mypublic page
        if ($filearea === 'backgroundimg' || $filearea === 'dashbackgroundimg') {
            $itemid = array_shift($args);
            $filename = array_pop($args); // The last item in the $args array.
            if (!$args) {
                $filepath = '/'; // $args is empty => the path is '/'
            } else {
                $filepath = '/'.implode('/', $args).'/'; // $args contains elements of the filepath
            }

            // Retrieve the file from the Files API.
            $fs = get_file_storage();
            $file = $fs->get_file($context->id, 'theme_stardust', $filearea, $itemid, $filepath, $filename);
            if (!$file) {
                return false; // The file does not exist.
            }
            send_file($file, 86400, 0, $forcedownload, $options);
        }


        if (!array_key_exists('cacheability', $options)) {
            $options['cacheability'] = 'public';
        }
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    } else {
        send_file_not_found();
    }
}

/**
 * Adds any custom CSS to the CSS before it is cached.
 *
 * @param string $css The original CSS.
 * @param string $customcss The custom CSS to add.
 * @return string The CSS which now contains our custom CSS.
 */
function theme_stardust_set_customcss($css, $customcss) {
    $tag = '[[setting:customcss]]';
    $replacement = $customcss;
    if (is_null($replacement)) {
        $replacement = '';
    }

    $css = str_replace($tag, $replacement, $css);

    return $css;
}

/**
 * Returns an object containing HTML for the areas affected by settings.
 *
 * Do not add stardust specific logic in here, child themes should be able to
 * rely on that function just by declaring settings with similar names.
 *
 * @param renderer_base $output Pass in $OUTPUT.
 * @param moodle_page $page Pass in $PAGE.
 * @return stdClass An object with the following properties:
 *      - navbarclass A CSS class to use on the navbar. By default ''.
 *      - heading HTML to use for the heading. A logo if one is selected or the default heading.
 *      - footnote HTML to use as a footnote. By default ''.
 */
function theme_stardust_get_html_for_settings(renderer_base $output, moodle_page $page) {
    global $CFG;
    $return = new stdClass;

    $return->navbarclass = '';
    if (!empty($page->theme->settings->invert)) {
        $return->navbarclass .= ' navbar-inverse';
    }

    // Only display the logo on the front page and login page, if one is defined.
    if (!empty($page->theme->settings->logo) &&
            ($page->pagelayout == 'frontpage' || $page->pagelayout == 'login')) {
        $return->heading = html_writer::tag('div', '', array('class' => 'logo'));
    } else {
        $return->heading = $output->page_heading();
    }

    $return->footnote = '';
    if (!empty($page->theme->settings->footnote)) {
        $return->footnote = '<div class="footnote text-center">'.format_text($page->theme->settings->footnote).'</div>';
    }

    return $return;
}

/**
 * All theme functions should start with theme_stardust_
 * @deprecated since 2.5.1
 */
function stardust_process_css() {
    throw new coding_exception('Please call theme_'.__FUNCTION__.' instead of '.__FUNCTION__);
}

/**
 * All theme functions should start with theme_stardust_
 * @deprecated since 2.5.1
 */
function stardust_set_logo() {
    throw new coding_exception('Please call theme_'.__FUNCTION__.' instead of '.__FUNCTION__);
}

/**
 * All theme functions should start with theme_stardust_
 * @deprecated since 2.5.1
 */
function stardust_set_customcss() {
    throw new coding_exception('Please call theme_'.__FUNCTION__.' instead of '.__FUNCTION__);
}


/**
 * Function for getting all configured activities in defined courses for defined user
 *
 * @param array $courseids An optional array of course ids. If provided only activities within the given course
 * will be returned. If the user is not enrolled in or can't view a given course a warning will be generated and returned.
 * @param array $capabilities An array of additional capability checks you wish to be made on the course context.
 * @param bool $includenotenrolledcourses Wheter to return courses that the user can see even if is not enroled in.
 * This requires the parameter $courseids to not be empty.
 * @param array $activitiesconf An array with configured activiries names and thier extrafields needed
 * @return An array of courses and warnings.
 * @since  Moodle 2.4
 */

function get_activities_mydashboard($activitiesconf = array(), $numofrelevantactivities = 4, $courseids = array(), $capabilities = array(), $includenotenrolledcourses = false) {
    global $USER, $DB, $CFG, $OUTPUT;

    // default params for getting courses
    $params = array(
            'courseids' => $courseids,
            'capabilities' => $capabilities,
            'includenotenrolledcourses' => $includenotenrolledcourses
    );

    $warnings = array();
    $courses = array();
    $fields = 'sortorder,shortname,fullname,timemodified,enddate';

    // If the courseids list is empty, we return only the courses where the user is enrolled in.
    if (empty($params['courseids'])) {
        $courses = enrol_get_users_courses($USER->id, true, $fields);
        $courseids = array_keys($courses);
    } else if ($includenotenrolledcourses) {
        // In this case, we don't have to check here for enrolmnents. Maybe the user can see the course even if is not enrolled.
        $courseids = $params['courseids'];
    } else {
        // We need to check for enrolments.
        $mycourses = enrol_get_users_courses($USER->id, true, $fields);
        $mycourseids = array_keys($mycourses);

        foreach ($params['courseids'] as $courseid) {
            if (!in_array($courseid, $mycourseids)) {
                unset($courses[$courseid]);
                $warnings[] = array(
                    'item' => 'course',
                    'itemid' => $courseid,
                    'warningcode' => '2',
                    'message' => 'User is not enrolled or does not have requested capability'
                );
            } else {
                $courses[$courseid] = $mycourses[$courseid];
            }
        }
        $courseids = array_keys($courses);
    }

    foreach ($courseids as $cid) {

        $context = context_course::instance($cid);
        $courses[$cid]->contextid = $context->id;
        if (count($params['capabilities']) > 0 && !has_all_capabilities($params['capabilities'], $context)) {
            unset($courses[$cid]);
        }
    }

    // build courses array
    $coursearray = array();
    // begin courses iteration
    foreach ($courses as $id => $course) {

        // get user status at current course - teacher or student
        $cccontext = context_course::instance($course->id);
        $isteacher = (has_capability('moodle/course:update', $cccontext) ? true : false);

        //build activities array
        $activities = array();

        //iterate through all configured activities inside particular course
        foreach ($activitiesconf as $activityname => $extrafields) {

            // Get activities in course one by one
            if ($modules = get_coursemodules_in_course($activityname, $courses[$id]->id, $extrafields)) {

                foreach ($modules as $module) {
                    $context = context_module::instance($module->id);

                    // add additional content and modify added into activityinfo
                    $activityinfo = set_icon_style_for_activity($module);

                    // filter modules. Show only visible and available to current user activities
                    $cminfo = get_fast_modinfo($course->id)->cms[$module->id];
                    if (!$cminfo->uservisible || !$cminfo->is_visible_on_course_page()) {
                        continue;
                    }

                    // dont't show submitted assignments
                    if (is_assign_submitted($module)) {
                        continue;
                    }

                    // add all activityinfo to general array
                    // $activities[$activityname][] = $activityinfo; // with activityname sorting
                    $activities[] = $activityinfo; // without activityname sorting
                } // end foreach module
            } // end if module exists
        } // end foreach activity

        $relevantactivities = get_relevant_activities($activities, $numofrelevantactivities); // slice relevant activities to this amount

        // get courses cover images
        $courseobj = new course_in_list($course);
        $coursecoverimgurl = '';
        foreach ($courseobj->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            $coursecoverimgurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), null, $file->get_filepath(), $file->get_filename());
        }
        if (empty($coursecoverimgurl)) {
            $coursecoverimgurl = $OUTPUT->image_url('banner', 'theme'); // define default course cover image in theme's pix folder
        }

        // get courses completeinon info
        $coursecomplinfo = new completion_info($course);
        $enabledforcourse = $coursecomplinfo->is_enabled();
        if (!$enabledforcourse) {
            $coursecomplstate = ((time() > $courses[$id]->enddate) && ($courses[$id]->enddate > 0)   ? true : false);
        } else {
            $coursecomplstate = $coursecomplinfo->is_course_complete($USER->id);
        }

        //get course progress info
        $percentage = progress::get_course_progress_percentage($course);

        if ($coursecomplstate){
            $coursearray['coursefinished'][]= array(
                'id' => $courses[$id]->id,
                'fullname' => format_string($courses[$id]->fullname, $course->contextid),
                'shortname' => format_string($courses[$id]->shortname, $course->contextid),
                'timemodified' => $courses[$id]->timemodified,
                'startdate' => $courses[$id]->startdate,
                'enddate' => $courses[$id]->enddate,
                'coursecoverimg' => $coursecoverimgurl->out(),
                'progress' => $percentage,
                'isteacher' => $isteacher,
                'activities' => $activities,
                'relevantactivities' => $relevantactivities
            );
        }else {
            $coursearray['courseactive'][]= array(
                'id' => $courses[$id]->id,
                'fullname' => format_string($courses[$id]->fullname, $course->contextid),
                'shortname' => format_string($courses[$id]->shortname, $course->contextid),
                'timemodified' => $courses[$id]->timemodified,
                'startdate' => $courses[$id]->startdate,
                'enddate' => $courses[$id]->enddate,
                'coursecoverimg' => $coursecoverimgurl->out(),
                'progress' => $percentage,
                'isteacher' => $isteacher,
                'activities' => $activities,
                'relevantactivities' => $relevantactivities
            );
        }

    } // end courses iteration

    $result = array(
        'courses' => $coursearray,
        'warnings' => $warnings
    );
    // echo '<pre>'.print_r($result,1).'</pre>';exit();
    return $result;
}

/**
 * Function defines activity (module) status, based on cutoffdate
 *
 * @param $module
 *
 * @return array
 */
function stardust_activity_status($module) {
    global $DB, $USER;

    //get module completion state
    $cmcomplstateraw = $DB->get_record('course_modules_completion', array('coursemoduleid' => $module->id,'userid'=>$USER->id), 'completionstate');
    $cmcomplstate = $cmcomplstateraw ? true : false; // completed or not activity

    // get module overrides
    $module = get_module_overrides($module);

    $activitystatus = array();

    $added = $module->added;
    $cutoffdate = isset($module->cutoffdate) ? $module->cutoffdate : null;
    $duedate = isset($module->duedate) ? $module->duedate : null;
    $currenttime = time();
    $openforsubmission = false;
    $actionwithtask = false;
    $turntotheteacher = false;

    $mincutoffdate =  ($cutoffdate * $duedate == 0) ? max($cutoffdate, $duedate) :  min($cutoffdate, $duedate);

    if (!empty($mincutoffdate)) {

        $timeratio = round(($currenttime - $added) / ($mincutoffdate - $added) * 100, 0, PHP_ROUND_HALF_DOWN);
        $timeline = ($timeratio > 100) ? 100 : $timeratio;

        if ($cmcomplstate) {
          $modstyle = 'mod_green';
          // $viewcutoffdate = get_string('cut_of_date', 'theme_stardust');
          $modstatus =  html_writer::tag('div', '', array('class' => 'mod_complete'));
          // $turntotheteacher = true;
        }
        elseif ($mincutoffdate - $currenttime <= 0) {
            $modstyle = 'mod_red';
            $modstatus = get_string('cut_of_date', 'theme_stardust');
            $turntotheteacher = true;
        // one day before assignment
        }elseif ( 0 < ($mincutoffdate - $currenttime) &&  ($mincutoffdate - $currenttime) <= (1*24*60*60)) {
            $modstyle = 'mod_orange';
            $modstatus = get_string('one_days_before_assignment', 'theme_stardust');
            $openforsubmission = true;
        // two day before assignment
        }elseif ( (1*24*60*60) < ($mincutoffdate - $currenttime) &&  ($mincutoffdate - $currenttime) <= (2*24*60*60)) {
            $modstyle = 'mod_orange';
            $modstatus = get_string('two_days_before_assignment', 'theme_stardust');
            $openforsubmission = true;
        // more than two days before assignment
        } else {
            $modstatus = date("d.m.y", $mincutoffdate);
            $modstyle = 'mod_gray';
            $actionwithtask = true;
        }
    } else {
        $modstatus =  get_string('no_submission_date', 'theme_stardust');
        $modstyle = 'mod_gray';
        $timeline = 0;
        $mincutoffdate = time()+ 2*364*24*60*60;
        $actionwithtask = true;
    }

    $activitystatus['cmid'] = $module->id;
    $activitystatus['duedate'] = $duedate;
    $activitystatus['cutoffdate'] = $cutoffdate;
    $activitystatus['timeline'] = $timeline;
    $activitystatus['modstyle'] = $modstyle;
    $activitystatus['modstatus'] = $modstatus;
    $activitystatus['mincutoffdate'] = $mincutoffdate;
    $activitystatus['openforsubmission'] = $openforsubmission;
    $activitystatus['actionwithtask'] = $actionwithtask;
    $activitystatus['turntotheteacher'] = $turntotheteacher;

    return $activitystatus;

}


/**
 * // add additional content and modify added into activityinfo
 *
 * @param array $course activity.
 * @return array $additional and modify content of activity.
 */
function set_icon_style_for_activity ($module) {
  global $DB, $USER;

  $activitystatus = stardust_activity_status($module);

  // get unit (section) name for activity
  $sectionnum = get_fast_modinfo($module->course)->cms[$module->id]->sectionnum;
  $sectionname = $DB->get_record('course_sections', array('course' => $module->course,'section'=>$sectionnum), 'name')->name;
  $sectionname = (empty($sectionname)) ? get_string('default_unit_name', 'theme_stardust') . $sectionnum : $sectionname;

  // //get module completion state
  // $cmcomplstateraw = $DB->get_record('course_modules_completion', array('coursemoduleid' => $module->id,'userid'=>$USER->id), 'completionstate');
  // $cmcomplstate = $cmcomplstateraw ? true : false; // completed or not activity

  foreach ($module as $modinstance => $modvalue) {
      $activityinfo[$modinstance] = $modvalue;
  }

  $activityinfo['isassign'] = (($activityinfo['modname'] === 'assign') ? true : false);
  $activityinfo['isquiz'] = (($activityinfo['modname'] === 'quiz') ? true : false);

  $activityinfo['cmid'] = $module->id;
  $activityinfo['duedate'] = $activitystatus['duedate'];
  // $activityinfo['duedate'] = date("m.d.y", $duedate);
  $activityinfo['cutoffdate'] = $activitystatus['cutoffdate'];
  // $activityinfo['cutoffdate'] = date("m.d.y", $cutoffdate);
  $activityinfo['timeline'] = $activitystatus['timeline'];
  $activityinfo['iconstyle'] = isset($activitystatus['iconstyle']) ? $activitystatus['iconstyle'] : false;
  $activityinfo['modstatus'] = $activitystatus['modstatus'];
  $activityinfo['mincutoffdate'] = $activitystatus['mincutoffdate'];
  $activityinfo['unitname'] = $sectionname;
  $activityinfo['modstyle'] = $activitystatus['modstyle'];
  $activityinfo['openforsubmission'] = $activitystatus['openforsubmission'];
  $activityinfo['actionwithtask'] = $activitystatus['actionwithtask'];
  $activityinfo['turntotheteacher'] = $activitystatus['turntotheteacher'];


  return $activityinfo;
}

/**
 * Function for getting only specific amount of activities for course cards (filter)
 *
 * @param array $activities An array with all fetched activities
 * @param int $numofactivities The number of needed activities
 * @return Array An array of filtered and sliced activities
 */
function get_relevant_activities($activities, $numofactivities) {
    $sorted = usort($activities, "mincutoffdatesort");
    $relevantslicedactivities = array_slice($activities, 0, $numofactivities);
    return $relevantslicedactivities;
}
/**
 * Help function to sort mincutoffdates of activities
 */
function mincutoffdatesort($a, $b) {
    $currenttime = time();
    $tdiffa = $a['mincutoffdate'] - $currenttime;
    $tdiffb = $b['mincutoffdate'] - $currenttime;
    if ($tdiffa == $tdiffb) {
        return 0;
    }
    return ($tdiffa < $tdiffb) ? -1 : 1;
}

/**
 * Function defines time overrides for activity (module)
 *
 * @param $module - cm details from DB and some extrafields (usually assign and quiz)
 *
 * @return array
 */
function get_module_overrides($module) {
    global $DB, $USER, $CFG;

    // process if assign
    if ($module->modname == 'assign') {
        require_once($CFG->dirroot . '/mod/assign/locallib.php');
        list ($course, $cm) = get_course_and_cm_from_cmid($module->id, 'assign');
        $context = context_module::instance($cm->id);
        $assign = new assign($context, $cm, $course);
        $overrides = $assign->override_exists($USER->id);
        if (isset($overrides->assignid) && $overrides->assignid == $module->instance) {
            $module->duedate    = (isset($overrides->duedate)) ? $overrides->duedate : $module->duedate;
            $module->cutoffdate = (isset($overrides->cutoffdate)) ? $overrides->cutoffdate : $module->cutoffdate;
        }
    }

    //process if quiz
    if ($module->modname == 'quiz') {
        require_once($CFG->dirroot . '/mod/quiz/locallib.php');
        $overrides = quiz_get_user_timeclose($module->course);
        if (isset($overrides[$module->instance])) {
            $module->cutoffdate = $overrides[$module->instance]->usertimeclose;
        }
    }

    return $module;
}

/**
 * Function checks if activity (module) is an assignment and if it is submitted
 *
 * @param $module - cm details from DB and some extrafields (usually assign and quiz)
 *
 * @return bool
 */

function is_assign_submitted($module) {
    global $USER, $DB;
    if ($module->modname == 'assign') {
        $submission = $DB->get_record('assign_submission', array('userid' => $USER->id, 'assignment' => $module->instance));
        if ($submission && $submission->status === 'submitted') {
            return true;
        }
    }
}

/**
 * Function gets the url of the course cover picture
 *
 * @param stdClass $course
 * @return string the url of the course picture
 */

function get_courses_cover_images ($course) {
  global $OUTPUT;
  
  $courseobj = new course_in_list($course);
  $coursecoverimgurl = '';
  foreach ($courseobj->get_course_overviewfiles() as $file) {
      $isimage = $file->is_valid_image();
      $coursecoverimgurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), null, $file->get_filepath(), $file->get_filename());
  }
  if (empty($coursecoverimgurl)) {
      $coursecoverimgurl = $OUTPUT->image_url('banner', 'theme'); // define default course cover image in theme's pix folder
  }

  return $coursecoverimgurl->out();
}
