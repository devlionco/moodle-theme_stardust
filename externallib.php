<?php

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
 * External Web Service Template
 *
 * @package    localwstemplate
 * @copyright  2011 Moodle Pty Ltd (http://moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . "/externallib.php");
include_once(__DIR__ . '/classes/reminders.php');

class theme_stardust_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function send_mail_to_teacher_parameters() {
        return new external_function_parameters(
                array(
                    'text' => new external_value(PARAM_TEXT, 'A message to a teacher', VALUE_REQUIRED, ''),
                    'userid' => new external_value(PARAM_INT, 'Teacher ID', VALUE_REQUIRED, ''),
                    'courseid' => new external_value(PARAM_INT, 'Course ID', VALUE_REQUIRED, ''),
                    )
        );
    }

    /**
     * Returns welcome message
     * @return string welcome message
     */
    public static function send_mail_to_teacher($text, $userid, $courseid) {
        global $USER, $DB, $PAGE;

        $context = context_system::instance();
        $PAGE->set_context($context);

        // $context = context_course::instance($courseid);
        // $PAGE->set_context($context);

        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(self::send_mail_to_teacher_parameters(),
                array(
                    'text' => $text,
                    'userid' => (int)$userid,
                    'courseid' => (int)$courseid,
                    )
                );

        $techsupport = new stdClass;
        $techsupport->id = 1;
        $techsupport->email = get_config('theme_stardust', 'technical_support_email');
        
        $teacher = (bool)$params['userid'] ? $DB->get_record('user', array('id' => (int)$params['userid'])) : $techsupport;
        $course = (bool)$params['courseid'] ? $DB->get_record('course', array('id' => $courseid)) : (bool)$params['courseid'];

        return self::send_mail_to_teacher_sender($USER, $teacher, $params['text'], $course);
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function send_mail_to_teacher_returns() {
        return new external_value(PARAM_TEXT, 'The result of email sending');
    }

    /**
     * Send message to a teacher
     * @return bool
     */
    protected static function send_mail_to_teacher_sender($userfrom, $userto, $text, $course) {
        global $SITE;

        $subject = (bool)$course ? $SITE->fullname.' - '.get_string('message_from_a_course_page', 'theme_stardust').' '.$course->fullname :  $SITE->fullname;
        $messagetext = trim(nl2br((string)$text));

        return email_to_user($userto, $userfrom, $subject, $messagetext, '', '', '', true, $userfrom->email);

    }
    
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_reminders_parameters() {
        return new external_function_parameters(
            array(
            )
        );
    }
    /**
     * @return string all user reminders
     */
    public static function get_reminders() {
        return reminders::get_reminders();
    }
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_reminders_returns() {
        return new external_value(PARAM_RAW, 'All user reminders');
    }
    
    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */

    public static function add_reminder_parameters() {
        return new external_function_parameters(
            array(
                'text' => new external_value(PARAM_TEXT, 'Reminder text'),
                'date' => new external_value(PARAM_TEXT, 'Reminder date'),
                'time' => new external_value(PARAM_TEXT, 'Reminder time'),
            )
        );
    }
    /**
     * @return string add reminder
     */
    public static function add_reminder($text, $date, $time) {

        $params = self::validate_parameters(self::add_reminder_parameters(),
            array(
                'text' => $text,
                'date' => $date,
                'time' => $time,
            )
        );
        return reminders::add_reminder($params);
    }
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function add_reminder_returns() {
        return new external_value(PARAM_TEXT, 'The result of add reminder');
    }

    public static function del_reminder_parameters() {
        return new external_function_parameters(
            array(
                'reminderid' => new external_value(PARAM_INT, 'Reminder ID')
            )
        );
    }
    /**
     * @return string delete reminder
     */
    public static function del_reminder($reminderid) {

        $params = self::validate_parameters(self::del_reminder_parameters(),
            array(
                'reminderid' => $reminderid,
            )
        );
        return reminders::del_reminder($params['reminderid']);
    }
    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function del_reminder_returns() {
        return new external_value(PARAM_TEXT, 'The result of delete reminder');
    }

}
