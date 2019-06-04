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
 * @package    themestardust_reminders
 * @copyright  devlion.co
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->libdir . '/enrollib.php');

/**
 * Description of hypertext_request
 *
 */
class reminders {

    public static function get_reminders() {
        global $DB, $USER;

        $allreminders = array();
        $counthappend = 0;

        // Check course messages and show it in the reminders list at first.
        $courses = enrol_get_users_courses($USER->id, true);
        if ($courses) {

            $courseids = implode(",", array_keys($courses));

            $sql = "SELECT *
                    FROM {theme_stardust_messages}
                    WHERE status = 1
                        AND courseid IN ($courseids)
            ";
            $coursemessages = $DB->get_records_sql($sql);
            if ($coursemessages) {
                foreach($coursemessages as $rem) {
                    // $counthappend ++;
                    $remindtime = self::get_remind_time($rem->timestatusupdate);
                    $allreminders[] = array(
                        'id' => $rem->id,
                        'text' => $rem->message,
                        'remindtime' => $remindtime,
                        'time' => $rem->timestatusupdate,
                        'happend' => 1,
                        'removable' => 0
                    );
                }
            }
        }

        $reminders = $DB->get_records('theme_stardust_reminders', array('userid' => $USER->id), 'timeremind');
        if (count($reminders)) {
            foreach($reminders as $rem) {
                $happend = (time() > $rem->timeremind) ? 1 : 0;
                $counthappend += $happend;
                $istoday = ( date('Ymd') == date('Ymd', $rem->timeremind) ) ? 1 : 0;
                $remindtime = self::get_remind_time($rem->timeremind, $rem->timecreated);
                $allreminders[] = array(
                    'id' => $rem->id,
                    'text' => $rem->text,
                    'remindtime' => $remindtime,
                    'time' => $rem->timeremind,
                    'happend' => $happend,
                    'istoday' => $istoday,
                    'removable' => 1
                );
            }
        }

        return json_encode(array("count" => count($reminders), "counthappend" => $counthappend, "reminders" => $allreminders));
    }

    public static function add_reminder($params) {
        global $DB, $USER;

        $reminder = new stdClass();
        $reminder->text = $params['text'];
        $reminder->userid = $USER->id;
        $reminder->timeremind = strtotime($params['date']." ".$params['time']);
        $reminder->timecreated = time();

        $reminder->id = $DB->insert_record('theme_stardust_reminders', $reminder);

        $reminder->happend = (time() > $reminder->timeremind) ? 1 : 0;
        $reminder->remindtime = self::get_remind_time($reminder->timeremind, $reminder->timecreated);
        return ($reminder->id) ? json_encode((array)$reminder) : 0;
    }

    public static function del_reminder($reminderid) {
        global $DB, $USER;
        $res = $DB->delete_records('theme_stardust_reminders', array('id' => $reminderid, 'userid' => $USER->id));
        return $res ? 1 : 0;
    }

    protected static function get_remind_time($timeremind, $timecreated=0) {
        $time = '';
        if ($timeremind != $timecreated) {
            $hours = date('H',$timeremind);
            $mins = date('i',$timeremind);
            $time = date('d.m.y',$timeremind) . " ";

            if ((int)$hours != 0 or (int)$mins != 0) {
                $time .= $hours . ":" . $mins . " ";
            }

        }
        return $time;
    }
}
