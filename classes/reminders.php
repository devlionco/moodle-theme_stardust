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

/**
 * Description of hypertext_request
 *
 */
class reminders {

    public static function get_reminders() {
        global $DB, $USER;
        $reminders = $DB->get_records('theme_stardust_reminders', array('userid' => $USER->id), 'timeremind');
        
        $allreminders = array();
        $counthappend = 0;
        if (count($reminders)) {
            foreach($reminders as $rem) {
                $happend = (time() > $rem->timeremind) ? 1 : 0;
                $counthappend += $happend;
                $allreminders[] = array(
                    'id' => $rem->id,
                    'text' => $rem->text,
                    'time' => $rem->timeremind,
                    'happend' => $happend
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
        
        return ($reminder->id) ? json_encode((array)$reminder) : 0;
    }
   
    public static function del_reminder($reminderid) {
        global $DB, $USER;
        $res = $DB->delete_records('theme_stardust_reminders', array('id' => $reminderid, 'userid' => $USER->id));
        return $res ? 1 : 0;
    }
   
}
