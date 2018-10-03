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
 * Form for editing a users background at mypublic page
 *
 * @copyright 1999 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package core_user
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    //  It must be included from a Moodle page.
}

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * Class user_editadvanced_form.
 *
 * @copyright 1999 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mydashboard_background_form extends moodleform {

    /**
     * Define the form.
     */
    public function definition() {
        global $USER, $CFG;

        $mform = $this->_form;
        if (!is_array($this->_customdata)) {
            throw new coding_exception('invalid custom data for user_edit_form');
        }
        $user = $this->_customdata['user'];
        $userid = $user->id;

        $filemanageroptions = array('subdirs' => false, 'maxfiles' => 1, 'accepted_types' => 'web_image');
        $context = context_user::instance($userid);
        $backgroundimgdraftid = file_get_submitted_draft_itemid('dashbackgroundimg');
        file_prepare_draft_area($backgroundimgdraftid, $context->id, 'theme_stardust', 'dashbackgroundimg', $userid,
                        array('subdirs' => false));


        $mform->addElement('header', 'background_img', get_string('mypublic_background_img_hdr', 'theme_stardust'));
        $mform->setExpanded('background_img', true);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', core_user::get_property_type('id'));

        $mform->addElement('filemanager', 'dashbackgroundimg', get_string('mypublic_new_background_img', 'theme_stardust'), '', $filemanageroptions);
        $mform->setDefault('dashbackgroundimg', $backgroundimgdraftid); 

        $mform->addElement('checkbox', 'deletebackgroundimg', get_string('mypublic_deletebackgroundimg', 'theme_stardust'));
        $mform->setDefault('deletebackgroundimg', 0);

        $mform->addElement('submit', 'submitavatar', get_string('savechanges'));
        $mform->closeHeaderBefore('submitavatar');

        $this->set_data($user);
    }

    /**
     * Extend the form definition after data has been parsed.
     */
    public function definition_after_data() {
        global $USER, $CFG, $DB, $OUTPUT;

        $mform = $this->_form;


        // Print picture.
        // if (empty($USER->newadminuser)) {
        //     if ($user) {
        //         $context = context_user::instance($user->id, MUST_EXIST);
        //         $fs = get_file_storage();
        //         $hasuploadedpicture = ($fs->file_exists($context->id, 'user', 'icon', 0, '/', 'f2.png') || $fs->file_exists($context->id, 'user', 'icon', 0, '/', 'f2.jpg'));
        //         if (!empty($user->picture) && $hasuploadedpicture) {
        //             $imagevalue = $OUTPUT->user_picture($user, array('courseid' => SITEID, 'size' => 64));
        //         } else {
        //             $imagevalue = get_string('none');
        //         }
        //     } else {
        //         $imagevalue = get_string('none');
        //     }
        //     $imageelement = $mform->getElement('currentpicture');
        //     $imageelement->setValue($imagevalue);

        //     if ($user && $mform->elementExists('deletepicture') && !$hasuploadedpicture) {
        //         $mform->removeElement('deletepicture');
        //     }
        // }
    }

    /**
     * Validate the form data.
     * @param array $usernew
     * @param array $files
     * @return array|bool
     */
    public function validation($usernew, $files) {
        global $CFG, $DB;
            return true;
    }
    
}


