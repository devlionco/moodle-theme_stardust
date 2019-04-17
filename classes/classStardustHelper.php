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

defined('MOODLE_INTERNAL') || die();

class StardustHelper {

    public static function getHelpContacts($courseid) {
        global $USER, $DB;
        
        $coursecontext = context_course::instance($courseid);
        $courseformatname = course_get_format($courseid)->get_format();
        
        //error_log(" \r\n HELP FOR COURSE $courseid =============================================== \r\n", 3, '/home/ice/proj/devlion/davidson/logs/teacherrole.log');
        $coursehelpcontactroles = $DB->get_record('course_format_options', array('courseid' => $courseid, 'format' => $courseformatname, 'name' => 'helpcontactroles')); //Get help contact roles setting for the course.

        if (!$coursehelpcontactroles) {
            $sql = 'SELECT * FROM {course_format_options} WHERE courseid = :courseid AND format = :format AND name LIKE "helpcontactroles_%"';
            $coursehelpcontactroleslist = $DB->get_records_sql($sql, ['courseid' => $courseid, 'format' => $courseformatname]);
            $coursehelpcontactrolesupd = self::update_helpcontactroles($coursehelpcontactroleslist);
        }

        if ($coursehelpcontactroles and $coursehelpcontactroles->value != '') {
            $helpcontactroles = $coursehelpcontactroles->value;
        } elseif ($coursehelpcontactrolesupd and $coursehelpcontactrolesupd != '') {
            $helpcontactroles = $coursehelpcontactrolesupd;
        } else {
            $helpcontactroles = get_config('theme_stardust', 'help_contact_roles'); // Use default contact roles from the theme settings.
        }

        $helpcontactrolesarray = explode(',', $helpcontactroles);
        $helpcontactsunchecked = array_values(get_role_users($helpcontactrolesarray, $coursecontext, false, 'ra.id, u.id, u.firstname, u.lastname, u.email'));

        $usergroupsall = groups_get_user_groups($courseid, $USER->id);
        $usergroups = $usergroupsall[0];

        foreach ($helpcontactsunchecked as $key => $contact) { 
            $userroles = self::get_user_roles_array($coursecontext, $contact->id);
            if (!in_array(16, $userroles)) { // Remove if user doesn't have support role - 16.
                unset($helpcontactsunchecked[$key]);
            }
            if (in_array(10, $userroles)) { // If user does have group teacher role (10) check both teacher and student in the same group.
                $contactgroupsall = groups_get_user_groups($courseid, $contact->id);
                $contactgroups = $contactgroupsall[0];
                if (!count(array_intersect($usergroups, $contactgroups))) { 
                    unset($helpcontactsunchecked[$key]);
                }
            }
        }
                
        return array_values($helpcontactsunchecked);
    }
    
    
    protected static function get_user_roles_array($context, $userid) {
        $userroles = get_user_roles($context, $userid);
        $rolesarray = array();
        foreach ($userroles as $role) {
            $rolesarray[] = $role->roleid;
        }
        return $rolesarray;
    }
    
    
    protected static function update_helpcontactroles($coursehelpcontactroleslist) {
        $roles = array();
        foreach ($coursehelpcontactroleslist as $key => $val) {
            if ($val->value == '1') {
                if (substr($val->name, 0, 17) === 'helpcontactroles_') {
                    $num = substr($val->name, strpos($val->name, "_") + 1);
                    $roles[] = $num;
                }
            }
        }
        return implode(',', $roles);
    }
}
