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
 * Defines the renderer for the quiz module.
 *
 * @package   theme_stardust
 * @copyright Devlion
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/mod/quiz/renderer.php');

/**
 * The renderer for the quiz module.
 *
 * @copyright  Devlion
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class theme_stardust_mod_quiz_renderer extends mod_quiz_renderer {
    const MAIN_CONTENT_TOKEN = '[MAIN CONTENT GOES HERE]';

    /**
     * Attempt Page
     *
     * @param quiz_attempt $attemptobj Instance of quiz_attempt
     * @param int $page Current page number
     * @param quiz_access_manager $accessmanager Instance of quiz_access_manager
     * @param array $messages An array of messages
     * @param array $slots Contains an array of integers that relate to questions
     * @param int $id The ID of an attempt
     * @param int $nextpage The number of the next page
     */
    public function attempt_page($attemptobj, $page, $accessmanager, $messages, $slots, $id,
            $nextpage) {
        global $PAGE;
        // $PAGE->set_pagelayout('quizattempt');
        $navbc = new quiz_attempt_nav_panel($attemptobj, $attemptobj->get_display_options(true), $page, $showall);
        $output = '';
        $output .= $this->header();
        $output .= $this->quiz_notices($messages);
        $output .= $this->navigation_panel($navbc);
        $output .= html_writer::start_tag('div', array('class' => 'quiz_header'));
        $output .= $this->navigation_panel($navbc);
        $output .= html_writer::link(new moodle_url('/mod/quiz/summary.php', array('attempt' => $attemptobj->get_attemptid())), get_string('quizattemptfinishlink', 'theme_stardust'), array('class' => 'finish_quiz'));
        $output .= html_writer::end_tag('div');
        $output .= $this->attempt_form($attemptobj, $page, $slots, $id, $nextpage);
        $output .= $this->navigation_panel($navbc);
        $output .= $this->footer();
        return $output;
    }

}
