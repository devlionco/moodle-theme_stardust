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
    public function attempt_page($attemptobj, $page, $accessmanager, $messages, $slots, $id, $nextpage) {
        global $PAGE;
        // $PAGE->set_pagelayout('quizattempt');
        $navbc = new quiz_attempt_nav_panel($attemptobj, $attemptobj->get_display_options(true), $page, $showall);
        // $navbc = new quiz_attempt_nav_panel($attemptobj, $attemptobj->get_display_options(t), $page, false);
        $output = '';

        $output .= $this->header();
        $output .= $this->quiz_notices($messages);
        // $output .= $this->navigation_panel($navbc);

        $output .= html_writer::start_tag('div', array('class' => 'quiz_header'));
        $output .= html_writer::tag('p', $attemptobj->get_quiz_name(), array('class' => 'quiz_name'));
        $output .= '<div class = "filter"><span>answered</span>
          <span>not answerd</span>
          <span>flaged</span>
          </div>';
        // $output .= $this->navigation_panel($navbc);
        // $output .= html_writer::link(new moodle_url('/mod/quiz/summary.php', array('attempt' => $attemptobj->get_attemptid())), get_string('quizattemptfinishlink', 'theme_stardust'), array('class' => 'finish_quiz'));
        $output .= html_writer::end_tag('div');
        $output .= $this->attempt_form($attemptobj, $page, $slots, $id, $nextpage);
        $output .= $this->navigation_panel($navbc);
        $output .= $this->footer();
        return $output;
    }

    /**
     * Outputs the navigation block panel
     *
     * @param quiz_nav_panel_base $panel instance of quiz_nav_panel_base
     */
    public function navigation_panel(quiz_nav_panel_base $panel) {

        $output = '';
        $userpicture = $panel->user_picture();
        if ($userpicture) {
            $fullname = fullname($userpicture->user);
            if ($userpicture->size === true) {
                $fullname = html_writer::div($fullname);
            }
            $output .= html_writer::tag('div', $this->render($userpicture) . $fullname,
                    array('id' => 'user-picture', 'class' => 'clearfix'));
        }
        $output .= $panel->render_before_button_bits($this);

        $bcc = $panel->get_button_container_class();
        $output .= html_writer::start_tag('div', array('class' => "qn_buttons clearfix $bcc"));
        foreach ($panel->get_question_buttons() as $button) {
            $button->navmethod = $panel->get_attemptobj()->get_navigation_method(); // nadavkav 15/7/2014
            // if ($button->stateclass != ' qpage') {
            if (!strpos($button->stateclass, 'qpage')) {
              $output .= $this->render($button);
            }
        }
        $output .= html_writer::end_tag('div');

        $output .= html_writer::tag('div', $panel->render_end_bits($this),
                array('class' => 'othernav'));

        if (empty($_GET['page'])) {
            $quiznavpage = 1;
        } else {
            $quiznavpage = $_GET['page'];
        }
        $this->page->requires->js_init_call('M.mod_quiz.nav.init', array($quiznavpage), false,
                quiz_get_js_module());

        return $output;
    }

}
