<?php
/**
 * Override course page output
 */
namespace theme_stardust\output\core;

use core_text;
use html_writer;
use stdClass;
use completion_info;

use cm_info;
use Locker\XApi\Object;
use moodle_url;
use context_course;
use pix_icon;
use coursecat_helper;
use lang_string;
use coursecat;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/renderer.php');

class course_renderer extends \core_course_renderer {

    /**
     * Renders html to display a name with the link to the course module on a course page
     *
     * If module is unavailable for user but still needs to be displayed
     * in the list, just the name is returned without a link
     *
     * Note, that for course modules that never have separate pages (i.e. labels)
     * this function return an empty string
     *
     * @param cm_info $mod
     * @param array $displayoptions
     * @return string
     */
    public function course_section_cm_name_title(\cm_info $mod, $displayoptions = array()) {
        global $DB, $CFG, $PAGE;

        $output = '';
        $url = $mod->url;
        if (!$mod->is_visible_on_course_page() || !$url) {
            // Nothing to be displayed to the user.
            return $output;
        }

        //Accessibility: for files get description via icon, this is very ugly hack!
        $instancename = $mod->get_formatted_name();
        $altname = $mod->modfullname;
        // Avoid unnecessary duplication: if e.g. a forum name already
        // includes the word forum (or Forum, etc) then it is unhelpful
        // to include that in the accessible description that is added.
        if (false !== strpos(core_text::strtolower($instancename),
                core_text::strtolower($altname))) {
            $altname = '';
        }
        // File type after name, for alphabetic lists (screen reader).
        if ($altname) {
            $altname = get_accesshide(' '.$altname);
        }

        list($linkclasses, $textclasses) = $this->course_section_cm_classes($mod);

        // Get on-click attribute value if specified and decode the onclick - it
        // has already been encoded for display (puke).
        $onclick = htmlspecialchars_decode($mod->onclick, ENT_QUOTES);

        // define activity status and icon
        $mextra = $DB->get_record_sql("
            SELECT
                m.*
            FROM
                {".$mod->modname."} m
            WHERE
                m.id = ?", array($mod->instance));

        // create temp $mod object with few fields to get current activity status correctly (for compat with func stardust_activity_status in lib.php)
        $tmod = new stdClass();
        $tmod->id = $mod->id;
        $tmod->modname = $mod->modname;
        $tmod->instance = $mod->instance;
        $tmod->added = $mod->added;
        $tmod->course = $mextra->course;

        if ($mod->modname == 'assign' || $mod->modname == 'quiz') {
            if ($mod->modname == 'assign') {
                $tmod->duedate = isset($mextra->duedate) ? $mextra->duedate : 0;
                $tmod->cutoffdate= isset($mextra->cutoffdate) ? $mextra->cutoffdate : 0;
            }
          $activitystatus = stardust_activity_status($tmod);

         /*  $micon = $this->output->image_url('/'.$mod->modname.'/'.$activitystatus['modstyle'], 'theme'); */
          $micon = $mod->get_icon_url();
          $mstatus = $activitystatus['modstatus'];
        } else {
          $micon = $mod->get_icon_url();
          $mstatus = '';
        }

        // Display link itself.
        $modstyle = (!empty($activitystatus['modstyle']))? $activitystatus['modstyle'] : '';

        $activitylink = html_writer::tag('div',
        html_writer::empty_tag('img', array('src' => $micon,
                'class' => 'iconlarge activityicon '.$modstyle,
                'alt' => ' ',
                'role' => 'presentation')),
                array('class' => 'activityicon_wrapper ' .$modstyle)
                ) .

                html_writer::tag('span', $instancename . $altname, array('class' => 'instancename'));
                // html_writer::tag('span', $mstatus, array('class' => 'mstatus ' .$activitystatus['modstyle']));
        if ($mod->uservisible) {
            $output .= html_writer::link($url, $activitylink, array('class' => $linkclasses, 'onclick' => $onclick));
            if(!$PAGE->user_is_editing() && !empty($activitystatus['modstyle'])) {
              $output .= html_writer::tag('span', $mstatus, array('class' => 'mstatus ' .$activitystatus['modstyle']));
            }
        } else {
            // We may be displaying this just in order to show information
            // about visibility, without the actual link ($mod->is_visible_on_course_page()).
            $output .= html_writer::tag('div', $activitylink, array('class' => $textclasses));
        }
        return $output;
    }


    /**
     * Renders HTML to display a list of course modules in a course section
     * Also displays "move here" controls in Javascript-disabled mode
     *
     * This function calls {@link core_course_renderer::course_section_cm()}
     *
     * @param stdClass $course course object
     * @param int|stdClass|section_info $section relative section number or section object
     * @param int $sectionreturn section number to return to
     * @param int $displayoptions
     * @return void
     */
    public function course_section_cm_list($course, $section, $sectionreturn = null, $displayoptions = array()) {
        global $USER, $PAGE;

        $output = '';
        $modinfo = get_fast_modinfo($course);
        if (is_object($section)) {
            $section = $modinfo->get_section_info($section->section);
        } else {
            $section = $modinfo->get_section_info($section);
        }
        $completioninfo = new completion_info($course);

        // check if we are currently in the process of moving a module with JavaScript disabled
        $ismoving = $this->page->user_is_editing() && ismoving($course->id);
        if ($ismoving) {
            $movingpix = new pix_icon('movehere', get_string('movehere'), 'moodle', array('class' => 'movetarget'));
            $strmovefull = strip_tags(get_string("movefull", "", "'$USER->activitycopyname'"));
        }

        // Get the list of modules visible to user (excluding the module being moved if there is one)
        $moduleshtml = array();
        if (!empty($modinfo->sections[$section->section])) {
            foreach ($modinfo->sections[$section->section] as $modnumber) {
                $mod = $modinfo->cms[$modnumber];

                if ($ismoving and $mod->id == $USER->activitycopy) {
                    // do not display moving mod
                    continue;
                }

                if ($modulehtml = $this->course_section_cm_list_item($course, $completioninfo, $mod, $sectionreturn, $displayoptions)) {
                    $moduleshtml[$modnumber] = $modulehtml;
                }
            }
        }

        $sectionoutput = '';
        if (!empty($moduleshtml) || $ismoving) {
            foreach ($moduleshtml as $modnumber => $modulehtml) {
                if ($ismoving) {
                    $movingurl = new moodle_url('/course/mod.php', array('moveto' => $modnumber, 'sesskey' => sesskey()));
                    $sectionoutput .= html_writer::tag('li',
                            html_writer::link($movingurl, $this->output->render($movingpix), array('title' => $strmovefull)),
                            array('class' => 'movehere'));
                }

                $sectionoutput .= $modulehtml;
            }

            if ($ismoving) {
                $movingurl = new moodle_url('/course/mod.php', array('movetosection' => $section->id, 'sesskey' => sesskey()));
                $sectionoutput .= html_writer::tag('li',
                        html_writer::link($movingurl, $this->output->render($movingpix), array('title' => $strmovefull)),
                        array('class' => 'movehere'));
            }
        }
        // Always output the section module list.
        $sectionstyle = '';
        if ($course->format == "stardust") {
            $sectionstyle = '';
            $sectionstyle = ($section->collapsed == FORMAT_STARDUST_COLLAPSED && $section->section != 0 || $section->visible == 3) ? 'display:none;' : 'display:block;';  // 0 is for FORMAT_STARDUST_COLLAPSED; SG - $section->visible = 3 - is hack for hiding section 0 content with css
        }
        $activityheader = '';

        if ($course->format == 'cards') {
          $addactivity = '';

          $addactivity = $this->course_section_add_cm_control($course, $section->section);

          // $activitititle = $section->name;
          $activitititle = get_section_name($course, $section);
          $activityheader = '<div class="activity-header">
                              <p class="header-title">'.$activitititle.'</p>
                              <span class = "add_activity">'.$addactivity.'</span>
                              <button class="header-btn-close"></button>
                            </div>';

          $activitcontent = $activityheader;
          $activitcontent .= $sectionoutput ? $sectionoutput : html_writer::tag('span', get_string('noactivities', 'format_cards'), array('class' => 'activities-empty'));

          $output .= html_writer::start_tag('div', array('class' => 'wrapper'));
          $output .= html_writer::tag('ul', $activitcontent, array('class' => 'activities img-text'));
          $output .= html_writer::end_tag('div');
        } elseif ($course->format == 'stardust') {
          $output .= html_writer::tag('ul', $sectionoutput, array('class' => 'section img-text', 'style' => $sectionstyle));
        } else {
            $output .= html_writer::tag('ul', $sectionoutput, array('class' => 'section img-text'));
        }

        return $output;
    }


    //Get student users of course
    public function get_students_course($courseid){
        global $DB;

        $sql = "
        SELECT u.id as userid, CONCAT(u.firstname,' ',u.lastname) as name
        FROM {user} u
        INNER JOIN {role_assignments} ra ON ra.userid = u.id
        INNER JOIN {context} ct ON ct.id = ra.contextid
        INNER JOIN {course} c ON c.id = ct.instanceid
        INNER JOIN {role} r ON r.id = ra.roleid
        WHERE r.shortname=? AND c.id=?
    ";
        $students = $DB->get_records_sql($sql, array('student', $courseid));

        return array_values($students);
    }


    /**
     * Renders html for completion box on course page
     *
     * If completion is disabled, returns empty string
     * If completion is automatic, returns an icon of the current completion state
     * If completion is manual, returns a form (with an icon inside) that allows user to
     * toggle completion
     *
     * @param stdClass $course course object
     * @param completion_info $completioninfo completion info for the course, it is recommended
     *     to fetch once for all modules in course/section for performance
     * @param cm_info $mod module to show completion for
     * @param array $displayoptions display options, not used in core
     * @return string
     */
    public function course_section_cm_completion($course, &$completioninfo, cm_info $mod, $displayoptions = array()) {
        global $CFG, $DB;
        $output = '';

        if (!$mod->is_visible_on_course_page()) {
            $output .= $this->render_block_submission_activity($mod);
            return $output;
        }

        if (!empty($displayoptions['hidecompletion']) || !isloggedin() || isguestuser() || !$mod->uservisible) {
            return $output;
        }
        if ($completioninfo === null) {
            $completioninfo = new completion_info($course);
        }
        $completion = $completioninfo->is_enabled($mod);
        if ($completion == COMPLETION_TRACKING_NONE) {
            if ($this->page->user_is_editing()) {
                $output .= html_writer::span('&nbsp;', 'filler');
            }
            $output .= $this->render_block_submission_activity($mod);
            return $output;
        }

        $completiondata = $completioninfo->get_data($mod, true);
        $completionicon = '';

        if ($this->page->user_is_editing()) {
            switch ($completion) {
                case COMPLETION_TRACKING_MANUAL :
                    $completionicon = 'manual-enabled'; break;
                case COMPLETION_TRACKING_AUTOMATIC :
                    $completionicon = 'auto-enabled'; break;
            }
        } else if ($completion == COMPLETION_TRACKING_MANUAL) {
            switch($completiondata->completionstate) {
                case COMPLETION_INCOMPLETE:
                    $completionicon = 'manual-n' . ($completiondata->overrideby ? '-override' : '');
                    break;
                case COMPLETION_COMPLETE:
                    $completionicon = 'manual-y' . ($completiondata->overrideby ? '-override' : '');
                    break;
            }
        } else { // Automatic
            switch($completiondata->completionstate) {
                case COMPLETION_INCOMPLETE:
                    $completionicon = 'auto-n' . ($completiondata->overrideby ? '-override' : '');
                    break;
                case COMPLETION_COMPLETE:
                    $completionicon = 'auto-y' . ($completiondata->overrideby ? '-override' : '');
                    break;
                case COMPLETION_COMPLETE_PASS:
                    $completionicon = 'auto-pass'; break;
                case COMPLETION_COMPLETE_FAIL:
                    $completionicon = 'auto-fail'; break;
            }
        }
        if ($completionicon) {
            $formattedname = $mod->get_formatted_name();
            if ($completiondata->overrideby) {
                $args = new stdClass();
                $args->modname = $formattedname;
                $overridebyuser = \core_user::get_user($completiondata->overrideby, '*', MUST_EXIST);
                $args->overrideuser = fullname($overridebyuser);
                $imgalt = get_string('completion-alt-' . $completionicon, 'completion', $args);
            } else {
                $imgalt = get_string('completion-alt-' . $completionicon, 'completion', $formattedname);
            }

            if ($this->page->user_is_editing()) {
                // When editing, the icon is just an image.
                $completionpixicon = new pix_icon('i/completion-'.$completionicon, $imgalt, '',
                        array('title' => $imgalt, 'class' => 'iconsmall'));
                $output .= html_writer::tag('span', $this->output->render($completionpixicon),
                        array('class' => 'autocompletion'));
            } else if ($completion == COMPLETION_TRACKING_MANUAL) {
                $newstate =
                    $completiondata->completionstate == COMPLETION_COMPLETE
                    ? COMPLETION_INCOMPLETE
                    : COMPLETION_COMPLETE;
                // In manual mode the icon is a toggle form...

                // If this completion state is used by the
                // conditional activities system, we need to turn
                // off the JS.
                $extraclass = '';
                if (!empty($CFG->enableavailability) &&
                        \core_availability\info::completion_value_used($course, $mod->id)) {
                    $extraclass = ' preventjs';
                }
                $output .= html_writer::start_tag('form', array('method' => 'post',
                    'action' => new moodle_url('/course/togglecompletion.php'),
                    'class' => 'togglecompletion'. $extraclass));
                $output .= html_writer::start_tag('div');
                $output .= html_writer::empty_tag('input', array(
                    'type' => 'hidden', 'name' => 'id', 'value' => $mod->id));
                $output .= html_writer::empty_tag('input', array(
                    'type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
                $output .= html_writer::empty_tag('input', array(
                    'type' => 'hidden', 'name' => 'modulename', 'value' => $mod->name));
                $output .= html_writer::empty_tag('input', array(
                    'type' => 'hidden', 'name' => 'completionstate', 'value' => $newstate));
                $output .= html_writer::tag('button',
                    $this->output->pix_icon('i/completion-' . $completionicon, $imgalt), array('class' => 'btn btn-link'));
                $output .= html_writer::end_tag('div');
                $output .= html_writer::end_tag('form');
            } else {
                // In auto mode, the icon is just an image.
                $completionpixicon = new pix_icon('i/completion-'.$completionicon, $imgalt, '',
                        array('title' => $imgalt));
                $output .= html_writer::tag('span', $this->output->render($completionpixicon),
                        array('class' => 'autocompletion'));
            }
        }
        $output .= $this->render_block_submission_activity($mod);
        return $output;
    }


    /**
     * Renders html to display a course search form
     *
     * @param string $value default value to populate the search field
     * @param string $format display format - 'plain' (default), 'short' or 'navbar'
     * @return string
     */
    function course_search_form($value = '', $format = 'plain') {
        static $count = 0;
        $formid = 'coursesearch';
        if ((++$count) > 1) {
            $formid .= $count;
        }

        switch ($format) {
            case 'navbar' :
                $formid = 'coursesearchnavbar';
                $inputid = 'navsearchbox';
                $inputsize = 20;
                break;
            case 'short' :
                $inputid = 'shortsearchbox';
                $inputsize = 12;
                break;
            default :
                $inputid = 'coursesearchbox';
                $inputsize = 30;
        }

        $strsearchcourses= get_string("searchcourses");
        $searchurl = new moodle_url('/course/search.php');

        $output = html_writer::start_tag('form', array('id' => $formid, 'action' => $searchurl, 'method' => 'get'));
        $output .= html_writer::start_tag('fieldset', array('class' => 'coursesearchbox invisiblefieldset'));
        $output .= html_writer::tag('label', $strsearchcourses.': ', array('for' => $inputid));
        $output .= html_writer::empty_tag('input', array('type' => 'text', 'id' => $inputid,
            'size' => $inputsize, 'name' => 'search', 'value' => s($value)));
        $output .= html_writer::empty_tag('input', array('type' => 'submit',
            'value' => get_string('go')));
        $output .= html_writer::end_tag('fieldset');
        $output .= html_writer::end_tag('form');

        return $output;
    }


    /**
     * Renders HTML to display one course module for display within a section.
     *
     * This function calls:
     * {@link core_course_renderer::course_section_cm()}
     *
     * @param stdClass $course
     * @param completion_info $completioninfo
     * @param cm_info $mod
     * @param int|null $sectionreturn
     * @param array $displayoptions
     * @return String
     */
    public function course_section_cm_list_item($course, &$completioninfo, cm_info $mod, $sectionreturn, $displayoptions = array()) {
        $output = '';
        if ($modulehtml = $this->course_section_cm($course, $completioninfo, $mod, $sectionreturn, $displayoptions)) {
            $modclasses = 'activity ' . $mod->modname . ' modtype_' . $mod->modname . ' ' . $mod->extraclasses;
            $output .= html_writer::tag('li', $modulehtml, array('class' => $modclasses, 'id' => 'module-' . $mod->id));
        }
        return $output;
    }

    /**
     * Renders HTML to display one course module in a course section
     *
     * This includes link, content, availability, completion info and additional information
     * that module type wants to display (i.e. number of unread forum posts)
     *
     * This function calls:
     * {@link core_course_renderer::course_section_cm_name()}
     * {@link core_course_renderer::course_section_cm_text()}
     * {@link core_course_renderer::course_section_cm_availability()}
     * {@link core_course_renderer::course_section_cm_completion()}
     * {@link course_get_cm_edit_actions()}
     * {@link core_course_renderer::course_section_cm_edit_actions()}
     *
     * @param stdClass $course
     * @param completion_info $completioninfo
     * @param cm_info $mod
     * @param int|null $sectionreturn
     * @param array $displayoptions
     * @return string
     */
    public function course_section_cm($course, &$completioninfo, cm_info $mod, $sectionreturn, $displayoptions = array()) {
        $output = '';
        // We return empty string (because course module will not be displayed at all)
        // if:
        // 1) The activity is not visible to users
        // and
        // 2) The 'availableinfo' is empty, i.e. the activity was
        //     hidden in a way that leaves no info, such as using the
        //     eye icon.
        if (!$mod->is_visible_on_course_page()) {
            return $output;
        }

        $indentclasses = 'mod-indent';
        if (!empty($mod->indent)) {
            $indentclasses .= ' mod-indent-'.$mod->indent;
            if ($mod->indent > 15) {
                $indentclasses .= ' mod-indent-huge';
            }
        }

        $output .= html_writer::start_tag('div');

        if ($this->page->user_is_editing()) {
            $output .= course_get_cm_move($mod, $sectionreturn);
        }

        $output .= html_writer::start_tag('div', array('class' => 'mod-indent-outer'));

        // This div is used to indent the content.
        $output .= html_writer::div('', $indentclasses);

        // Start a wrapper for the actual content to keep the indentation consistent
        $output .= html_writer::start_tag('div');

        // Display the link to the module (or do nothing if module has no url)
        $cmname = $this->course_section_cm_name($mod, $displayoptions);

        if (!empty($cmname)) {
            // Start the div for the activity title, excluding the edit icons.
            $output .= html_writer::start_tag('div', array('class' => 'activityinstance'));

            $output .= $cmname;


            // Module can put text after the link (e.g. forum unread)
            $output .= $mod->afterlink;

            // Closing the tag which contains everything but edit icons. Content part of the module should not be part of this.
            $output .= html_writer::end_tag('div'); // .activityinstance
        }

        // If there is content but NO link (eg label), then display the
        // content here (BEFORE any icons). In this case cons must be
        // displayed after the content so that it makes more sense visually
        // and for accessibility reasons, e.g. if you have a one-line label
        // it should work similarly (at least in terms of ordering) to an
        // activity.
        $contentpart = $this->course_section_cm_text($mod, $displayoptions);
        $url = $mod->url;
        if (empty($url)) {
            $output .= $contentpart;
        }


        $modicons = '';
        if ($this->page->user_is_editing()) {
            $editactions = course_get_cm_edit_actions($mod, $mod->indent, $sectionreturn);
            $modicons .= ' '. $this->course_section_cm_edit_actions($editactions, $mod, $displayoptions);
            $modicons .= $mod->afterediticons;
        }

        $modicons .= $this->course_section_cm_completion($course, $completioninfo, $mod, $displayoptions);

        if (!empty($modicons)) {
            $output .= html_writer::span($modicons, 'actions');
        }

        // Show availability info (if module is not available).
        $output .= $this->course_section_cm_availability($mod, $displayoptions);

        // If there is content AND a link, then display the content here
        // (AFTER any icons). Otherwise it was displayed before
        if (!empty($url)) {
            $output .= $contentpart;
        }

        $output .= html_writer::end_tag('div'); // $indentclasses

        // End of indentation div.
        $output .= html_writer::end_tag('div');

        $output .= html_writer::end_tag('div');
        return $output;
    }


    /**
     * Renders html to display the module content on the course page (i.e. text of the labels)
     *
     * @param cm_info $mod
     * @param array $displayoptions
     * @return string
     */
    public function course_section_cm_text(cm_info $mod, $displayoptions = array()) {
        $output = '';
        if (!$mod->is_visible_on_course_page()) {
            // nothing to be displayed to the user
            return $output;
        }
        $content = $mod->get_formatted_content(array('overflowdiv' => true, 'noclean' => true));
        list($linkclasses, $textclasses) = $this->course_section_cm_classes($mod);
        if ($mod->url && $mod->uservisible) {
            if ($content) {
                // If specified, display extra content after link.
                $output = html_writer::tag('div', $content, array('class' =>
                        trim('contentafterlink ' . $textclasses)));
            }
        } else {
            $groupinglabel = $mod->get_grouping_label($textclasses);

            // No link, so display only content.
            $output = html_writer::tag('div', $content . $groupinglabel,
                    array('class' => 'contentwithoutlink ' . $textclasses));
        }
        return $output;
    }



    public function render_block_submission_activity(cm_info $mod){
        global $CFG, $DB, $COURSE, $USER;
        $html = '';

        if ($COURSE->format != 'stardust') {
            return $html;
        }

        // define activity status and icon
        $mextra = $DB->get_record_sql("
            SELECT
                m.*
            FROM
                {".$mod->modname."} m
            WHERE
                m.id = ?", array($mod->instance));

        if (empty($mextra)){
            return '';
        }

        // create temp $mod object with few fields to get current activity status correctly (for compat with func stardust_activity_status in lib.php)
        $tmod = new \stdClass();
        $tmod->id = $mod->id;
        $tmod->added = $mod->added;
        $mstatus='';

        if ($mod->modname == 'assign' || $mod->modname == 'quiz' || $mod->modname == 'questionnaire') {

            $tmod->duedate = isset($mextra->duedate) ? $mextra->duedate : 0;
            $tmod->cutoffdate= isset($mextra->cutoffdate) ? $mextra->cutoffdate : 0;

            if ($mod->modname == 'quiz'){
                $tmod->cutoffdate= isset($mextra->timeclose) ? $mextra->timeclose:0;
            }

            if ($mod->modname == 'questionnaire'){
                $tmod->cutoffdate= isset($mextra->closedate) ? $mextra->closedate:0;
            }

            $activitystatus = $this->davidson_activity_status($tmod, $mod);
            $countobj = $this->davidson_activity_count_users_attempt($mod);
            $count_users = $countobj['users'];
            $count_max_users = $countobj['maxusers'];

            // $mstatus  =  isset($activitystatus['grade'])?$activitystatus['grade']:(isset($activitystatus['modstatus'])?$activitystatus['modstatus']:"");

            $coursecontext = context_course::instance($COURSE->id);
            if(is_siteadmin() || has_capability('moodle/course:update', $coursecontext)) {
                $mstatus='';
                $gradeurl = '';
                if ($mod->modname == 'assign'){
                    $url = $CFG->wwwroot.'/mod/assign/view.php?id='.$mod->context->instanceid.'&action=grading';
                    $gradeurl = '<a target="__blank" href="'.$url.'">'.get_string('grades').'</a>';
                }

                if ($mod->modname == 'questionnaire'){
                    $url = $CFG->wwwroot.'/mod/questionnaire/report.php?instance='.$mod->instance;
                    $gradeurl = '<a target="__blank" href="'.$url.'">'.get_string('grades').'</a>';
                }

                if ($mod->modname == 'quiz'){
                    $url = $CFG->wwwroot.'/mod/quiz/report.php?id='.$mod->context->instanceid.'&mode=overview';
                    $gradeurl = '<a target="__blank" href="'.$url.'">'.get_string('grades').'</a>';
                }

                $mstatus = get_string('submitted', 'theme_stardust').' ('.$count_users.' '.get_string('of', 'theme_stardust').' '.$count_max_users.') '.$gradeurl;
            }
        }

        $html = html_writer::tag('span', $mstatus, array('class' => ''));
        return $html;
    }

    public function davidson_activity_count_users_attempt($mod){
        global $DB, $USER, $COURSE;

        $count = 0;
        $maxcount = count($this->get_students_course($COURSE->id));

        //Quiz
        if($mod->modname == 'quiz'){
            $sql = "
            SELECT *
            FROM {quiz_attempts}
            WHERE quiz=? AND state='finished'
            GROUP BY userid;
        ";

            $query = $DB->get_records_sql($sql, array($mod->instance));
            $count = count($query);
        }

        //Questionnaire
        if($mod->modname == 'questionnaire'){
            $sid = $DB->get_record('questionnaire', array('id' => $mod->instance), 'sid');
            $sql = "
            SELECT *
            FROM {questionnaire_response}
            WHERE survey_id=? AND complete='y'
            GROUP BY userid;
        ";

            $query = $DB->get_records_sql($sql, array($sid->sid));
            $count = count($query);
        }

        //Assign
        if($mod->modname == 'assign'){
            $sql = "
            SELECT *
            FROM {assign_submission}
            WHERE assignment=? AND status='submitted'
            GROUP BY userid;
        ";

            $query = $DB->get_records_sql($sql, array($mod->instance));
            $count = count($query);
        }

        return array('maxusers' => $maxcount, 'users' => $count);
    }

    public function davidson_activity_status($module, cm_info $mod) {
        global $DB, $USER, $CFG;

        $stageAssign = array('submitted' => false, 'grade' => false, );

        switch ($mod->modname){
            case "assign":
                $rowas = $DB->get_record('assign_submission', array('assignment' => $mod->instance, 'userid' => $USER->id, 'status' => 'submitted'));
                if($rowas){
                    $stageAssign['submitted'] = true;
                }
                $rowag = $DB->get_record('assign_grades', array('assignment' => $mod->instance, 'userid' => $USER->id));
                if($rowag){
                    $stageAssign['grade'] = $rowag->grade;
                    $stageAssign['submitted'] = true;
                }

                break;
            case "questionnaire":
                $sid = $DB->get_record('questionnaire', array('id' => $mod->instance), 'sid');
                $rowas = $DB->get_records('questionnaire_response', array('survey_id' => $sid->sid, 'userid' => $USER->id, 'complete' => 'y'));
                if($rowas){
                    $stageAssign['submitted'] = true;
                }

                break;
            case "quiz":
                // We can have more then one finished attempt in a quiz. (IGNORE_MULTIPLE)
                $rowas = $DB->get_record('quiz_attempts', array('quiz' => $mod->instance, 'userid' => $USER->id, 'state' => 'finished'), '*', IGNORE_MULTIPLE);
                if($rowas){
                    $stageAssign['submitted'] = true;
                }
                $rowag = $DB->get_record('quiz_grades', array('quiz' => $mod->instance, 'userid' => $USER->id));
                if($rowag){
                    $stageAssign['grade'] = $rowag->grade;
                    $stageAssign['submitted'] = true;
                }
                break;
            default:
        }

        //get module completion state
        $cmcomplstateraw = $DB->get_record('course_modules_completion', array('coursemoduleid' => $module->id,'userid'=>$USER->id), 'completionstate');
        $cmcomplstate = $cmcomplstateraw ? true : false; // completed or not activity
        $activitystatus = array();
        $cutoffdate=$module->cutoffdate;
        $added = $module->added;
        $duedate = $module->duedate;
        $currenttime = time();
        $openforsubmission = false;
        $actionwithtask = false;
        $turntotheteacher = false;
        $mincutoffdate =  ($cutoffdate * $duedate == 0) ? max($cutoffdate, $duedate) :  min($cutoffdate, $duedate);

        if (!empty($mincutoffdate)||$stageAssign['submitted']||$stageAssign['grade']) {
            $timeratio = round(($currenttime - $added) / ($mincutoffdate - $added) * 100, 0, PHP_ROUND_HALF_DOWN);
            $timeline = ($timeratio > 100) ? 100 : $timeratio;
            if ($cmcomplstate||$stageAssign['submitted']||$stageAssign['grade']) {
                $modstyle = 'mod_green';
                if($stageAssign['grade']){
                    $modstatus =  get_string('tested', 'theme_stardust').' '. round($stageAssign['grade']);
                }elseif($stageAssign['submitted']){
                    $modstatus =  get_string('submitted', 'theme_stardust');
                }else{
                    $modstatus =  get_string('complete', 'theme_stardust');
                }
            }
            elseif ($mincutoffdate - $currenttime <= 0) {
                $modstyle = 'mod_red';
                $modstatus = get_string('cut_of_date', 'theme_stardust');
                $turntotheteacher = true;
                // one day before assignment
            }elseif ( 0 < ($mincutoffdate - $currenttime) &&  ($mincutoffdate - $currenttime) <= (1*24*60*60)) {
                $modstyle = 'mod_orange';
                $modstatus = get_string('one_days_before_assignment', 'theme_stardust');
                $openforsubmission = true;
                // two day before assignment
            }elseif ( (1*24*60*60) < ($mincutoffdate - $currenttime) &&  ($mincutoffdate - $currenttime) <= (2*24*60*60)) {
                $modstyle = 'mod_orange';
                $modstatus = get_string('two_days_before_assignment', 'theme_stardust');
                $openforsubmission = true;
                // more than two days before assignment
            } else {
                $a= new \stdClass();
                $a->date=date("d/m/Y H:i", $mincutoffdate);
                $modstatus = get_string('cut_of_date_label', 'theme_stardust',$a) ;
                $modstyle = 'mod_gray';
                $actionwithtask = true;
            }
        } else {
            $modstatus =  get_string('no_submission_date', 'theme_stardust');
            $modstyle = 'mod_gray';
            $timeline = 0;
            $mincutoffdate = time()+ 2*364*24*60*60;
            $actionwithtask = true;
        }

        $activitystatus['cmid'] = $module->id;
        $activitystatus['duedate'] = $duedate;
        $activitystatus['cutoffdate'] = $cutoffdate;
        $activitystatus['timeline'] = $timeline;
        $activitystatus['modstyle'] = $modstyle;
        $activitystatus['modstatus'] = $modstatus;
        $activitystatus['mincutoffdate'] = $mincutoffdate;
        $activitystatus['openforsubmission'] = $openforsubmission;
        $activitystatus['actionwithtask'] = $actionwithtask;
        $activitystatus['turntotheteacher'] = $turntotheteacher;

        return $activitystatus;
    }


} // end class theme_stardust_core_course_renderer
