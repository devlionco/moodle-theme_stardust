<?php
/**
 * Override course page output
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/format/topcoll/renderer.php');

class theme_stardust_format_topcoll_renderer extends format_topcoll_renderer {


    /**
     * Calculates section progress in percents
     *
     * @param stdClass $section The course_section entry from DB.
     * @return int Progress in percents without sign '%'
     */
    protected function sectionprogress($section) {
        global $DB, $USER, $modinfo, $course;

        // get all current user's completions on current course
        $usercourseallcmcraw = $DB->get_records_sql("
        SELECT
            cmc.*
        FROM
            {course_modules} cm
            INNER JOIN {course_modules_completion} cmc ON cmc.coursemoduleid=cm.id
        WHERE
            cm.course=? AND cmc.userid=?", array($course->id, $USER->id));
        $usercmscompletions = array();
        foreach ($usercourseallcmcraw as $record) {
            //$usercourseallcmc[$record->coursemoduleid] = (array)$record;
            if ($record->completionstate <> 0) {
                $usercmscompletions[] = $record->coursemoduleid;
            }
        }

        // get current course's completable cms
        $ccompetablecms = array();
        $coursefminfo = get_fast_modinfo($course);
        foreach ($coursefminfo->get_cms() as $cm) {
            if ($cm->completion != COMPLETION_TRACKING_NONE && !$cm->deletioninprogress) {
                $ccompetablecms[] = $cm->id;
            }
        }

        $completedactivitiescount = 0;
        @$scms = $modinfo->sections[$section->section]; // get current section activities
        if (!empty($scms)) {
            $allcmsinsectioncount = count($scms);          // first count all cms in section
            foreach ($scms as $arid=>$scmid) {              // for each acivity in section
                if (!in_array($scmid, $ccompetablecms)) {
                    unset($scms[$arid]); // unset cms that are not  completable
                } else {
                    if (in_array($scmid, $usercmscompletions)) {
                        $completedactivitiescount++; // if cm is compledted - count it
                    }
                }
            }
            $completedcmsinsectioncount = count($scms);
            $csectionpreogress = round($completedcmsinsectioncount/$allcmsinsectioncount*100);
            return $csectionpreogress;
        } else {
            return $csectionpreogress = 0;
        }
    }

    /**
     * Generate the display of the header part of a section before
     * course modules are included.
     *
     * @param stdClass $section The course_section entry from DB.
     * @param stdClass $course The course entry from DB.
     * @param bool $onsectionpage true if being printed on a section page.
     * @param int $sectionreturn The section to return to after an action.
     * @return string HTML to output.
     */
    protected function section_header($section, $course, $onsectionpage, $sectionreturn = null) {
        $o = '';

        $sectionstyle = '';
        $rightcurrent = '';
        $context = context_course::instance($course->id);

        if ($section->section != 0) {
            // Only in the non-general sections.
            if (!$section->visible) {
                $sectionstyle = ' hidden';
            }
            if ($section->section == $this->currentsection) {
                $sectionstyle = ' current';
                $rightcurrent = ' left';
            }
        }

        if ((!$this->formatresponsive) && ($section->section != 0) &&
            ($this->tcsettings['layoutcolumnorientation'] == 2)) { // Horizontal column layout.
            $sectionstyle .= ' ' . $this->get_column_class($this->tcsettings['layoutcolumns']);
        }
        $liattributes = array(
            'id' => 'section-' . $section->section,
            'class' => 'section main clearfix' . $sectionstyle,
            'role' => 'region',
            'aria-label' => $this->courseformat->get_topcoll_section_name($course, $section, false)
        );
        if (($this->formatresponsive) && ($this->tcsettings['layoutcolumnorientation'] == 2)) { // Horizontal column layout.
            $liattributes['style'] = 'width: ' . $this->tccolumnwidth . '%;';
        }
        $o .= html_writer::start_tag('li', $liattributes);

        if ((($this->mobiletheme === false) && ($this->tablettheme === false)) || ($this->userisediting)) {
            $leftcontent = $this->section_left_content($section, $course, $onsectionpage);
            $rightcontent = '';
            if (($section->section != 0) && $this->userisediting && has_capability('moodle/course:update', $context)) {
                $url = new moodle_url('/course/editsection.php', array('id' => $section->id, 'sr' => $sectionreturn));

                $rightcontent .= html_writer::link($url,
                    $this->output->pix_icon('t/edit', get_string('edit')),
                        array('title' => get_string('editsection', 'format_topcoll'), 'class' => 'tceditsection'));
            }
            $rightcontent .= $this->section_right_content($section, $course, $onsectionpage);

            if ($this->rtl) {
                // Swap content.
                $o .= html_writer::tag('div', $rightcontent, array('class' => 'right side'));
                $o .= html_writer::tag('div', $leftcontent, array('class' => 'left side'));
            } else {
                $o .= html_writer::tag('div', $leftcontent, array('class' => 'left side'));
                $o .= html_writer::tag('div', $rightcontent, array('class' => 'right side'));
            }
        }

        if ((!($section->toggle === null)) && ($section->toggle == true)) {
            $toggleclass = 'toggle_open';
            $ariapressed = 'true';
            $sectionclass = ' sectionopen';
        } else {
            $toggleclass = 'toggle_closed';
            $ariapressed = 'false';
            $sectionclass = '';
        }

        $o .= html_writer::start_tag('div', array('class' => 'content '.$sectionclass));

        if (($onsectionpage == false) && ($section->section != 0)) {
            $o .= html_writer::start_tag('div',
                array('class' => 'sectionhead toggle toggle-'.$this->tcsettings['toggleiconset'],
                'id' => 'toggle-'.$section->section)
            );

              // if ((!($section->toggle === null)) && ($section->toggle == true)) {
              //     $toggleclass = 'toggle_open';
              //     $ariapressed = 'true';
              //     $sectionclass = ' sectionopen';
              // } else {
              //     $toggleclass = 'toggle_closed';
              //     $ariapressed = 'false';
              //     $sectionclass = '';
              // }
            $toggleclass .= ' the_toggle ' . $this->tctoggleiconsize;
            $o .= html_writer::start_tag('span',
                array('class' => $toggleclass, 'role' => 'button', 'aria-pressed' => $ariapressed)
            );

            if (empty($this->tcsettings)) {
                $this->tcsettings = $this->courseformat->get_settings();
            }

            if ($this->userisediting) {
                $title = $this->section_title($section, $course);
            } else {
                $o .= html_writer::tag('span', $section->section); // add section number
                $title = $this->courseformat->get_topcoll_section_name($course, $section, true);
            }
            if ((($this->mobiletheme === false) && ($this->tablettheme === false)) || ($this->userisediting)) {
                $o .= $this->output->heading($title, 3, 'sectionname');
            } else {
                $o .= html_writer::tag('h3', $title); // Moodle H3's look bad on mobile / tablet with CT so use plain.
            }

            $o .= $this->section_availability($section);

            $o .= html_writer::end_tag('span');
            $o .= html_writer::end_tag('div');

            // add progress bar to section header
            $o .= html_writer::start_tag('div', array('class' => 'sectionprogress'));


            $o .= html_writer::tag('span', $this->sectionprogress($section).'%', array(
                'class' => 'sectionprogress-percent',
                'style' => "left: calc(".$this->sectionprogress($section)."% - 30px)",
            ));
            $o .= html_writer::tag('div', '',
              array(
                'class' => 'sectionprogress-bar',
                'role'  => "progressbar",
                'style' => "width: ".$this->sectionprogress($section)."%",
                'aria-valuenow' => $this->sectionprogress($section),
                'aria-valuemin' => "0",
                'aria-valuemax' => "100",
              )
            );

            // $o .= $this->sectionprogress($section);
            $o .= html_writer::end_tag('div');

            if ($this->tcsettings['showsectionsummary'] == 2) {
                $o .= $this->section_summary_container($section);
            }

            $o .= html_writer::start_tag('div',
                array('class' => 'sectionbody toggledsection' . $sectionclass,
                'id' => 'toggledsection-' . $section->section)
            );

            if ($this->userisediting && has_capability('moodle/course:update', $context)) {
                $url = new moodle_url('/course/editsection.php', array('id' => $section->id, 'sr' => $sectionreturn));
                $o .= html_writer::link($url,
                    $this->output->pix_icon('t/edit', get_string('edit')),
                    array('title' => get_string('editsection', 'format_topcoll'))
                );
            }

            if ($this->tcsettings['showsectionsummary'] == 1) {
                $o .= $this->section_summary_container($section);
            }
        } else {
            // When on a section page, we only display the general section title, if title is not the default one.
            $hasnamesecpg = ($section->section == 0 && (string) $section->name !== '');

            if ($hasnamesecpg) {
                $o .= $this->output->heading($this->section_title($section, $course), 3, 'section-title');
            }
            $o .= $this->section_availability($section);
            $o .= html_writer::start_tag('div', array('class' => 'summary'));
            $o .= $this->format_summary_text($section);

            if ($this->userisediting && has_capability('moodle/course:update', $context)) {
                $url = new moodle_url('/course/editsection.php', array('id' => $section->id, 'sr' => $sectionreturn));
                $o .= html_writer::link($url,
                    $this->output->pix_icon('t/edit', get_string('edit')),
                    array('title' => get_string('editsection', 'format_topcoll'))
                );
            }
            $o .= html_writer::end_tag('div');
        }
        return $o;
    }

} // end class
