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
require_once($CFG->dirroot . '/mod/quiz/attemptlib.php');

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

        $showallquestions = optional_param('showallquestions', 0, PARAM_INT);

        $filter = optional_param('filter', '',  'alphaext');

        if($showallquestions) {
            $nextpage = -1;
            $page = 0;
            $slots = $attemptobj->get_slots('all');
        }

        $PAGE->requires->js_call_amd('theme_stardust/quizfilter', 'init'); // filter
        $PAGE->requires->js_call_amd('theme_stardust/questionsnav', 'init'); // slider for paging
        // $PAGE->set_pagelayout('quizattempt');
        $navbc = new quiz_attempt_nav_panel($attemptobj, $attemptobj->get_display_options(true), $page, $showallquestions);
        $output = '';

        $output .= $this->header();
        $output .= $this->quiz_notices($messages);

        $output .= html_writer::start_tag('div', array('class' => 'quiz_header'));
        $output .= html_writer::start_tag('div', array('class' => 'quiz_header_wrap'));
        $output .= html_writer::tag('p', $attemptobj->get_quiz_name(), array('class' => 'quiz_name'));
        $output .= $this->quiestion_filter($showallquestions, $filter);
        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');
        //$output .= html_writer::tag('div', $PAGE->activityrecord->intro, array('class' => 'quiz_description')); // SG -- show quiz description as sub geader
        $output .= html_writer::tag('div', $attemptobj->get_heading_before_slot(1), array('class' => 'quiz_description')); // SG -- show slot section heading as sub header
        $output .= $this->no_questions_matched_criteria();

        $output .= $this->attempt_form($attemptobj, $page, $slots, $id, $nextpage, $showallquestions);
        $output .= $this->navigation_panel($navbc);
        $output .= $this->footer();

        return $output;
    }

    /**
     * Outputs the navigation block panel
     *
     * @param quiz_nav_panel_base $panel instance of quiz_nav_panel_base
     */
    public function no_questions_matched_criteria() {

        $output = '';
        $output .= html_writer::start_tag('div', array('class' => "clearfix"));
        $output .= html_writer::tag('h3', get_string('no_questions_matched_criteria', 'theme_stardust'), array(
            'style' => 'display:none',
            'class' => 'no_questions_matched_criteria'
        ));
        $output .= html_writer::end_tag('div');
        return $output;
    }

    /**
     * Outputs the navigation block panel
     *
     * @param quiz_nav_panel_base $panel instance of quiz_nav_panel_base
     */
    public function navigation_panel(quiz_nav_panel_base $panel) {

        $output = '';
        // $userpicture = $panel->user_picture();
        // if ($userpicture) {
        //     $fullname = fullname($userpicture->user);
        //     if ($userpicture->size === true) {
        //         $fullname = html_writer::div($fullname);
        //     }
        //     $output .= html_writer::tag('div', $this->render($userpicture) . $fullname,
        //             array('id' => 'user-picture', 'class' => 'clearfix'));
        // }
        $output .= $panel->render_before_button_bits($this);

        $bcc = $panel->get_button_container_class();
        $output .= html_writer::start_tag('div', array('class' => "qn_buttons clearfix $bcc"));
        $output .= html_writer::start_tag('div', array('class' => "qn_buttons-inner"));
          foreach ($panel->get_question_buttons() as $button) {
              $button->navmethod = $panel->get_attemptobj()->get_navigation_method(); // nadavkav 15/7/2014
              // if ($button->stateclass != ' qpage') {
              if (!strpos($button->stateclass, 'qpage')) {
                $output .= $this->render($button);
              }
          }
        $output .= html_writer::end_tag('div');
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

    /**
     * Outputs the navigation block panel
     *
     * @param quiz_nav_panel_base $panel instance of quiz_nav_panel_base
     */
    public function quiestion_filter($showallquestions, $filter) {
      global $PAGE;

      $params = $PAGE->url->params();
      $paginatedurl = new moodle_url($PAGE->url, $params);
      $params['showallquestions'] = 1;
      $allquestionsurl = new moodle_url($PAGE->url, $params);

      $output = '';
      $output .= html_writer::start_tag('div', array(
          'class' => $showallquestions ? 'filter_wrap quiz_all_questions' : 'filter_wrap',
          'data-allquestionspage' => $allquestionsurl->out(false),
          'data-originalquizpage' => $paginatedurl->out(false),
          ));

      $output .= html_writer::tag('span', get_string('quiz_filter', 'theme_stardust') , array('class' => 'filter_legend'));
      $output .= html_writer::tag('button', '<span class = "filter_pin"></span>', array(
        'data-placement' =>"bottom",
        'data-tooltip' =>"tooltip",
        'title' =>get_string('show_flagged', 'theme_stardust'),
        'data-handler' => 'filter_flag',
        'class' => 'filter_toggle filter_flag' . ($filter == 'filter_flag' ? ' filter_preset' : '')
      ));
      $output .= html_writer::tag('button', '<span class = "filter_pin"></span>', array(
        'data-placement' =>"bottom",
        'data-tooltip' =>"tooltip",
        'title' =>get_string('show_answered', 'theme_stardust'),
        'data-handler' => 'filter_answered',
        'class' => 'filter_toggle filter_answered' . ($filter == 'filter_answered' ? ' filter_preset' : '')
      ));
      $output .= html_writer::tag('button', '<span class = "filter_pin"></span>', array(
        'data-placement' =>"bottom",
        'data-tooltip' =>"tooltip",
        'title' =>get_string('show_notanswered', 'theme_stardust'),
        'data-handler' => 'filter_notanswered',
        'class' => 'filter_toggle filter_notanswered' . ($filter == 'filter_notanswered' ? ' filter_preset' : '')
      ));
      $output .= html_writer::end_tag('div');

      return $output;
    }

    /**
     * Display the prev/next buttons that go at the bottom of each page of the attempt.
     *
     * @param int $page the page number. Starts at 0 for the first page.
     * @param bool $lastpage is this the last page in the quiz?
     * @param string $navmethod Optional quiz attribute, 'free' (default) or 'sequential'
     * @return string HTML fragment.
     */
    public function attempt_navigation_buttons($page, $lastpage, $navmethod = 'free') {
        $output = '';

        $output .= html_writer::start_tag('div', array('class' => 'submitbtns'));
        if ($page > 0 && $navmethod == 'free') {
            $output .= html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'previous',
                    'value' => get_string('navigateprevious', 'quiz'), 'class' => 'mod_quiz-prev-nav btn btn-secondary'));
        }
        $endteststyle = '';
        if ($lastpage) {
            $nextlabel = get_string('endtest', 'quiz');
            $endteststyle = ' quiz_endtest';
        } else {
            $nextlabel = get_string('navigatenext', 'quiz');
        }
        $output .= html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'next',
                'value' => $nextlabel, 'class' => 'mod_quiz-next-nav btn btn-primary'.$endteststyle));
        $output .= html_writer::end_tag('div');

        return $output;
    }


    /**
     * Ouputs the form for making an attempt
     *
     * @param quiz_attempt $attemptobj
     * @param int $page Current page number
     * @param array $slots Array of integers relating to questions
     * @param int $id ID of the attempt
     * @param int $nextpage Next page number
     */
    public function attempt_form($attemptobj, $page, $slots, $id, $nextpage, $showallquestions = null) {
        global $PAGE;

        $output = '';

        // Start the form.
        $output .= html_writer::start_tag('form',
                array('action' => new moodle_url($attemptobj->processattempt_url(),
                array('cmid' => $attemptobj->get_cmid())), 'method' => 'post',
                'enctype' => 'multipart/form-data', 'accept-charset' => 'utf-8',
                'id' => 'responseform'));
        $output .= html_writer::start_tag('div');

        // special quiz page layout settings (show/hide html elements). // nadavkav 26/8/2015
          // undelete this after copying the DB from moodle31
          /*
        $davidsonlayoutsettings = explode(',', $attemptobj->get_quiz()->davidson);
        foreach ($davidsonlayoutsettings as $setting) {
            list($key, $value) = explode('=', $setting);
            $layoutsetting[$key] = $value;
        }

        if ($layoutsetting['info'] == '0')
            $output .= html_writer::tag('style', '.info {display:none;}.dir-rtl .que .content, .que .content{margin:0;}');
        if ($layoutsetting['info_flag'] == '0')
            $output .= html_writer::tag('style', '.questionflag {display:none;}');
        if ($layoutsetting['info_state'] == '0')
            $output .= html_writer::tag('style', '.state {display:none;}'); // hanna 5/5/19
        if ($layoutsetting['info_question_number'] == '0')
            $output .= html_writer::tag('style', '.info .no {display:none;}');
        if ($layoutsetting['info_grade'] == '0')
            $output .= html_writer::tag('style', '.grade {display:none;}');
        if ($layoutsetting['quizsummary'] == '0')
            $output .= html_writer::tag('style', '.quizreviewsummary {display:none;}');
*/
        // Davidson - end

        // Print all the questions.
        foreach ($slots as $slot) {
            $output .= $attemptobj->render_question($slot, false, $this,
                    $attemptobj->attempt_url($slot, $page), $this);
        }

        $navmethod = $attemptobj->get_quiz()->navmethod;
        $islastpage = $showallquestions ? true : $attemptobj->is_last_page($page);
        $output .= $this->attempt_navigation_buttons($page, $islastpage, $navmethod);

        // Some hidden fields to trach what is going on.
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'attempt',
                'value' => $attemptobj->get_attemptid()));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'thispage',
                'value' => $page, 'id' => 'followingpage'));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'nextpage',
                'value' => $nextpage));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'timeup',
                'value' => '0', 'id' => 'timeup'));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey',
                'value' => sesskey()));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'scrollpos',
                'value' => '', 'id' => 'scrollpos'));

        // Add a hidden field with questionids. Do this at the end of the form, so
        // if you navigate before the form has finished loading, it does not wipe all
        // the student's answers.
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'slots',
                'value' => implode(',', $attemptobj->get_active_slots($page))));

        // Finish the form.
        $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('form');

        $output .= $this->connection_warning();

        return $output;
    }

}
