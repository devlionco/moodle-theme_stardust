<?php
//require_once('../../../config.php');
require_once($CFG->libdir . '/datalib.php');
include_once($CFG->dirroot . '/theme/stardust/lib.php');


class classAjax
{

    private $method;

    public function __construct()
    {
        $this->method = required_param('method', PARAM_TEXT);
    }

    public function run()
    {
        //call ajax method
        if (method_exists($this, $this->method)) {
            $method = $this->method;
            return $this->$method();
        } else {
            return 'wrong method';
        }
    }

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

    private $activitiesconf = array (
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

    private function get_activities($activitiesconf = array(), $courseids = array(), $capabilities = array(), $includenotenrolledcourses = false) {
        global $USER, $DB, $CFG, $OUTPUT;

        // default params for getting courses
        $params = array(
                'courseids' => $courseids,
                'capabilities' => $capabilities,
                'includenotenrolledcourses' => $includenotenrolledcourses
        );

        $warnings = array();
        $courses = array();
        $fields = 'sortorder,shortname,fullnam,timemodified,enddate';

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


        // build activities array
        $activities = array();
        // begin courses iteration
        foreach ($courses as $id => $course) {

            //iterate through all configured activities inside particular course
            foreach ($activitiesconf as $activityname => $extrafields) {

                // Get activities in course one by one
                if ($modules = get_coursemodules_in_course($activityname, $courses[$id]->id, $extrafields)) {

                    foreach ($modules as $module) {
                        $context = context_module::instance($module->id);

                        // add additional content and modify added into activityinfo
                        $activityinfo = set_icon_style_for_activity ($module);
                        $activityinfo['coursename'] = format_string($courses[$id]->shortname, $course->contextid);

                        // add all activityinfo to general array
                        // $activities[$activityname][] = $activityinfo; // with activityname sorting
                        $activities[] = $activityinfo; // without activityname sorting
                    } // end foreach module
                } // end if module exists
            } // end foreach activity

        } // end courses iteration

        $result = $activities;
        return $result;
    }

    //Get timetable of the week
    public function get_timetable_week()  {
        global $CFG, $USER, $DB, $OUTPUT;

        $data = array();

        $activities = $this->get_activities($this->activitiesconf); // get all activities for user
        if (empty($activities)) {
            $data['noanyactivities'] = true;
        }

        $firstdayunixtime = optional_param('firstday', '', PARAM_INT); //get first week day timestamp from ajax
        $direction= optional_param('direction', 'current', PARAM_RAW);

        $firstweekdayunixtime = strtotime("last Sunday", $firstdayunixtime); //define real first day timestamp for current week
        if ((date("l", $firstdayunixtime)) === "Sunday"){
            // $firstweekdayunixtime = $firstdayunixtime;
            $firstweekdayunixtime = strtotime("midnight", $firstdayunixtime);
        }

        if ($direction == "prevweek") {
            $firstweekdayunixtime = strtotime("previous Sunday", $firstweekdayunixtime); // calculate firstday timestamp for previous Sunday
        }elseif ($direction == "nextweek") {
            $firstweekdayunixtime = strtotime("next Sunday", $firstweekdayunixtime); // calculate firstday timestamp for next Sunday
        }

        // foreach (range(0,6) as $daynum) {
        foreach (range(0,4) as $daynum) {
            $data['week'][$daynum]['dayname'] = get_string('dayname_'.$daynum, 'theme_stardust');
            $daydate = strtotime('+'.$daynum.' day', $firstweekdayunixtime); //current day into loop
            $data['week'][$daynum]['daydate'] = date("j.n", $daydate); // j - day without zeros, n - mont without zeros; d.m - with zeros
            // $data['week'][$daynum]['fulldate'] = date("d.m.Y H:i:s", $daydate);
            // $data['week'][$daynum]['timestampe'] = $daydate;
            // $prevdaydate = strtotime('-1 day', $daydate);
            $nextdaydate = strtotime('+1 day', $daydate);

            foreach ($activities as $activity => $activityinfo) {
                if ($activityinfo['mincutoffdate'] >= $daydate && $activityinfo['mincutoffdate'] < $nextdaydate) {
                    $data['week'][$daynum]['activities'][] = $activityinfo;
                }
            }
        }

        // general context for week
        $data['time'] = $firstweekdayunixtime;
        // print_object($data);
        // echo '<pre style = "direction: ltr;">'.print_r($data,1).'</pre>'; exit();

        $html = $OUTPUT->render_from_template('theme_stardust/tabweek', $data);

        return $html;
    }

    /**
     * Funtion saves teachers course message to theme_stardust_messages table
     *
     */
    private function send_course_message() {
        global $CFG, $DB;
        $userid  = required_param('user', PARAM_INT);
        $courseid = required_param('courseid', PARAM_INT);
        $message = required_param('message', PARAM_RAW);
        $status = 1;                         // when teacher creates the message - we enable it (show it) by default
        $status = (empty($message) ? 0 : 1); // if teacher erases the message - we disable (hied) message block by default

        // prepare new DB record
        $record = new stdClass();
        $record->courseid         = $courseid;
        $record->message          = $message;
        $record->userfrom         = $userid;
        $record->timecreated      = time();
        $record->status           = $status;
        $record->timestatusupdate = time();

        // insert or update message
        if ($messid = $DB->get_record('theme_stardust_messages', array ('courseid' => $courseid))) {
            $record->id = $messid->id;
            $DB->update_record('theme_stardust_messages', $record);
        } else {
            $DB->insert_record('theme_stardust_messages', $record);
        }

    }
    /**
     * Funtion saves teachers course message show/hide status to theme_stardust_messages table
     *
     */
    private function toggle_course_message_status() {
        global $CFG, $DB;
        //$userid  = required_param('user', PARAM_INT); // SG -- we will not change userfrom field, as it's just status update
        $courseid = required_param('courseid', PARAM_INT);
        $status = required_param('status', PARAM_INT);

        // prepare new DB record
        $record = new stdClass();
        $record->courseid         = $courseid;
        $record->status           = $status;
        //$record->userfrom         = $userid; // SG -- we will not change userfrom field, as it's just status update
        $record->timestatusupdate = time();

        // insert or update message
        if ($messid = $DB->get_record('theme_stardust_messages', array ('courseid' => $courseid))) {
            $record->id = $messid->id;
            $DB->update_record('theme_stardust_messages', $record);
        } else {
            echo "There is no message for this course, so we cannot change message status.";
        }

    }

    private function save_course_filter() {
        global $DB;

        $contextid = required_param('contextid', PARAM_INT);
        $filterstate = required_param('filterstate', PARAM_RAW);

        $userid = context::instance_by_id($contextid)->instanceid;

        $response = set_user_preference('pagemy_filterstate', $filterstate, $userid);

        return $response;
    }

}
