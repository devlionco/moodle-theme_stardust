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
 * @package theme_stardust
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class theme_stardust_get_add_form extends moodleform {

    /**
     * Form definition.
     * @return void
     */
    public function definition() {
        global $CFG;

        require_once($CFG->dirroot . '/enrol/locallib.php');

        $mform = $this->_form;
        $mform->addElement('date_time_selector', 'reminderdate', get_string('dateandtime', 'theme_stardust'));
        $mform->addHelpButton('reminderdate', 'reminderdate');
    }
}
