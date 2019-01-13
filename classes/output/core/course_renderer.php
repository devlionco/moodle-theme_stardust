<?php
/**
 * Override course page output
 */
namespace theme_stardust\output\core;

use core_text;
use html_writer;
use stdClass;
use completion_info;

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

          $micon = $this->output->image_url('/'.$mod->modname.'/'.$activitystatus['modstyle'], 'theme');
          // $micon = $mod->get_icon_url();
          $mstatus = $activitystatus['modstatus'];
        } else {
          $micon = $mod->get_icon_url();
          $mstatus = '';
        }

        // Display link itself.
        $modstyle = (!empty($activitystatus['modstyle']))? $activitystatus['modstyle'] : '';

        $activitylink = html_writer::empty_tag('img', array('src' => $micon,
                'class' => 'iconlarge activityicon '.$modstyle,
                'alt' => ' ',
                'role' => 'presentation')) .
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

                if ($modulehtml = $this->course_section_cm_list_item($course,
                        $completioninfo, $mod, $sectionreturn, $displayoptions)) {
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

} // end class theme_stardust_core_course_renderer
