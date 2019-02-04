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
namespace theme_fordson\output;

use coding_exception;
use html_writer;
use tabobject;
use tabtree;
use custom_menu_item;
use custom_menu;
use block_contents;
use navigation_node;
use action_link;
use stdClass;
use moodle_url;
use preferences_groups;
use action_menu;
use help_icon;
use single_button;
use single_select;
use paging_bar;
use url_select;
use context_course;
use pix_icon;
use theme_config;
use action_menu_filler;
use action_menu_link_secondary;
use core_text;

defined('MOODLE_INTERNAL') || die;

require_once ($CFG->dirroot . "/course/renderer.php");
require_once ($CFG->libdir . '/coursecatlib.php');
require_once ($CFG->dirroot . "/message/lib.php");
require_once ($CFG->libdir . '/badgeslib.php');
require_once ($CFG->libdir . '/externallib.php');

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_stardust
 * @copyright  2018 Devlion.co
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class core_renderer extends \theme_boost\output\core_renderer {


  /**
   * Construct a user menu, returning HTML that can be echoed out by a
   * layout file.
   *
   * @param stdClass $user A user object, usually $USER.
   * @param bool $withlinks true if a dropdown should be built.
   * @return string HTML fragment.
   */
  public function user_menu($user = null, $withlinks = null) {

      global $USER, $CFG;
      require_once($CFG->dirroot . '/user/lib.php');

      if (is_null($user)) {
          $user = $USER;
      }

      // Note: this behaviour is intended to match that of core_renderer::login_info,
      // but should not be considered to be good practice; layout options are
      // intended to be theme-specific. Please don't copy this snippet anywhere else.
      if (is_null($withlinks)) {
          $withlinks = empty($this->page->layout_options['nologinlinks']);
      }

      // Add a class for when $withlinks is false.
      $usermenuclasses = 'usermenu';
      if (!$withlinks) {
          $usermenuclasses .= ' withoutlinks';
      }

      $returnstr = "";

      // If during initial install, return the empty return string.
      if (during_initial_install()) {
          return $returnstr;
      }

      $loginpage = $this->is_login_page();
      $loginurl = get_login_url();
      // If not logged in, show the typical not-logged-in string.
      if (!isloggedin()) {
          $returnstr = get_string('loggedinnot', 'moodle');
          if (!$loginpage) {
              $returnstr .= " (<a href=\"$loginurl\">" . get_string('login') . '</a>)';
          }
          return html_writer::div(
              html_writer::span(
                  $returnstr,
                  'login'
              ),
              $usermenuclasses
          );

      }

      // If logged in as a guest user, show a string to that effect.
      if (isguestuser()) {
          $returnstr = get_string('loggedinasguest');
          if (!$loginpage && $withlinks) {
              $returnstr .= " (<a href=\"$loginurl\">".get_string('login').'</a>)';
          }

          return html_writer::div(
              html_writer::span(
                  $returnstr,
                  'login'
              ),
              $usermenuclasses
          );
      }

      // Get some navigation opts.
      $opts = user_get_user_navigation_info($user, $this->page);

      $avatarclasses = "avatars";
      $avatarcontents = html_writer::span($opts->metadata['useravatar'], 'avatar current');
      $usertextcontents = $opts->metadata['userfullname'];

      // Other user.
      if (!empty($opts->metadata['asotheruser'])) {
          $avatarcontents .= html_writer::span(
              $opts->metadata['realuseravatar'],
              'avatar realuser'
          );
          $usertextcontents = $opts->metadata['realuserfullname'];
          $usertextcontents .= html_writer::tag(
              'span',
              get_string(
                  'loggedinas',
                  'moodle',
                  html_writer::span(
                      $opts->metadata['userfullname'],
                      'value'
                  )
              ),
              array('class' => 'meta viewingas')
          );
      }

      // Role.
      if (!empty($opts->metadata['asotherrole'])) {
          $role = core_text::strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['rolename'])));
          $usertextcontents .= html_writer::span(
              $opts->metadata['rolename'],
              'meta role role-' . $role
          );
      }

      // User login failures.
      if (!empty($opts->metadata['userloginfail'])) {
          $usertextcontents .= html_writer::span(
              $opts->metadata['userloginfail'],
              'meta loginfailures'
          );
      }

      // MNet.
      if (!empty($opts->metadata['asmnetuser'])) {
          $mnet = strtolower(preg_replace('#[ ]+#', '-', trim($opts->metadata['mnetidprovidername'])));
          $usertextcontents .= html_writer::span(
              $opts->metadata['mnetidprovidername'],
              'meta mnet mnet-' . $mnet
          );
      }

      $returnstr .= html_writer::span(
          html_writer::span($usertextcontents, 'usertext mr-3') .
          html_writer::span($avatarcontents, $avatarclasses),
          'userbutton'
      );

      // Create a divider (well, a filler).
      $divider = new action_menu_filler();
      $divider->primary = false;

      $am = new action_menu();
      $am->set_menu_trigger(
          $returnstr
      );
      $am->set_alignment(action_menu::TR, action_menu::BR);
      $am->set_nowrap_on_items();
      if ($withlinks) {
          $navitemcount = count($opts->navitems);
          $idx = 0;
          foreach ($opts->navitems as $key => $value) {

              switch ($value->itemtype) {
                  case 'divider':
                      // If the nav item is a divider, add one and skip link processing.
                      $am->add($divider);
                      break;

                  case 'invalid':
                      // Silently skip invalid entries (should we post a notification?).
                      break;

                  case 'link':
                      // Process this as a link item.
                      $pix = null;
                      if (isset($value->pix) && !empty($value->pix)) {
                          $pix = new pix_icon($value->pix, $value->title, null, array('class' => 'iconsmall'));
                      } else if (isset($value->imgsrc) && !empty($value->imgsrc)) {
                          $value->title = html_writer::img(
                              $value->imgsrc,
                              $value->title,
                              array('class' => 'iconsmall')
                          ) . $value->title;
                      }

                      $al = new action_menu_link_secondary(
                          $value->url,
                          $pix,
                          $value->title,
                          array('class' => 'icon')
                      );
                      if (!empty($value->titleidentifier)) {
                          $al->attributes['data-title'] = $value->titleidentifier;
                      }
                      $am->add($al);
                      break;
              }

              $idx++;

              // Add dividers after the first item and before the last item.
              if ($idx == 1 || $idx == $navitemcount - 1) {
                  $am->add($divider);
              }
          }
      }

      return html_writer::div(
          $this->render($am),
          $usermenuclasses
      );
  }


    /**
     * Allow plugins to provide some content to be rendered in the navbar.
     * The plugin must define a PLUGIN_render_navbar_output function that returns
     * the HTML they wish to add to the navbar.
     *
     * @return string HTML for the navbar
     */
    public function navbar_plugin_output() {
        $output = '';

        if ($pluginsfunction = get_plugins_with_function('render_navbar_output')) {
            foreach ($pluginsfunction as $plugintype => $plugins) {
                foreach ($plugins as $pluginfunction) {
                    $output .= $pluginfunction($this);
                }
            }
        }

        return $output;
    }

    protected function render_custom_menu(custom_menu $menu) {
        global $CFG;

        $langs = get_string_manager()->get_list_of_translations();
        $haslangmenu = $this->lang_menu() != '';

        if (!$menu->has_children() && !$haslangmenu) {
            return '';
        }

        if ($haslangmenu) {
            $strlang = get_string('language');
            $currentlang = current_language();
            // if (isset($langs[$currentlang])) {
            //     $currentlang = $langs[$currentlang];
            // } else {
            //     $currentlang = $strlang;
            // }
            $this->language = $menu->add($currentlang, new moodle_url('#'), $strlang, 10000);
            foreach ($langs as $langtype => $langname) {
                $this->language->add($langname, new moodle_url($this->page->url, array('lang' => $langtype)), $langname);
            }
        }

        $content = '';
        foreach ($menu->get_children() as $item) {
            $context = $item->export_for_template($this);
            $content .= $this->render_from_template('core/custom_menu_item', $context);
        }

        return $content;
    }

    public function get_stardust_logo() {
        global $OUTPUT;

        $logourl = (current_language() == "he") ? 'header/logo_davidson_he' : 'header/logo_davidson_eng';
        $output = $OUTPUT->image_url($logourl, 'theme');

        return $output;
    }

    public function get_stardust_moodle_logo() {
        global $OUTPUT;
        $output = $OUTPUT->image_url('header/logo_moodle', 'theme');

        return $output;
    }

    /**
     * Wrapper for header elements.
     *
     * @return string HTML to display the main header.
     */
    public function full_header() {

        global $PAGE, $COURSE;

        $html = html_writer::start_tag('header', array(
            'id' => 'page-header',
            'class' => 'row'
        ));
        $html .= html_writer::start_div('col-xs-12 p-a-1');
        $html .= html_writer::start_div('card');
        $html .= html_writer::start_div('headerfade');
        $html .= html_writer::start_div('card-block');
        if (!isset($PAGE->theme->settings->coursemanagementtoggle)) {
            $html .= html_writer::div($this->context_header_settings_menu() , 'pull-xs-right context-header-settings-menu');
        }
        else if (isset($COURSE->id) && $COURSE->id == 1) {
            $html .= html_writer::div($this->context_header_settings_menu() , 'pull-xs-right context-header-settings-menu');
        }
        $html .= html_writer::start_div('pull-xs-left');
        $context_header = $this->context_header();
        $html .= html_writer::link(new moodle_url('/course/view.php', array('id' => $PAGE->course->id)) , $context_header);
        $html .= html_writer::end_div();
        $pageheadingbutton = $this->page_heading_button();
        if (empty($PAGE->layout_options['nonavbar'])) {
            $html .= html_writer::start_div('clearfix w-100 pull-xs-left', array(
                'id' => 'page-navbar'
            ));
            $html .= html_writer::tag('div', $this->navbar() , array(
                'class' => 'breadcrumb-nav'
            ));
            $html .= html_writer::div($pageheadingbutton, 'breadcrumb-button pull-xs-right');
            $html .= html_writer::end_div();
        }
        else if ($pageheadingbutton) {
            $html .= html_writer::div($pageheadingbutton, 'breadcrumb-button nonavbar pull-xs-right');
        }
        $html .= html_writer::tag('div', $this->course_header() , array(
            'id' => 'course-header'
        ));
        $html .= html_writer::end_div();
        $html .= html_writer::end_div();
        $html .= html_writer::end_div();
        $html .= html_writer::end_div();
        $html .= html_writer::end_tag('header');
        return $html;
    }

    public function image_url($imagename, $component = 'moodle') {
        // Strip -24, -64, -256  etc from the end of filetype icons so we
        // only need to provide one SVG, see MDL-47082.
        $imagename = \preg_replace('/-\d\d\d?$/', '', $imagename);
        return $this->page->theme->image_url($imagename, $component);
    }

    public function headerimage() {
      // TODO remove headerimage setting from theme-setting
        return '';
        global $CFG, $COURSE, $PAGE, $OUTPUT;
        // Get course overview files.
        if (empty($CFG->courseoverviewfileslimit)) {
            return '';
        }
        require_once ($CFG->libdir . '/filestorage/file_storage.php');
        require_once ($CFG->dirroot . '/course/lib.php');
        $fs = get_file_storage();
        $context = context_course::instance($COURSE->id);
        $files = $fs->get_area_files($context->id, 'course', 'overviewfiles', false, 'filename', false);
        if (count($files)) {
            $overviewfilesoptions = course_overviewfiles_options($COURSE->id);
            $acceptedtypes = $overviewfilesoptions['accepted_types'];
            if ($acceptedtypes !== '*') {
                // Filter only files with allowed extensions.
                require_once ($CFG->libdir . '/filelib.php');
                foreach ($files as $key => $file) {
                    if (!file_extension_in_typegroup($file->get_filename() , $acceptedtypes)) {
                        unset($files[$key]);
                    }
                }
            }
            if (count($files) > $CFG->courseoverviewfileslimit) {
                // Return no more than $CFG->courseoverviewfileslimit files.
                $files = array_slice($files, 0, $CFG->courseoverviewfileslimit, true);
            }
        }

        // Get course overview files as images - set $courseimage.
        // The loop means that the LAST stored image will be the one displayed if >1 image file.
        $courseimage = '';
        foreach ($files as $file) {
            $isimage = $file->is_valid_image();
            if ($isimage) {
                $courseimage = file_encode_url("$CFG->wwwroot/pluginfile.php", '/' . $file->get_contextid() . '/' . $file->get_component() . '/' . $file->get_filearea() . $file->get_filepath() . $file->get_filename() , !$isimage);
            }
        }

        $headerbg = $PAGE->theme->setting_file_url('headerdefaultimage', 'headerdefaultimage');
        $headerbgimgurl = $PAGE->theme->setting_file_url('headerdefaultimage', 'headerdefaultimage', true);
        $defaultimgurl = $OUTPUT->image_url('headerbg', 'theme');

        // Create html for header.
        $html = html_writer::start_div('headerbkg');
        // If course image display it in separate div to allow css styling of inline style.
        if (theme_fordson_get_setting('showcourseheaderimage') && $courseimage) {
            $html .= html_writer::start_div('withimage', array(
                'style' => 'background-image: url("' . $courseimage . '"); background-size: cover; background-position:center;
                width: 100%; height: 100%;'
            ));
            $html .= html_writer::end_div(); // End withimage inline style div.

        } else if (theme_fordson_get_setting('showcourseheaderimage') && !$courseimage && isset($headerbg)) {
            $html .= html_writer::start_div('customimage', array(
                'style' => 'background-image: url("' . $headerbgimgurl . '"); background-size: cover; background-position:center;
                width: 100%; height: 100%;'
            ));
            $html .= html_writer::end_div(); // End withoutimage inline style div.

        } else if ($courseimage && isset($headerbg) && !theme_fordson_get_setting('showcourseheaderimage')) {
            $html .= html_writer::start_div('customimage', array(
                'style' => 'background-image: url("' . $headerbgimgurl . '"); background-size: cover; background-position:center;
                width: 100%; height: 100%;'
            ));
            $html .= html_writer::end_div(); // End withoutimage inline style div.

        } else if (!$courseimage && isset($headerbg) && !theme_fordson_get_setting('showcourseheaderimage')) {
            $html .= html_writer::start_div('customimage', array(
                'style' => 'background-image: url("' . $headerbgimgurl . '"); background-size: cover; background-position:center;
                width: 100%; height: 100%;'
            ));
            $html .= html_writer::end_div(); // End withoutimage inline style div.

        } else {
            $html .= html_writer::start_div('default', array(
                'style' => 'background-image: url("' . $defaultimgurl . '"); background-size: cover; background-position:center;
                width: 100%; height: 100%;'
            ));
            $html .= html_writer::end_div(); // End default inline style div.

        }

        $html .= html_writer::end_div();

        return $html;

    }

    public function edit_button_fhs() {
        global $SITE, $PAGE, $USER, $CFG, $COURSE;
        if (!$PAGE->user_allowed_editing() || $COURSE->id <= 1) {
            return '';
        }
        if  ($PAGE->pagelayout == 'course') {
            $url = new moodle_url($PAGE->url);
            $url->param('sesskey', sesskey());
            if ($PAGE->user_is_editing()) {
                $url->param('edit', 'off');
                $btn = 'btn-danger editingbutton';
                $title = get_string('editoff', 'theme_fordson');
                $icon = 'fa-power-off';
            }
            else {
                $url->param('edit', 'on');
                $btn = 'btn-success editingbutton';
                $title = get_string('editon', 'theme_fordson');
                $icon = 'fa-edit';
            }
            return html_writer::tag('a', html_writer::start_tag('i', array(
                'class' => $icon . ' fa fa-fw'
            )) . html_writer::end_tag('i'), array(
                'href' => $url,
                'class' => 'btn edit-btn ' . $btn,
                'data-tooltip' => "tooltip",
                'data-placement'=> "bottom",
                'title' => $title,
            ));
            return $output;
        }
    }


    /**
     * Generates an array of sections and an array of activities for the given course.
     *
     * This method uses the cache to improve performance and avoid the get_fast_modinfo call
     *
     * @param stdClass $course
     * @return array Array($sections, $activities)
     */
    protected function generate_sections_and_activities(stdClass $course) {
        global $CFG;
        require_once($CFG->dirroot.'/course/lib.php');

        $modinfo = get_fast_modinfo($course);
        $sections = $modinfo->get_section_info_all();

        // For course formats using 'numsections' trim the sections list
        $courseformatoptions = course_get_format($course)->get_format_options();
        if (isset($courseformatoptions['numsections'])) {
            $sections = array_slice($sections, 0, $courseformatoptions['numsections']+1, true);
        }

        $activities = array();

        foreach ($sections as $key => $section) {
            // Clone and unset summary to prevent $SESSION bloat (MDL-31802).
            $sections[$key] = clone($section);
            unset($sections[$key]->summary);
            $sections[$key]->hasactivites = false;
            if (!array_key_exists($section->section, $modinfo->sections)) {
                continue;
            }
            foreach ($modinfo->sections[$section->section] as $cmid) {
                $cm = $modinfo->cms[$cmid];
                $activity = new stdClass;
                $activity->id = $cm->id;
                $activity->course = $course->id;
                $activity->section = $section->section;
                $activity->name = $cm->name;
                $activity->icon = $cm->icon;
                $activity->iconcomponent = $cm->iconcomponent;
                $activity->hidden = (!$cm->visible);
                $activity->modname = $cm->modname;
                $activity->nodetype = navigation_node::NODETYPE_LEAF;
                $activity->onclick = $cm->onclick;
                $url = $cm->url;
                if (!$url) {
                    $activity->url = null;
                    $activity->display = false;
                } else {
                    $activity->url = $url->out();
                    $activity->display = $cm->is_visible_on_course_page() ? true : false;
      //              if (self::module_extends_navigation($cm->modname)) {
      //                  $activity->nodetype = navigation_node::NODETYPE_BRANCH;
      //              }
                }
                $activities[$cmid] = $activity;
                if ($activity->display) {
                    $sections[$key]->hasactivites = true;
                }
            }
        }

        return array($sections, $activities);

    }

    /*
     * This renders the bootstrap top menu.
     *
     * This renderer is needed to enable the Bootstrap style navigation.
    */
    public function fordson_custom_menu() {
        global $CFG, $COURSE, $PAGE, $OUTPUT;
        $context = $this->page->context;

        $menu = new custom_menu();

        $hasdisplaymycourses = (empty($this->page->theme->settings->displaymycourses)) ? false : $this->page->theme->settings->displaymycourses;
        if (isloggedin() && !isguestuser() && $hasdisplaymycourses) {
            $mycoursetitle = $this->page->theme->settings->mycoursetitle;
            if ($mycoursetitle == 'module') {
                $branchtitle = get_string('mymodules', 'theme_fordson');
                $thisbranchtitle = get_string('thismymodules', 'theme_fordson');
                $homebranchtitle = get_string('homemymodules', 'theme_fordson');
            } else if ($mycoursetitle == 'unit') {
                $branchtitle = get_string('myunits', 'theme_fordson');
                $thisbranchtitle = get_string('thismyunits', 'theme_fordson');
                $homebranchtitle = get_string('homemyunits', 'theme_fordson');
            } else if ($mycoursetitle == 'class') {
                $branchtitle = get_string('myclasses', 'theme_fordson');
                $thisbranchtitle = get_string('thismyclasses', 'theme_fordson');
                $homebranchtitle = get_string('homemyclasses', 'theme_fordson');
            } else if ($mycoursetitle == 'training') {
                $branchtitle = get_string('mytraining', 'theme_fordson');
                $thisbranchtitle = get_string('thismytraining', 'theme_fordson');
                $homebranchtitle = get_string('homemytraining', 'theme_fordson');
            } else if ($mycoursetitle == 'pd') {
                $branchtitle = get_string('myprofessionaldevelopment', 'theme_fordson');
                $thisbranchtitle = get_string('thismyprofessionaldevelopment', 'theme_fordson');
                $homebranchtitle = get_string('homemyprofessionaldevelopment', 'theme_fordson');
            } else if ($mycoursetitle == 'cred') {
                $branchtitle = get_string('mycred', 'theme_fordson');
                $thisbranchtitle = get_string('thismycred', 'theme_fordson');
                $homebranchtitle = get_string('homemycred', 'theme_fordson');
            } else if ($mycoursetitle == 'plan') {
                $branchtitle = get_string('myplans', 'theme_fordson');
                $thisbranchtitle = get_string('thismyplans', 'theme_fordson');
                $homebranchtitle = get_string('homemyplans', 'theme_fordson');
            } else if ($mycoursetitle == 'comp') {
                $branchtitle = get_string('mycomp', 'theme_fordson');
                $thisbranchtitle = get_string('thismycomp', 'theme_fordson');
                $homebranchtitle = get_string('homemycomp', 'theme_fordson');
            } else if ($mycoursetitle == 'program') {
                $branchtitle = get_string('myprograms', 'theme_fordson');
                $thisbranchtitle = get_string('thismyprograms', 'theme_fordson');
                $homebranchtitle = get_string('homemyprograms', 'theme_fordson');
            } else if ($mycoursetitle == 'lecture') {
                $branchtitle = get_string('mylectures', 'theme_fordson');
                $thisbranchtitle = get_string('thismylectures', 'theme_fordson');
                $homebranchtitle = get_string('homemylectures', 'theme_fordson');
            } else if ($mycoursetitle == 'lesson') {
                $branchtitle = get_string('mylessons', 'theme_fordson');
                $thisbranchtitle = get_string('thismylessons', 'theme_fordson');
                $homebranchtitle = get_string('homemylessons', 'theme_fordson');
            } else {
                $branchtitle = get_string('mycourses', 'theme_fordson');
                $thisbranchtitle = get_string('thismycourses', 'theme_fordson');
                $homebranchtitle = get_string('homemycourses', 'theme_fordson');
            }
            $branchlabel = $branchtitle;
            $branchurl = new moodle_url('/my/index.php');
            $branchsort = 10000;

            $branch = $menu->add($branchlabel, $branchurl, $branchtitle, $branchsort);

            $dashlabel = get_string('mymoodle', 'my');
            $dashurl = new moodle_url("/my");
            $dashtitle = $dashlabel;
            $branch->add($dashlabel, $dashurl, $dashtitle);

            if ($courses = enrol_get_my_courses(NULL, 'fullname ASC')) {
                foreach ($courses as $course) {
                    if ($course->visible) {
                        $branch->add(format_string($course->fullname) , new moodle_url('/course/view.php?id=' . $course->id) , format_string($course->shortname));
                    }
                }
            } else {
                $noenrolments = get_string('noenrolments', 'theme_fordson');
                $branch->add('<em>' . $noenrolments . '</em>', new moodle_url('/') , $noenrolments);
            }

            $hasdisplaythiscourse = (empty($this->page->theme->settings->displaythiscourse)) ? false : $this->page->theme->settings->displaythiscourse;
            $sections = $this->generate_sections_and_activities($COURSE);
            if ($sections && $COURSE->id > 1 && $hasdisplaythiscourse) {

                $branchlabel = $thisbranchtitle;
                $branch = $menu->add($branchlabel, $branchurl, $branchtitle, $branchsort);
                $course = course_get_format($COURSE)->get_course();

                $coursehomelabel = $homebranchtitle;
                $coursehomeurl = new moodle_url('/course/view.php?', array('id' => $PAGE->course->id));
                $coursehometitle = $coursehomelabel;
                $branch->add($coursehomelabel, $coursehomeurl, $coursehometitle);

                $callabel = get_string('calendar', 'calendar');
                $calurl = new moodle_url('/calendar/view.php?view=month', array('course' => $PAGE->course->id));
                $caltitle = $callabel;
                $branch->add($callabel, $calurl, $caltitle);

                $participantlabel = get_string('participants', 'moodle');
                $participanturl = new moodle_url('/user/index.php', array('id' => $PAGE->course->id));
                $participanttitle = $participantlabel;
                $branch->add($participantlabel, $participanturl, $participanttitle);

                if($CFG->enablebadges == 1){
                    $badgelabel = get_string('badges', 'badges');
                    $badgeurl = new moodle_url('/badges/view.php?type=2', array('id' => $PAGE->course->id));
                    $badgetitle = $badgelabel;
                    $branch->add($badgelabel, $badgeurl, $badgetitle);
                }

                if (get_config('core_competency', 'enabled')) {
                $complabel = get_string('competencies', 'competency');
                $compurl = new moodle_url('/admin/tool/lp/coursecompetencies.php', array('courseid' => $PAGE->course->id));
                $comptitle = $complabel;
                $branch->add($complabel, $compurl, $comptitle);
                }

                foreach ($sections[0] as $sectionid => $section) {
                    $sectionname = get_section_name($COURSE, $section);
                    if ($course->coursedisplay == COURSE_DISPLAY_MULTIPAGE) {
                        $sectionurl = '/course/view.php?id=' . $COURSE->id . '&section=' . $sectionid;
                    }
                    else {
                        $sectionurl = '/course/view.php?id=' . $COURSE->id . '#section-' . $sectionid;
                    }
                    $branch->add(format_string($sectionname) , new moodle_url($sectionurl) , format_string($sectionname));
                }
            }

        }

        $content = '';
        foreach ($menu->get_children() as $item) {
            $context = $item->export_for_template($this);
            $content .= $this->render_from_template('core/custom_menu_item', $context);
        }

        return $content;
    }

    protected function render_courseactivities_menu(custom_menu $menu) {
        global $CFG;

        $content = '';
        foreach ($menu->get_children() as $item) {
            $context = $item->export_for_template($this);
            $content .= $this->render_from_template('theme_fordson/activitygroups', $context);
        }

        return $content;
    }

    public function courseactivities_menu() {
        global $PAGE, $COURSE, $OUTPUT, $CFG;
        $menu = new custom_menu();
        $context = $this->page->context;
        if (isset($COURSE->id) && $COURSE->id > 1) {
            $branchtitle = get_string('courseactivities', 'theme_fordson');
            $branchlabel = $branchtitle;
            $branchurl = new moodle_url('#');
            $branch = $menu->add($branchlabel, $branchurl, $branchtitle, 10002);

            $data = theme_fordson_get_course_activities();

            foreach ($data as $modname => $modfullname) {
                if ($modname === 'resources') {

                    $branch->add($modfullname, new moodle_url('/course/resources.php', array(
                        'id' => $PAGE->course->id
                    )));
                } else {

                    $branch->add($modfullname, new moodle_url('/mod/' . $modname . '/index.php', array(
                        'id' => $PAGE->course->id
                    )));
                }
            }

        }

        return $this->render_courseactivities_menu($menu);
    }

    public function social_icons() {
        global $PAGE;

        $hasfacebook = (empty($PAGE->theme->settings->facebook)) ? false : $PAGE->theme->settings->facebook;
        $hastwitter = (empty($PAGE->theme->settings->twitter)) ? false : $PAGE->theme->settings->twitter;
        $hasgoogleplus = (empty($PAGE->theme->settings->googleplus)) ? false : $PAGE->theme->settings->googleplus;
        $haslinkedin = (empty($PAGE->theme->settings->linkedin)) ? false : $PAGE->theme->settings->linkedin;
        $hasyoutube = (empty($PAGE->theme->settings->youtube)) ? false : $PAGE->theme->settings->youtube;
        $hasflickr = (empty($PAGE->theme->settings->flickr)) ? false : $PAGE->theme->settings->flickr;
        $hasvk = (empty($PAGE->theme->settings->vk)) ? false : $PAGE->theme->settings->vk;
        $haspinterest = (empty($PAGE->theme->settings->pinterest)) ? false : $PAGE->theme->settings->pinterest;
        $hasinstagram = (empty($PAGE->theme->settings->instagram)) ? false : $PAGE->theme->settings->instagram;
        $hasskype = (empty($PAGE->theme->settings->skype)) ? false : $PAGE->theme->settings->skype;
        $haswebsite = (empty($PAGE->theme->settings->website)) ? false : $PAGE->theme->settings->website;
        $hasblog = (empty($PAGE->theme->settings->blog)) ? false : $PAGE->theme->settings->blog;
        $hasvimeo = (empty($PAGE->theme->settings->vimeo)) ? false : $PAGE->theme->settings->vimeo;
        $hastumblr = (empty($PAGE->theme->settings->tumblr)) ? false : $PAGE->theme->settings->tumblr;
        $hassocial1 = (empty($PAGE->theme->settings->social1)) ? false : $PAGE->theme->settings->social1;
        $social1icon = (empty($PAGE->theme->settings->socialicon1)) ? 'globe' : $PAGE->theme->settings->socialicon1;
        $hassocial2 = (empty($PAGE->theme->settings->social2)) ? false : $PAGE->theme->settings->social2;
        $social2icon = (empty($PAGE->theme->settings->socialicon2)) ? 'globe' : $PAGE->theme->settings->socialicon2;
        $hassocial3 = (empty($PAGE->theme->settings->social3)) ? false : $PAGE->theme->settings->social3;
        $social3icon = (empty($PAGE->theme->settings->socialicon3)) ? 'globe' : $PAGE->theme->settings->socialicon3;

        $socialcontext = [

        // If any of the above social networks are true, sets this to true.
        'hassocialnetworks' => ($hasfacebook || $hastwitter || $hasgoogleplus || $hasflickr || $hasinstagram || $hasvk || $haslinkedin || $haspinterest || $hasskype || $haslinkedin || $haswebsite || $hasyoutube || $hasblog || $hasvimeo || $hastumblr || $hassocial1 || $hassocial2 || $hassocial3) ? true : false,

        'socialicons' => array(
            array(
                'haslink' => $hasfacebook,
                'linkicon' => 'facebook'
            ) ,
            array(
                'haslink' => $hastwitter,
                'linkicon' => 'twitter'
            ) ,
            array(
                'haslink' => $hasgoogleplus,
                'linkicon' => 'google-plus'
            ) ,
            array(
                'haslink' => $haslinkedin,
                'linkicon' => 'linkedin'
            ) ,
            array(
                'haslink' => $hasyoutube,
                'linkicon' => 'youtube'
            ) ,
            array(
                'haslink' => $hasflickr,
                'linkicon' => 'flickr'
            ) ,
            array(
                'haslink' => $hasvk,
                'linkicon' => 'vk'
            ) ,
            array(
                'haslink' => $haspinterest,
                'linkicon' => 'pinterest'
            ) ,
            array(
                'haslink' => $hasinstagram,
                'linkicon' => 'instagram'
            ) ,
            array(
                'haslink' => $hasskype,
                'linkicon' => 'skype'
            ) ,
            array(
                'haslink' => $haswebsite,
                'linkicon' => 'globe'
            ) ,
            array(
                'haslink' => $hasblog,
                'linkicon' => 'bookmark'
            ) ,
            array(
                'haslink' => $hasvimeo,
                'linkicon' => 'vimeo-square'
            ) ,
            array(
                'haslink' => $hastumblr,
                'linkicon' => 'tumblr'
            ) ,
            array(
                'haslink' => $hassocial1,
                'linkicon' => $social1icon
            ) ,
            array(
                'haslink' => $hassocial2,
                'linkicon' => $social2icon
            ) ,
            array(
                'haslink' => $hassocial3,
                'linkicon' => $social3icon
            ) ,
        ) ];

        return $this->render_from_template('theme_fordson/socialicons', $socialcontext);

    }

    public function fp_wonderbox() {
        global $PAGE;

        $context = $this->page->context;

        $hascreateicon = (empty($PAGE->theme->settings->createicon && isloggedin() && has_capability('moodle/course:create', $context))) ? false : $PAGE->theme->settings->createicon;
        $createbuttonurl = (empty($PAGE->theme->settings->createbuttonurl)) ? false : $PAGE->theme->settings->createbuttonurl;
        $createbuttontext = (empty($PAGE->theme->settings->createbuttontext)) ? false : $PAGE->theme->settings->createbuttontext;

        $hasslideicon = (empty($PAGE->theme->settings->slideicon && isloggedin() && !isguestuser())) ? false : $PAGE->theme->settings->slideicon;
        $slideiconbuttonurl = 'data-toggle="collapse" data-target="#collapseExample';
        $slideiconbuttontext = (empty($PAGE->theme->settings->slideiconbuttontext)) ? false : $PAGE->theme->settings->slideiconbuttontext;

        $hasnav1icon = (empty($PAGE->theme->settings->nav1icon && isloggedin() && !isguestuser())) ? false : $PAGE->theme->settings->nav1icon;
        $hasnav2icon = (empty($PAGE->theme->settings->nav2icon && isloggedin() && !isguestuser())) ? false : $PAGE->theme->settings->nav2icon;
        $hasnav3icon = (empty($PAGE->theme->settings->nav3icon && isloggedin() && !isguestuser())) ? false : $PAGE->theme->settings->nav3icon;
        $hasnav4icon = (empty($PAGE->theme->settings->nav4icon && isloggedin() && !isguestuser())) ? false : $PAGE->theme->settings->nav4icon;
        $hasnav5icon = (empty($PAGE->theme->settings->nav5icon && isloggedin() && !isguestuser())) ? false : $PAGE->theme->settings->nav5icon;
        $hasnav6icon = (empty($PAGE->theme->settings->nav6icon && isloggedin() && !isguestuser())) ? false : $PAGE->theme->settings->nav6icon;
        $hasnav7icon = (empty($PAGE->theme->settings->nav7icon && isloggedin() && !isguestuser())) ? false : $PAGE->theme->settings->nav7icon;
        $hasnav8icon = (empty($PAGE->theme->settings->nav8icon && isloggedin() && !isguestuser())) ? false : $PAGE->theme->settings->nav8icon;

        $nav1buttonurl = (empty($PAGE->theme->settings->nav1buttonurl)) ? false : $PAGE->theme->settings->nav1buttonurl;
        $nav2buttonurl = (empty($PAGE->theme->settings->nav2buttonurl)) ? false : $PAGE->theme->settings->nav2buttonurl;
        $nav3buttonurl = (empty($PAGE->theme->settings->nav3buttonurl)) ? false : $PAGE->theme->settings->nav3buttonurl;
        $nav4buttonurl = (empty($PAGE->theme->settings->nav4buttonurl)) ? false : $PAGE->theme->settings->nav4buttonurl;
        $nav5buttonurl = (empty($PAGE->theme->settings->nav5buttonurl)) ? false : $PAGE->theme->settings->nav5buttonurl;
        $nav6buttonurl = (empty($PAGE->theme->settings->nav6buttonurl)) ? false : $PAGE->theme->settings->nav6buttonurl;
        $nav7buttonurl = (empty($PAGE->theme->settings->nav7buttonurl)) ? false : $PAGE->theme->settings->nav7buttonurl;
        $nav8buttonurl = (empty($PAGE->theme->settings->nav8buttonurl)) ? false : $PAGE->theme->settings->nav8buttonurl;

        $nav1buttontext = (empty($PAGE->theme->settings->nav1buttontext)) ? false : format_text($PAGE->theme->settings->nav1buttontext);
        $nav2buttontext = (empty($PAGE->theme->settings->nav2buttontext)) ? false : format_text($PAGE->theme->settings->nav2buttontext);
        $nav3buttontext = (empty($PAGE->theme->settings->nav3buttontext)) ? false : format_text($PAGE->theme->settings->nav3buttontext);
        $nav4buttontext = (empty($PAGE->theme->settings->nav4buttontext)) ? false : format_text($PAGE->theme->settings->nav4buttontext);
        $nav5buttontext = (empty($PAGE->theme->settings->nav5buttontext)) ? false : format_text($PAGE->theme->settings->nav5buttontext);
        $nav6buttontext = (empty($PAGE->theme->settings->nav6buttontext)) ? false : format_text($PAGE->theme->settings->nav6buttontext);
        $nav7buttontext = (empty($PAGE->theme->settings->nav7buttontext)) ? false : format_text($PAGE->theme->settings->nav7buttontext);
        $nav8buttontext = (empty($PAGE->theme->settings->nav8buttontext)) ? false : format_text($PAGE->theme->settings->nav8buttontext);

        $fptextbox = (empty($PAGE->theme->settings->fptextbox && isloggedin())) ? false : format_text($PAGE->theme->settings->fptextbox);
        $fptextboxlogout = (empty($PAGE->theme->settings->fptextboxlogout && !isloggedin())) ? false : format_text($PAGE->theme->settings->fptextboxlogout);
        $slidetextbox = (empty($PAGE->theme->settings->slidetextbox && isloggedin())) ? false : format_text($PAGE->theme->settings->slidetextbox);
        $alertbox = (empty($PAGE->theme->settings->alertbox)) ? false : format_text($PAGE->theme->settings->alertbox);

        $hasmarketing1 = (empty($PAGE->theme->settings->marketing1 && $PAGE->theme->settings->togglemarketing == 1)) ? false : format_text($PAGE->theme->settings->marketing1);
        $marketing1content = (empty($PAGE->theme->settings->marketing1content)) ? false : format_text($PAGE->theme->settings->marketing1content);
        $marketing1buttontext = (empty($PAGE->theme->settings->marketing1buttontext)) ? false : format_text($PAGE->theme->settings->marketing1buttontext);
        $marketing1buttonurl = (empty($PAGE->theme->settings->marketing1buttonurl)) ? false : $PAGE->theme->settings->marketing1buttonurl;
        $marketing1target = (empty($PAGE->theme->settings->marketing1target)) ? false : $PAGE->theme->settings->marketing1target;
        $marketing1image = (empty($PAGE->theme->settings->marketing1image)) ? false : 'marketing1image';

        $hasmarketing2 = (empty($PAGE->theme->settings->marketing2 && $PAGE->theme->settings->togglemarketing == 1)) ? false : format_text($PAGE->theme->settings->marketing2);
        $marketing2content = (empty($PAGE->theme->settings->marketing2content)) ? false : format_text($PAGE->theme->settings->marketing2content);
        $marketing2buttontext = (empty($PAGE->theme->settings->marketing2buttontext)) ? false : format_text($PAGE->theme->settings->marketing2buttontext);
        $marketing2buttonurl = (empty($PAGE->theme->settings->marketing2buttonurl)) ? false : $PAGE->theme->settings->marketing2buttonurl;
        $marketing2target = (empty($PAGE->theme->settings->marketing2target)) ? false : $PAGE->theme->settings->marketing2target;
        $marketing2image = (empty($PAGE->theme->settings->marketing2image)) ? false : 'marketing2image';

        $hasmarketing3 = (empty($PAGE->theme->settings->marketing3 && $PAGE->theme->settings->togglemarketing == 1)) ? false : format_text($PAGE->theme->settings->marketing3);
        $marketing3content = (empty($PAGE->theme->settings->marketing3content)) ? false : format_text($PAGE->theme->settings->marketing3content);
        $marketing3buttontext = (empty($PAGE->theme->settings->marketing3buttontext)) ? false : format_text($PAGE->theme->settings->marketing3buttontext);
        $marketing3buttonurl = (empty($PAGE->theme->settings->marketing3buttonurl)) ? false : $PAGE->theme->settings->marketing3buttonurl;
        $marketing3target = (empty($PAGE->theme->settings->marketing3target)) ? false : $PAGE->theme->settings->marketing3target;
        $marketing3image = (empty($PAGE->theme->settings->marketing3image)) ? false : 'marketing3image';

        $hasmarketing4 = (empty($PAGE->theme->settings->marketing4 && $PAGE->theme->settings->togglemarketing == 1)) ? false : format_text($PAGE->theme->settings->marketing4);
        $marketing4content = (empty($PAGE->theme->settings->marketing4content)) ? false : format_text($PAGE->theme->settings->marketing4content);
        $marketing4buttontext = (empty($PAGE->theme->settings->marketing4buttontext)) ? false : format_text($PAGE->theme->settings->marketing4buttontext);
        $marketing4buttonurl = (empty($PAGE->theme->settings->marketing4buttonurl)) ? false : $PAGE->theme->settings->marketing4buttonurl;
        $marketing4target = (empty($PAGE->theme->settings->marketing4target)) ? false : $PAGE->theme->settings->marketing4target;
        $marketing4image = (empty($PAGE->theme->settings->marketing4image)) ? false : 'marketing4image';

        $hasmarketing5 = (empty($PAGE->theme->settings->marketing5 && $PAGE->theme->settings->togglemarketing == 1)) ? false : format_text($PAGE->theme->settings->marketing5);
        $marketing5content = (empty($PAGE->theme->settings->marketing5content)) ? false : format_text($PAGE->theme->settings->marketing5content);
        $marketing5buttontext = (empty($PAGE->theme->settings->marketing5buttontext)) ? false : format_text($PAGE->theme->settings->marketing5buttontext);
        $marketing5buttonurl = (empty($PAGE->theme->settings->marketing5buttonurl)) ? false : $PAGE->theme->settings->marketing5buttonurl;
        $marketing5target = (empty($PAGE->theme->settings->marketing5target)) ? false : $PAGE->theme->settings->marketing5target;
        $marketing5image = (empty($PAGE->theme->settings->marketing5image)) ? false : 'marketing5image';

        $hasmarketing6 = (empty($PAGE->theme->settings->marketing6 && $PAGE->theme->settings->togglemarketing == 1)) ? false : format_text($PAGE->theme->settings->marketing6);
        $marketing6content = (empty($PAGE->theme->settings->marketing6content)) ? false : format_text($PAGE->theme->settings->marketing6content);
        $marketing6buttontext = (empty($PAGE->theme->settings->marketing6buttontext)) ? false : format_text($PAGE->theme->settings->marketing6buttontext);
        $marketing6buttonurl = (empty($PAGE->theme->settings->marketing6buttonurl)) ? false : $PAGE->theme->settings->marketing6buttonurl;
        $marketing6target = (empty($PAGE->theme->settings->marketing6target)) ? false : $PAGE->theme->settings->marketing6target;
        $marketing6image = (empty($PAGE->theme->settings->marketing6image)) ? false : 'marketing6image';

        $fp_wonderboxcontext = [

        'hasfptextbox' => (!empty($PAGE->theme->settings->fptextbox && isloggedin())) , 'fptextbox' => $fptextbox,

        'hasslidetextbox' => (!empty($PAGE->theme->settings->slidetextbox && isloggedin())) , 'slidetextbox' => $slidetextbox,

        'hasfptextboxlogout' => !isloggedin() , 'fptextboxlogout' => $fptextboxlogout, 'hasshowloginform' => $PAGE->theme->settings->showloginform,

        'hasalert' => (!empty($PAGE->theme->settings->alertbox && isloggedin())) , 'alertbox' => $alertbox,

        'hasmarkettiles' => ($hasmarketing1 || $hasmarketing2 || $hasmarketing3 || $hasmarketing4 || $hasmarketing5 || $hasmarketing6) ? true : false, 'markettiles' => array(
            array(
                'hastile' => $hasmarketing1,
                'tileimage' => $marketing1image,
                'content' => $marketing1content,
                'title' => $hasmarketing1,
                'button' => "<a href = '$marketing1buttonurl' title = '$marketing1buttontext' alt='$marketing1buttontext' class='btn btn-primary' target='$marketing1target'> $marketing1buttontext </a>"
            ) ,
            array(
                'hastile' => $hasmarketing2,
                'tileimage' => $marketing2image,
                'content' => $marketing2content,
                'title' => $hasmarketing2,
                'button' => "<a href = '$marketing2buttonurl' title = '$marketing2buttontext' alt='$marketing2buttontext' class='btn btn-primary' target='$marketing2target'> $marketing2buttontext </a>"
            ) ,
            array(
                'hastile' => $hasmarketing3,
                'tileimage' => $marketing3image,
                'content' => $marketing3content,
                'title' => $hasmarketing3,
                'button' => "<a href = '$marketing3buttonurl' title = '$marketing3buttontext' alt='$marketing3buttontext' class='btn btn-primary' target='$marketing3target'> $marketing3buttontext </a>"
            ) ,
            array(
                'hastile' => $hasmarketing4,
                'tileimage' => $marketing4image,
                'content' => $marketing4content,
                'title' => $hasmarketing4,
                'button' => "<a href = '$marketing4buttonurl' title = '$marketing4buttontext' alt='$marketing4buttontext' class='btn btn-primary' target='$marketing4target'> $marketing4buttontext </a>"
            ) ,
            array(
                'hastile' => $hasmarketing5,
                'tileimage' => $marketing5image,
                'content' => $marketing5content,
                'title' => $hasmarketing5,
                'button' => "<a href = '$marketing5buttonurl' title = '$marketing5buttontext' alt='$marketing5buttontext' class='btn btn-primary' target='$marketing5target'> $marketing5buttontext </a>"
            ) ,
            array(
                'hastile' => $hasmarketing6,
                'tileimage' => $marketing6image,
                'content' => $marketing6content,
                'title' => $hasmarketing6,
                'button' => "<a href = '$marketing6buttonurl' title = '$marketing6buttontext' alt='$marketing6buttontext' class='btn btn-primary' target='$marketing6target'> $marketing6buttontext </a>"
            ) ,
        ) ,

        // If any of the above social networks are true, sets this to true.
        'hasfpiconnav' => ($hasnav1icon || $hasnav2icon || $hasnav3icon || $hasnav4icon || $hasnav5icon || $hasnav6icon || $hasnav7icon || $hasnav8icon || $hascreateicon || $hasslideicon) ? true : false,
        'fpiconnav' => array(
            array(
                'hasicon' => $hasnav1icon,
                'linkicon' => $hasnav1icon,
                'link' => $nav1buttonurl,
                'linktext' => $nav1buttontext
            ) ,
            array(
                'hasicon' => $hasnav2icon,
                'linkicon' => $hasnav2icon,
                'link' => $nav2buttonurl,
                'linktext' => $nav2buttontext
            ) ,
            array(
                'hasicon' => $hasnav3icon,
                'linkicon' => $hasnav3icon,
                'link' => $nav3buttonurl,
                'linktext' => $nav3buttontext
            ) ,
            array(
                'hasicon' => $hasnav4icon,
                'linkicon' => $hasnav4icon,
                'link' => $nav4buttonurl,
                'linktext' => $nav4buttontext
            ) ,
            array(
                'hasicon' => $hasnav5icon,
                'linkicon' => $hasnav5icon,
                'link' => $nav5buttonurl,
                'linktext' => $nav5buttontext
            ) ,
            array(
                'hasicon' => $hasnav6icon,
                'linkicon' => $hasnav6icon,
                'link' => $nav6buttonurl,
                'linktext' => $nav6buttontext
            ) ,
            array(
                'hasicon' => $hasnav7icon,
                'linkicon' => $hasnav7icon,
                'link' => $nav7buttonurl,
                'linktext' => $nav7buttontext
            ) ,
            array(
                'hasicon' => $hasnav8icon,
                'linkicon' => $hasnav8icon,
                'link' => $nav8buttonurl,
                'linktext' => $nav8buttontext
            ) ,
        ) , 'fpcreateicon' => array(
            array(
                'hasicon' => $hascreateicon,
                'linkicon' => $hascreateicon,
                'link' => $createbuttonurl,
                'linktext' => $createbuttontext
            ) ,
        ) , 'fpslideicon' => array(
            array(
                'hasicon' => $hasslideicon,
                'linkicon' => $hasslideicon,
                'link' => $slideiconbuttonurl,
                'linktext' => $slideiconbuttontext
            ) ,
        ) ,

        ];

        return $this->render_from_template('theme_fordson/fpwonderbox', $fp_wonderboxcontext);

    }

    public function customlogin() {
        global $PAGE;

        $hasloginnav1icon = (empty($PAGE->theme->settings->loginnav1icon)) ? false : $PAGE->theme->settings->loginnav1icon;
        $hasloginnav2icon = (empty($PAGE->theme->settings->loginnav2icon)) ? false : $PAGE->theme->settings->loginnav2icon;
        $hasloginnav3icon = (empty($PAGE->theme->settings->loginnav3icon)) ? false : $PAGE->theme->settings->loginnav3icon;
        $hasloginnav4icon = (empty($PAGE->theme->settings->loginnav4icon)) ? false : $PAGE->theme->settings->loginnav4icon;

        $loginnav1titletext = (empty($PAGE->theme->settings->loginnav1titletext)) ? false : $PAGE->theme->settings->loginnav1titletext;
        $loginnav2titletext = (empty($PAGE->theme->settings->loginnav2titletext)) ? false : $PAGE->theme->settings->loginnav2titletext;
        $loginnav3titletext = (empty($PAGE->theme->settings->loginnav3titletext)) ? false : $PAGE->theme->settings->loginnav3titletext;
        $loginnav4titletext = (empty($PAGE->theme->settings->loginnav4titletext)) ? false : $PAGE->theme->settings->loginnav4titletext;

        $loginnav1icontext = (empty($PAGE->theme->settings->loginnav1icontext)) ? false : format_text($PAGE->theme->settings->loginnav1icontext);
        $loginnav2icontext = (empty($PAGE->theme->settings->loginnav2icontext)) ? false : format_text($PAGE->theme->settings->loginnav2icontext);
        $loginnav3icontext = (empty($PAGE->theme->settings->loginnav3icontext)) ? false : format_text($PAGE->theme->settings->loginnav3icontext);
        $loginnav4icontext = (empty($PAGE->theme->settings->loginnav4icontext)) ? false : format_text($PAGE->theme->settings->loginnav4icontext);
        $hascustomlogin = $PAGE->theme->settings->showcustomlogin == 1;
        $hasdefaultlogin = $PAGE->theme->settings->showcustomlogin == 0;

        $customlogin_context = [

        'hascustomlogin' => $hascustomlogin,
        'hasdefaultlogin' => $hasdefaultlogin,

        'hasfeature1' => !empty($PAGE->theme->setting_file_url('feature1image', 'feature1image')) && !empty($PAGE->theme->settings->feature1text),
        'hasfeature2' => !empty($PAGE->theme->setting_file_url('feature2image', 'feature2image')) && !empty($PAGE->theme->settings->feature2text),
        'hasfeature3' => !empty($PAGE->theme->setting_file_url('feature3image', 'feature3image')) && !empty($PAGE->theme->settings->feature3text),
        'feature1image' => $PAGE->theme->setting_file_url('feature1image', 'feature1image'),
        'feature2image' => $PAGE->theme->setting_file_url('feature2image', 'feature2image'),
        'feature3image' => $PAGE->theme->setting_file_url('feature3image', 'feature3image'),
        'feature1text' => (empty($PAGE->theme->settings->feature1text)) ? false : format_text($PAGE->theme->settings->feature1text),
        'feature2text' => (empty($PAGE->theme->settings->feature2text)) ? false : format_text($PAGE->theme->settings->feature2text),
        'feature3text' => (empty($PAGE->theme->settings->feature3text)) ? false : format_text($PAGE->theme->settings->feature3text),

        // If any of the above social networks are true, sets this to true.
        'hasfpiconnav' => ($hasloginnav1icon || $hasloginnav2icon || $hasloginnav3icon || $hasloginnav4icon) ? true : false,
        'fpiconnav' => array(
            array(
                'hasicon' => $hasloginnav1icon,
                'icon' => $hasloginnav1icon,
                'title' => $loginnav1titletext,
                'text' => $loginnav1icontext
            ) ,
            array(
                'hasicon' => $hasloginnav2icon,
                'icon' => $hasloginnav2icon,
                'title' => $loginnav2titletext,
                'text' => $loginnav2icontext
            ) ,
            array(
                'hasicon' => $hasloginnav3icon,
                'icon' => $hasloginnav3icon,
                'title' => $loginnav3titletext,
                'text' => $loginnav3icontext
            ) ,
            array(
                'hasicon' => $hasloginnav4icon,
                'icon' => $hasloginnav4icon,
                'title' => $loginnav4titletext,
                'text' => $loginnav4icontext
            ) ,
        ) ,

        ];

        return $this->render_from_template('theme_fordson/customlogin', $customlogin_context);

    }

    public function fp_marketingtiles() {
        global $PAGE;

        $hasmarketing1 = (empty($PAGE->theme->settings->marketing1 && $PAGE->theme->settings->togglemarketing == 2)) ? false : format_text($PAGE->theme->settings->marketing1);
        $marketing1content = (empty($PAGE->theme->settings->marketing1content)) ? false : format_text($PAGE->theme->settings->marketing1content);
        $marketing1buttontext = (empty($PAGE->theme->settings->marketing1buttontext)) ? false : format_text($PAGE->theme->settings->marketing1buttontext);
        $marketing1buttonurl = (empty($PAGE->theme->settings->marketing1buttonurl)) ? false : $PAGE->theme->settings->marketing1buttonurl;
        $marketing1target = (empty($PAGE->theme->settings->marketing1target)) ? false : $PAGE->theme->settings->marketing1target;
        $marketing1image = (empty($PAGE->theme->settings->marketing1image)) ? false : 'marketing1image';

        $hasmarketing2 = (empty($PAGE->theme->settings->marketing2 && $PAGE->theme->settings->togglemarketing == 2)) ? false : format_text($PAGE->theme->settings->marketing2);
        $marketing2content = (empty($PAGE->theme->settings->marketing2content)) ? false : format_text($PAGE->theme->settings->marketing2content);
        $marketing2buttontext = (empty($PAGE->theme->settings->marketing2buttontext)) ? false : format_text($PAGE->theme->settings->marketing2buttontext);
        $marketing2buttonurl = (empty($PAGE->theme->settings->marketing2buttonurl)) ? false : $PAGE->theme->settings->marketing2buttonurl;
        $marketing2target = (empty($PAGE->theme->settings->marketing2target)) ? false : $PAGE->theme->settings->marketing2target;
        $marketing2image = (empty($PAGE->theme->settings->marketing2image)) ? false : 'marketing2image';

        $hasmarketing3 = (empty($PAGE->theme->settings->marketing3 && $PAGE->theme->settings->togglemarketing == 2)) ? false : format_text($PAGE->theme->settings->marketing3);
        $marketing3content = (empty($PAGE->theme->settings->marketing3content)) ? false : format_text($PAGE->theme->settings->marketing3content);
        $marketing3buttontext = (empty($PAGE->theme->settings->marketing3buttontext)) ? false : format_text($PAGE->theme->settings->marketing3buttontext);
        $marketing3buttonurl = (empty($PAGE->theme->settings->marketing3buttonurl)) ? false : $PAGE->theme->settings->marketing3buttonurl;
        $marketing3target = (empty($PAGE->theme->settings->marketing3target)) ? false : $PAGE->theme->settings->marketing3target;
        $marketing3image = (empty($PAGE->theme->settings->marketing3image)) ? false : 'marketing3image';

        $hasmarketing4 = (empty($PAGE->theme->settings->marketing4 && $PAGE->theme->settings->togglemarketing == 2)) ? false : format_text($PAGE->theme->settings->marketing4);
        $marketing4content = (empty($PAGE->theme->settings->marketing4content)) ? false : format_text($PAGE->theme->settings->marketing4content);
        $marketing4buttontext = (empty($PAGE->theme->settings->marketing4buttontext)) ? false : format_text($PAGE->theme->settings->marketing4buttontext);
        $marketing4buttonurl = (empty($PAGE->theme->settings->marketing4buttonurl)) ? false : $PAGE->theme->settings->marketing4buttonurl;
        $marketing4target = (empty($PAGE->theme->settings->marketing4target)) ? false : $PAGE->theme->settings->marketing4target;
        $marketing4image = (empty($PAGE->theme->settings->marketing4image)) ? false : 'marketing4image';

        $hasmarketing5 = (empty($PAGE->theme->settings->marketing5 && $PAGE->theme->settings->togglemarketing == 2)) ? false : format_text($PAGE->theme->settings->marketing5);
        $marketing5content = (empty($PAGE->theme->settings->marketing5content)) ? false : format_text($PAGE->theme->settings->marketing5content);
        $marketing5buttontext = (empty($PAGE->theme->settings->marketing5buttontext)) ? false : format_text($PAGE->theme->settings->marketing5buttontext);
        $marketing5buttonurl = (empty($PAGE->theme->settings->marketing5buttonurl)) ? false : $PAGE->theme->settings->marketing5buttonurl;
        $marketing5target = (empty($PAGE->theme->settings->marketing5target)) ? false : $PAGE->theme->settings->marketing5target;
        $marketing5image = (empty($PAGE->theme->settings->marketing5image)) ? false : 'marketing5image';

        $hasmarketing6 = (empty($PAGE->theme->settings->marketing6 && $PAGE->theme->settings->togglemarketing == 2)) ? false : format_text($PAGE->theme->settings->marketing6);
        $marketing6content = (empty($PAGE->theme->settings->marketing6content)) ? false : format_text($PAGE->theme->settings->marketing6content);
        $marketing6buttontext = (empty($PAGE->theme->settings->marketing6buttontext)) ? false : format_text($PAGE->theme->settings->marketing6buttontext);
        $marketing6buttonurl = (empty($PAGE->theme->settings->marketing6buttonurl)) ? false : $PAGE->theme->settings->marketing6buttonurl;
        $marketing6target = (empty($PAGE->theme->settings->marketing6target)) ? false : $PAGE->theme->settings->marketing6target;
        $marketing6image = (empty($PAGE->theme->settings->marketing6image)) ? false : 'marketing6image';

        $fp_marketingtiles = [

        'hasmarkettiles' => ($hasmarketing1 || $hasmarketing2 || $hasmarketing3 || $hasmarketing4 || $hasmarketing5 || $hasmarketing6) ? true : false,

        'markettiles' => array(
            array(
                'hastile' => $hasmarketing1,
                'tileimage' => $marketing1image,
                'content' => $marketing1content,
                'title' => $hasmarketing1,
                'button' => "<a href = '$marketing1buttonurl' title = '$marketing1buttontext' alt='$marketing1buttontext' class='btn btn-primary' target='$marketing1target'> $marketing1buttontext </a>"
            ) ,
            array(
                'hastile' => $hasmarketing2,
                'tileimage' => $marketing2image,
                'content' => $marketing2content,
                'title' => $hasmarketing2,
                'button' => "<a href = '$marketing2buttonurl' title = '$marketing2buttontext' alt='$marketing2buttontext' class='btn btn-primary' target='$marketing2target'> $marketing2buttontext </a>"
            ) ,
            array(
                'hastile' => $hasmarketing3,
                'tileimage' => $marketing3image,
                'content' => $marketing3content,
                'title' => $hasmarketing3,
                'button' => "<a href = '$marketing3buttonurl' title = '$marketing3buttontext' alt='$marketing3buttontext' class='btn btn-primary' target='$marketing3target'> $marketing3buttontext </a>"
            ) ,
            array(
                'hastile' => $hasmarketing4,
                'tileimage' => $marketing4image,
                'content' => $marketing4content,
                'title' => $hasmarketing4,
                'button' => "<a href = '$marketing4buttonurl' title = '$marketing4buttontext' alt='$marketing4buttontext' class='btn btn-primary' target='$marketing4target'> $marketing4buttontext </a>"
            ) ,
            array(
                'hastile' => $hasmarketing5,
                'tileimage' => $marketing5image,
                'content' => $marketing5content,
                'title' => $hasmarketing5,
                'button' => "<a href = '$marketing5buttonurl' title = '$marketing5buttontext' alt='$marketing5buttontext' class='btn btn-primary' target='$marketing5target'> $marketing5buttontext </a>"
            ) ,
            array(
                'hastile' => $hasmarketing6,
                'tileimage' => $marketing6image,
                'content' => $marketing6content,
                'title' => $hasmarketing6,
                'button' => "<a href = '$marketing6buttonurl' title = '$marketing6buttontext' alt='$marketing6buttontext' class='btn btn-primary' target='$marketing6target'> $marketing6buttontext </a>"
            ) ,
        ) , ];

        return $this->render_from_template('theme_fordson/fpmarkettiles', $fp_marketingtiles);
    }

    public function fp_slideshow() {
        global $PAGE;

        $theme = theme_config::load('fordson');

        $slideshowon = $PAGE->theme->settings->showslideshow == 1;

        $hasslide1 = (empty($theme->setting_file_url('slide1image', 'slide1image'))) ? false : $theme->setting_file_url('slide1image', 'slide1image');
        $slide1 = (empty($PAGE->theme->settings->slide1title)) ? false : format_text($PAGE->theme->settings->slide1title);
        $slide1content = (empty($PAGE->theme->settings->slide1content)) ? false : format_text($PAGE->theme->settings->slide1content);
        $showtext1 = (empty($PAGE->theme->settings->slide1title)) ? false : format_text($PAGE->theme->settings->slide1title);

        $hasslide2 = (empty($theme->setting_file_url('slide2image', 'slide2image'))) ? false : $theme->setting_file_url('slide2image', 'slide2image');
        $slide2 = (empty($PAGE->theme->settings->slide2title)) ? false : format_text($PAGE->theme->settings->slide2title);
        $slide2content = (empty($PAGE->theme->settings->slide2content)) ? false : format_text($PAGE->theme->settings->slide2content);
        $showtext2 = (empty($PAGE->theme->settings->slide2title)) ? false : format_text($PAGE->theme->settings->slide2title);

        $hasslide3 = (empty($theme->setting_file_url('slide3image', 'slide3image'))) ? false : $theme->setting_file_url('slide3image', 'slide3image');
        $slide3 = (empty($PAGE->theme->settings->slide3title)) ? false : format_text($PAGE->theme->settings->slide3title);
        $slide3content = (empty($PAGE->theme->settings->slide3content)) ? false : format_text($PAGE->theme->settings->slide3content);
        $showtext3 = (empty($PAGE->theme->settings->slide3title)) ? false : format_text($PAGE->theme->settings->slide3title);

        $fp_slideshow = [

        'hasfpslideshow' => $slideshowon,

        'hasslide1' => $hasslide1 ? true : false, 'hasslide2' => $hasslide2 ? true : false, 'hasslide3' => $hasslide3 ? true : false,

        'showtext1' => $showtext1 ? true : false, 'showtext2' => $showtext2 ? true : false, 'showtext3' => $showtext3 ? true : false,

        'slide1' => array(
            'slidetitle' => $slide1,
            'slidecontent' => $slide1content
        ) , 'slide2' => array(
            'slidetitle' => $slide2,
            'slidecontent' => $slide2content
        ) , 'slide3' => array(
            'slidetitle' => $slide3,
            'slidecontent' => $slide3content
        ) ,

        ];

        return $this->render_from_template('theme_fordson/slideshow', $fp_slideshow);
    }

    public function teacherdashmenu() {
        global $PAGE, $COURSE, $CFG, $DB, $OUTPUT;
        $course = $this->page->course;
        $context = context_course::instance($course->id);

        // $showincourseonly = isset($COURSE->id) && $COURSE->id > 1 && $PAGE->theme->settings->coursemanagementtoggle && isloggedin() && !isguestuser();
        // $showincourseonly = isset($COURSE->id) && $COURSE->id > 1  && isloggedin() && !isguestuser();
        // moodle/course:update
        $showincourseonly = isset($COURSE->id) && $COURSE->id > 1  && has_capability('moodle/grade:viewall', $context);
        $haspermission = has_capability('enrol/category:config', $context) && isset($PAGE->theme->settings->coursemanagementtoggle) && isset($COURSE->id) && $COURSE->id > 1;

        $togglebutton = '';
        $togglebuttonstudent = '';
        $hasteacherdash = '';
        $hasstudentdash = '';
        $globalhaseasyenrollment = enrol_get_plugin('easy');
        $coursehaseasyenrollment = '';

        if ($globalhaseasyenrollment) {
            $coursehaseasyenrollment = $DB->record_exists('enrol', array(
                'courseid' => $COURSE->id,
                'enrol' => 'easy'
            ));
            $easyenrollinstance = $DB->get_record('enrol', array(
                'courseid' => $COURSE->id,
                'enrol' => 'easy'
            ));
        }

        if ($coursehaseasyenrollment && isset($COURSE->id) && $COURSE->id > 1) {
            $easycodetitle = get_string('header_coursecodes', 'enrol_easy');
            $easycodelink = new moodle_url('/enrol/editinstance.php', array(
                'courseid' => $PAGE->course->id,
                'id' => $easyenrollinstance->id,
                'type' => 'easy'
            ));
        }

        if (isloggedin() && ISSET($COURSE->id) && $COURSE->id > 1) {
            $course = $this->page->course;
            $context = context_course::instance($course->id);
            // Used to be : moodle/course:viewhiddenactivities , but Hanna changed it to:
            // moodle/grade:viewall (18-10-2018)
            $iamteacher = has_capability('moodle/grade:viewall', $context);
            $hasteacherdash = $iamteacher;
            $hasstudentdash = !$iamteacher;
            if ($iamteacher) {
                $togglebutton = get_string('coursemanagementbutton', 'theme_fordson');
            } else {
                $togglebuttonstudent = get_string('studentdashbutton', 'theme_fordson');
            }

            // show quiz settings button (show/hide blocks region) at header only for non-students and on specific pages
            // $onquizview = $PAGE->url->compare(new moodle_url('/mod/quiz/view.php'), URL_MATCH_BASE);
            $onquizattempt = $PAGE->url->compare(new moodle_url('/mod/quiz/attempt.php'), URL_MATCH_BASE);
            if ($hasteacherdash && $onquizattempt) {
                $quizsettingsbutton = true;
            }
        }

        $haseditcog = isset($PAGE->theme->settings->courseeditingcog) ? $PAGE->theme->settings->courseeditingcog : null;
        // $editcog = html_writer::div($this->context_header_settings_menu() , 'pull-xs-right context-header-settings-menu');

        $siteadmintitle = get_string('siteadminquicklink', 'theme_fordson');
        $siteadminurl = new moodle_url('/admin/search.php');

        $hasadminlink = is_siteadmin();

        $course = $this->page->course;

        // Send to template.
        $dashmenu = [
            'showincourseonly' => $showincourseonly,
            'togglebutton' => $togglebutton,
            'togglebuttonstudent' => $togglebuttonstudent,
            'hasteacherdash' => $hasteacherdash,
            'hasstudentdash' => $hasstudentdash,
            'haspermission' => $haspermission,
            'hasadminlink' => $hasadminlink,
            'siteadmintitle' => $siteadmintitle,
            'siteadminurl' => $siteadminurl,
            'haseditcog' => $haseditcog,
            'editcog' => isset($editcog) ? $editcog : null,
            'quizsettingsbutton' => isset($quizsettingsbutton) ? $quizsettingsbutton : null,
        ];

        // Attach easy enrollment links if active.
        if ($globalhaseasyenrollment && $coursehaseasyenrollment) {
            $dashmenu['dashmenu'][] = array(
                'haseasyenrollment' => $coursehaseasyenrollment,
                'title' => $easycodetitle,
                'url' => $easycodelink
            );

        }

        return $this->render_from_template('theme_stardust/teacherdashmenu', $dashmenu);
    }


    public function teacherdash() {
        global $PAGE, $COURSE, $CFG, $DB, $OUTPUT;

        require_once ($CFG->dirroot . '/completion/classes/progress.php');
        $togglebutton = '';
        $togglebuttonstudent = '';
        $hasteacherdash = '';
        $hasstudentdash = '';
        if (isloggedin() && ISSET($COURSE->id) && $COURSE->id > 1) {
            $course = $this->page->course;
            $context = context_course::instance($course->id);
            // Used to be : moodle/course:viewhiddenactivities , but Hanna changed it to:
            // moodle/grade:viewall (18-10-2018)
            $iamteacher = has_capability('moodle/grade:viewall', $context);
            $hasteacherdash = $iamteacher;
            $hasstudentdash = !$iamteacher;
            if ($iamteacher) {
                $togglebutton = get_string('coursemanagementbutton', 'theme_fordson');
            } else {
                $togglebuttonstudent = get_string('studentdashbutton', 'theme_fordson');
            }
        }
        $course = $this->page->course;
        $context = context_course::instance($course->id);
        $coursemanagementmessage = (empty($PAGE->theme->settings->coursemanagementtextbox)) ? false : format_text($PAGE->theme->settings->coursemanagementtextbox);

        $courseactivities = $this->courseactivities_menu();
        // $showincourseonly = isset($COURSE->id) && $COURSE->id > 1 && $PAGE->theme->settings->coursemanagementtoggle && isloggedin() && !isguestuser();
        $showincourseonly = isset($COURSE->id) && $COURSE->id > 1 && isloggedin() && !isguestuser();
        $globalhaseasyenrollment = enrol_get_plugin('easy');
        $coursehaseasyenrollment = '';
        if ($globalhaseasyenrollment) {
            $coursehaseasyenrollment = $DB->record_exists('enrol', array(
                'courseid' => $COURSE->id,
                'enrol' => 'easy'
            ));
            $easyenrollinstance = $DB->get_record('enrol', array(
                'courseid' => $COURSE->id,
                'enrol' => 'easy'
            ));
        }

        // Link catagories.
        $haspermission = has_capability('enrol/category:config', $context) && isset($PAGE->theme->settings->coursemanagementtoggle) && isset($COURSE->id) && $COURSE->id > 1;
        $userlinks = get_string('userlinks', 'theme_fordson');
        $userlinksdesc = get_string('userlinks_desc', 'theme_fordson');
        $qbank = get_string('qbank', 'theme_fordson');
        $qbankdesc = get_string('qbank_desc', 'theme_fordson');
        $badges = get_string('badges', 'theme_fordson');
        $badgesdesc = get_string('badges_desc', 'theme_fordson');
        $coursemanage = get_string('coursemanage', 'theme_fordson');
        $coursemanagedesc = get_string('coursemanage_desc', 'theme_fordson');
        $coursemanagementmessage = (empty($PAGE->theme->settings->coursemanagementtextbox)) ? false : format_text($PAGE->theme->settings->coursemanagementtextbox);
        $studentdashboardtextbox = (empty($PAGE->theme->settings->studentdashboardtextbox)) ? false : format_text($PAGE->theme->settings->studentdashboardtextbox);

        // User links.
        if ($coursehaseasyenrollment && isset($COURSE->id) && $COURSE->id > 1) {
            $easycodetitle = get_string('header_coursecodes', 'enrol_easy');
            $easycodelink = new moodle_url('/enrol/editinstance.php', array(
                'courseid' => $PAGE->course->id,
                'id' => $easyenrollinstance->id,
                'type' => 'easy'
            ));
        }
        $gradestitle = get_string('gradesoverview', 'gradereport_overview');
        $gradeslink = new moodle_url('/grade/report/index.php', array(
            'id' => $PAGE->course->id
        ));
        $participantstitle = (isset($PAGE->theme->settings->studentdashboardtextbox) && $PAGE->theme->settings->studentdashboardtextbox == 1) ? false : get_string('participants', 'moodle');
        $participantslink = new moodle_url('/user/index.php', array(
            'id' => $PAGE->course->id
        ));
        (empty($participantstitle)) ? false : get_string('participants', 'moodle');
        $activitycompletiontitle = get_string('activitycompletion', 'completion');
        $activitycompletionlink = new moodle_url('/report/progress/index.php', array(
            'course' => $PAGE->course->id
        ));
        $grouptitle = get_string('groups', 'group');
        $grouplink = new moodle_url('/group/index.php', array(
            'id' => $PAGE->course->id
        ));
        $enrolmethodtitle = get_string('enrolmentinstances', 'enrol');
        $enrolmethodlink = new moodle_url('/enrol/instances.php', array(
            'id' => $PAGE->course->id
        ));
        if (has_capability('moodle/course:manageactivities', $context)  ) {
            $extendeduserreporttitle = get_string('extendeduserreport', 'theme_stardust');
            $extendeduserreportlink = new moodle_url('/blocks/configurable_reports/viewreport.php?id=62&', array(
                'courseid' => $PAGE->course->id
            ));
        } elseif (!has_capability('moodle/course:manageactivities', $context) &&
            has_capability('moodle/grade:viewall', $context)) { // for groupteacherviewer hanna 20/1/16
            $extendeduserreporttitle = get_string('userspassreport', 'theme_stardust');
            $extendeduserreportlink = new moodle_url('/blocks/configurable_reports/viewreport.php?id=63&', array(
                'courseid' => $PAGE->course->id
            ));
        }
        // User reports.
        $logstitle = get_string('logs', 'moodle');
        $logslink = new moodle_url('/report/log/index.php', array(
            'id' => $PAGE->course->id
        ));
        $livelogstitle = get_string('loglive:view', 'report_loglive');
        $livelogslink = new moodle_url('/report/loglive/index.php', array(
            'id' => $PAGE->course->id
        ));
        $participationtitle = get_string('participation:view', 'report_participation');
        $participationlink = new moodle_url('/report/participation/index.php', array(
            'id' => $PAGE->course->id
        ));
        $activitytitle = get_string('outline:view', 'report_outline');
        $activitylink = new moodle_url('/report/outline/index.php', array(
            'id' => $PAGE->course->id
        ));
        $completionreporttitle = get_string('coursecompletion', 'completion');
        $completionreportlink = new moodle_url('/report/completion/index.php', array(
            'course' => $PAGE->course->id
        ));


        // Questionbank.
        $qbanktitle = get_string('questionbank', 'question');
        $qbanklink = new moodle_url('/question/edit.php', array(
            'courseid' => $PAGE->course->id
        ));
        $qcattitle = get_string('questioncategory', 'question');
        $qcatlink = new moodle_url('/question/category.php', array(
            'courseid' => $PAGE->course->id
        ));
        $qimporttitle = get_string('import', 'question');
        $qimportlink = new moodle_url('/question/import.php', array(
            'courseid' => $PAGE->course->id
        ));
        $qexporttitle = get_string('export', 'question');
        $qexportlink = new moodle_url('/question/export.php', array(
            'courseid' => $PAGE->course->id
        ));

        // Manage course.
        $coursecompletiontitle = get_string('coursecompletion', 'moodle');
        $coursecompletionlink = new moodle_url('/course/completion.php', array(
            'id' => $PAGE->course->id
        ));

        $competencytitle = get_string('competencies', 'competency');
        $competencyurl = new moodle_url('/admin/tool/lp/coursecompetencies.php', array('courseid' => $PAGE->course->id));

        // SG - T-276 - allow these links only for course admins, not for customized teachers
        if (has_capability('moodle/backup:backupcourse', $context)) {
            $courseadmintitle = get_string('courseadministration', 'moodle');
            $courseadminlink = new moodle_url('/course/admin.php', array(
                'courseid' => $PAGE->course->id
            ));
            $courseresettitle = get_string('reset', 'moodle');
            $courseresetlink = new moodle_url('/course/reset.php', array(
                'id' => $PAGE->course->id
            ));
            $coursebackuptitle = get_string('backup', 'moodle');
            $coursebackuplink = new moodle_url('/backup/backup.php', array(
                'id' => $PAGE->course->id
            ));
            $courserestoretitle = get_string('restore', 'moodle');
            $courserestorelink = new moodle_url('/backup/restorefile.php', array(
                'contextid' => $PAGE->context->id
            ));
        } else {
            $courseadmintitle = $courseadminlink = $courseresettitle = $courseresetlink = $coursebackuptitle = $coursebackuplink = $courserestoretitle = $courserestorelink = null;
        }

        $courseimporttitle = get_string('import', 'moodle');
        $courseimportlink = new moodle_url('/backup/import.php', array(
            'id' => $PAGE->course->id
        ));
        $courseedittitle = get_string('editcoursesettings', 'moodle');
        $courseeditlink = new moodle_url('/course/edit.php', array(
            'id' => $PAGE->course->id
        ));

        $badgemanagetitle = get_string('managebadges', 'badges');
        $badgemanagelink = new moodle_url('/badges/index.php?type=2', array(
            'id' => $PAGE->course->id
        ));
        $badgeaddtitle = get_string('newbadge', 'badges');
        $badgeaddlink = new moodle_url('/badges/newbadge.php?type=2', array(
            'id' => $PAGE->course->id
        ));

        $recyclebintitle = get_string('pluginname', 'tool_recyclebin');
        $recyclebinlink = new moodle_url('/admin/tool/recyclebin/index.php', array(
            'contextid' => $PAGE->context->id
        ));

        // SG - T-276 - allow these links only for course admins, not for customized teachers
        if (has_capability('moodle/backup:backupcourse', $context)) {
            $filtertitle = get_string('filtersettings', 'filters');
            $filterlink = new moodle_url('/filter/manage.php', array(
                'contextid' => $PAGE->context->id
            ));
            $eventmonitoringtitle = get_string('managesubscriptions', 'tool_monitor');
            $eventmonitoringlink = new moodle_url('/admin/tool/monitor/managerules.php', array(
                'courseid' => $PAGE->course->id
            ));
        } else {
            $filtertitle = $filterlink = $eventmonitoringtitle = $eventmonitoringlink = null;
        }


        // Student Dash.
        if (\core_completion\progress::get_course_progress_percentage($PAGE->course)) {
            $comppc = \core_completion\progress::get_course_progress_percentage($PAGE->course);
            $comppercent = number_format($comppc, 0);
            $hasprogress = true;
        } else {
            $comppercent = 0;
            $hasprogress = false;
        }
        $progresschartcontext = ['hasprogress' => $hasprogress, 'progress' => $comppercent];
        $progresschart = $this->render_from_template('block_myoverview/progress-chart', $progresschartcontext);
        $gradeslinkstudent = new moodle_url('/grade/report/user/index.php', array(
            'id' => $PAGE->course->id
        ));

        $hascourseinfogroup = array(
            'title' => get_string('courseinfo', 'theme_fordson') ,
            'icon' => 'map'
        );
        $summary = theme_fordson_strip_html_tags($COURSE->summary);
        $summarytrim = theme_fordson_course_trim_char($summary, 300);
        $courseinfo = array(
            array(
                //'content' => format_text($summarytrim) ,
            )
        );
        $hascoursestaff = array(
            'title' => get_string('coursestaff', 'theme_fordson') ,
            'icon' => 'users'
        );
        $courseteachers = array();
        $courseother = array();
        // If you created custom roles, please change the shortname value to match the name of your role.  This is teacher.
        $role = $DB->get_record('role', array(
            'shortname' => 'editingteacher'
        ));
        if ($role) {
            $context = context_course::instance($PAGE->course->id);
            $teachers = get_role_users($role->id, $context, false, 'u.id, u.firstname, u.middlename, u.lastname, u.alternatename,
                    u.firstnamephonetic, u.lastnamephonetic, u.email, u.picture,
                    u.imagealt');

            foreach ($teachers as $staff) {
                $picture = $OUTPUT->user_picture($staff, array(
                    'size' => 50
                ));
                $messaging = new moodle_url('/message/index.php', array(
                    'id' => $staff->id
                ));
                $hasmessaging = $CFG->messaging == 1;
                $courseteachers[] = array(
                    'name' => $staff->firstname . ' ' . $staff->lastname . ' ' . $staff->alternatename,
                    'email' => $staff->email,
                    'picture' => $picture,
                    'messaging' => $messaging,
                    'hasmessaging' => $hasmessaging
                );
            }
        }
        // If you created custom roles, please change the shortname value to match the name of your role.  This is non-editing teacher.
        $role = $DB->get_record('role', array(
            'shortname' => 'teacher'
        ));
        if ($role) {
            $context = context_course::instance($PAGE->course->id);
            $teachers = get_role_users($role->id, $context, false, 'u.id, u.firstname, u.middlename, u.lastname, u.alternatename,
                    u.firstnamephonetic, u.lastnamephonetic, u.email, u.picture,
                    u.imagealt');
            foreach ($teachers as $staff) {
                $picture = $OUTPUT->user_picture($staff, array(
                    'size' => 50
                ));
                $messaging = new moodle_url('/message/index.php', array(
                    'id' => $staff->id
                ));
                $hasmessaging = $CFG->messaging == 1;
                $courseother[] = array(
                    'name' => $staff->firstname . ' ' . $staff->lastname,
                    'email' => $staff->email,
                    'picture' => $picture,
                    'messaging' => $messaging,
                    'hasmessaging' => $hasmessaging
                );
            }
        }
        $activitylinkstitle = get_string('activitylinkstitle', 'theme_fordson');
        $activitylinkstitle_desc = get_string('activitylinkstitle_desc', 'theme_fordson');
        $mygradestext = get_string('mygradestext', 'theme_fordson');
        $myprogresstext = get_string('myprogresstext', 'theme_fordson');
        $studentcoursemanage = get_string('courseadministration', 'moodle');

        // Permissionchecks for teacher access.
        $hasquestionpermission = has_capability('moodle/question:add', $context);
        $hasbadgepermission = has_capability('moodle/badges:awardbadge', $context);
        $hascoursepermission = has_capability('moodle/course:update', $context);
        $hasbackuppermission = has_capability('moodle/backup:backupcourse', $context);
        $hasuserpermission = has_capability('moodle/course:viewhiddenactivities', $context);
        $isteacherviewer = has_capability('moodle/grade:viewall', $context) && !$hasuserpermission;
        $hasgradebookshow = (isset($PAGE->course->showgrades) && $PAGE->course->showgrades == 1) && (isset($PAGE->theme->settings->showstudentgrades) && $PAGE->theme->settings->showstudentgrades == 1);
        $hascompletionshow = (isset($PAGE->course->enablecompletion) && $PAGE->course->enablecompletion == 1) && (isset($PAGE->theme->settings->showstudentcompletion) && $PAGE->theme->settings->showstudentcompletion == 1);
        $hascourseadminshow = isset($PAGE->theme->settings->showcourseadminstudents) && $PAGE->theme->settings->showcourseadminstudents == 1;
        $hascompetency = get_config('core_competency', 'enabled');

        // Send to template.
        $dashlinks = [
            'showincourseonly' => $showincourseonly,
            'haspermission' => $haspermission,
            'courseactivities' => $courseactivities,
            'togglebutton' => $togglebutton,
            'togglebuttonstudent' => $togglebuttonstudent,
            'userlinkstitle' => $userlinks,
            'userlinksdesc' => $userlinksdesc,
            'qbanktitle' => $qbank,
            'activitylinkstitle' => $activitylinkstitle,
            'activitylinkstitle_desc' => $activitylinkstitle_desc,
            'qbankdesc' => $qbankdesc,
            'badgestitle' => $badges,
            'badgesdesc' => $badgesdesc,
            'coursemanagetitle' => $coursemanage,
            'coursemanagedesc' => $coursemanagedesc,
            'coursemanagementmessage' => $coursemanagementmessage,
            'progresschart' => $progresschart,
            'gradeslink' => $gradeslink,
            'gradeslinkstudent' => $gradeslinkstudent,
            'hascourseinfogroup' => $hascourseinfogroup,
            'courseinfo' => $courseinfo,
            'hascoursestaffgroup' => $hascoursestaff,
            'courseteachers' => $courseteachers,
            'courseother' => $courseother,
            'myprogresstext' => $myprogresstext,
            'mygradestext' => $mygradestext,
            'studentdashboardtextbox' => $studentdashboardtextbox,
            'hasteacherdash' => $hasteacherdash,
            'teacherdash' => array('hasquestionpermission' => $hasquestionpermission,
                'hasbadgepermission' => $hasbadgepermission,
                'hascoursepermission' => $hascoursepermission,
                'isteacherviewer' => $isteacherviewer,
                'hasuserpermission' => $hasuserpermission),
            'hasstudentdash' => $hasstudentdash,
            'hasgradebookshow' => $hasgradebookshow,
            'hascompletionshow' => $hascompletionshow,
            'studentcourseadminlink' => isset($courseadminlink) ? $courseadminlink : null,
            'studentcoursemanage' => $studentcoursemanage,
            'hascourseadminshow' => $hascourseadminshow,
            'hascompetency' => $hascompetency,
            'competencytitle' => $competencytitle,
            'competencyurl' => $competencyurl,

        'dashlinks' => array(
            array(
                'hasuserlinks' => $gradestitle,
                'title' => $gradestitle,
                'url' => $gradeslink
            ) ,
            array(
                'hasuserlinks' => $participantstitle,
                'title' => $participantstitle,
                'url' => $participantslink
            ) ,
            array(
                'hasuserlinks' => $grouptitle,
                'title' => $grouptitle,
                'url' => $grouplink
            ) ,
            array(
                'hasuserlinks' => $enrolmethodtitle,
                'title' => $enrolmethodtitle,
                'url' => $enrolmethodlink
            ) ,
            array(
                'hasuserlinks' => $activitycompletiontitle,
                'title' => $activitycompletiontitle,
                'url' => $activitycompletionlink
            ) ,
            array(
                'hasuserlinks' => $completionreporttitle,
                'title' => $completionreporttitle,
                'url' => $completionreportlink
            ) ,
            array(
                'hasuserlinks' => $logstitle,
                'title' => $logstitle,
                'url' => $logslink
            ) ,
            array(
                'hasuserlinks' => $livelogstitle,
                'title' => $livelogstitle,
                'url' => $livelogslink
            ) ,
            array(
                'hasuserlinks' => $participationtitle,
                'title' => $participationtitle,
                'url' => $participationlink
            ) ,
            array(
                'hasuserlinks' => $activitytitle,
                'title' => $activitytitle,
                'url' => $activitylink
            ) ,
            array(
                'hasuserlinks' => isset($extendeduserreporttitle) ? $extendeduserreporttitle : null,
                'title' => isset($extendeduserreporttitle) ? $extendeduserreporttitle: null,
                'url' => isset($extendeduserreportlink) ? $extendeduserreportlink : null,
            ) ,
            array(
                'hasteacherviewerlinks' => isset($extendeduserreporttitle) ? $extendeduserreporttitle : null,
                'title' => isset($extendeduserreporttitle) ? $extendeduserreporttitle : null,
                'url' => isset($extendeduserreportlink) ? $extendeduserreportlink : null,
            ) ,
            array(
                'hasteacherviewerlinks' => $participantstitle,
                'title' => $participantstitle,
                'url' => $participantslink
            ) ,
            array(
                'hasqbanklinks' => $qbanktitle,
                'title' => $qbanktitle,
                'url' => $qbanklink
            ) ,
            array(
                'hasqbanklinks' => $qcattitle,
                'title' => $qcattitle,
                'url' => $qcatlink
            ) ,
            array(
                'hasqbanklinks' => $qimporttitle,
                'title' => $qimporttitle,
                'url' => $qimportlink
            ) ,
            array(
                'hasqbanklinks' => $qexporttitle,
                'title' => $qexporttitle,
                'url' => $qexportlink
            ) ,
            array(
                'hascoursemanagelinks' => $courseedittitle,
                'title' => $courseedittitle,
                'url' => $courseeditlink
            ) ,
            array(
                'hascoursemanagelinks' => $coursecompletiontitle,
                'title' => $coursecompletiontitle,
                'url' => $coursecompletionlink
            ) ,
            array(
                'hascoursemanagelinks' => $hascompetency,
                'title' => $competencytitle,
                'url' => $competencyurl
            ) ,
            array(
                'hascoursemanagelinks' => $courseadmintitle,
                'title' => $courseadmintitle,
                'url' => $courseadminlink
            ) ,
            array(
                'hascoursemanagelinks' => $courseresettitle,
                'title' => $courseresettitle,
                'url' => $courseresetlink
            ) ,
            array(
                'hascoursemanagelinks' => $coursebackuptitle,
                'title' => $coursebackuptitle,
                'url' => $coursebackuplink
            ) ,
            array(
                'hascoursemanagelinks' => $courserestoretitle,
                'title' => $courserestoretitle,
                'url' => $courserestorelink
            ) ,
            array(
                'hascoursemanagelinks' => $courseimporttitle,
                'title' => $courseimporttitle,
                'url' => $courseimportlink
            ) ,
            array(
                'hascoursemanagelinks' => $recyclebintitle,
                'title' => $recyclebintitle,
                'url' => $recyclebinlink
            ) ,
            array(
                'hascoursemanagelinks' => $filtertitle,
                'title' => $filtertitle,
                'url' => $filterlink
            ) ,
            array(
                'hascoursemanagelinks' => $eventmonitoringtitle,
                'title' => $eventmonitoringtitle,
                'url' => $eventmonitoringlink
            ) ,
            array(
                'hasbadgelinks' => $badgemanagetitle,
                'title' => $badgemanagetitle,
                'url' => $badgemanagelink
            ) ,
            array(
                'hasbadgelinks' => $badgeaddtitle,
                'title' => $badgeaddtitle,
                'url' => $badgeaddlink
            ) ,
        ) , ];

        // Attach easy enrollment links if active.
        if ($globalhaseasyenrollment && $coursehaseasyenrollment) {
            $dashlinks['dashlinks'][] = array(
                'haseasyenrollment' => $coursehaseasyenrollment,
                'title' => $easycodetitle,
                'url' => $easycodelink
            );

        }
        return $this->render_from_template('theme_stardust/teacherdash', $dashlinks);

    }

    public function footnote() {
        global $PAGE;
        $footnote = '';
        $footnote = (empty($PAGE->theme->settings->footnote)) ? false : format_text($PAGE->theme->settings->footnote);
        return $footnote;
    }

    public function brandorganization_footer() {
        $theme = theme_config::load('fordson');
        $setting = $theme->settings->brandorganization;
        return $setting != '' ? $setting : '';
    }

    public function brandwebsite_footer() {
        $theme = theme_config::load('fordson');
        $setting = $theme->settings->brandwebsite;
        return $setting != '' ? $setting : '';
    }

    public function brandphone_footer() {
        $theme = theme_config::load('fordson');
        $setting = $theme->settings->brandphone;
        return $setting != '' ? $setting : '';
    }

    public function brandemail_footer() {
        $theme = theme_config::load('fordson');
        $setting = $theme->settings->brandemail;
        return $setting != '' ? $setting : '';
    }

    public function logintext_custom() {
        global $PAGE;
        $logintext_custom = '';
        $logintext_custom = (empty($PAGE->theme->settings->fptextboxlogout)) ? false : format_text($PAGE->theme->settings->fptextboxlogout);
        return $logintext_custom;
    }

    public function render_login(\core_auth\output\login $form) {
        global $SITE, $PAGE;

        $context = $form->export_for_template($this);

        // Override because rendering is not supported in template yet.
        $context->cookieshelpiconformatted = $this->help_icon('cookiesenabled');
        $context->errorformatted = $this->error_text($context->error);
        $url = $this->get_logo_url();

        // Custom logins.
        $context->logintext_custom = isset($PAGE->theme->settings->fptextboxlogout) ? format_text($PAGE->theme->settings->fptextboxlogout) : '';
        $context->logintopimage = $PAGE->theme->setting_file_url('logintopimage', 'logintopimage');
        $context->hascustomlogin = (isset($PAGE->theme->settings->showcustomlogin) && $PAGE->theme->settings->showcustomlogin == 1) ? true : false;
        $context->hasdefaultlogin = (isset($PAGE->theme->settings->showcustomlogin) && $PAGE->theme->settings->showcustomlogin == 0) ? true : false;
        $context->alertbox = isset($PAGE->theme->settings->alertbox) ? format_text($PAGE->theme->settings->alertbox) : '';
        if ($url) {
            $url = $url->out(false);
        }
        $context->logourl = $url;
        $context->sitename = format_string($SITE->fullname, true, ['context' => context_course::instance(SITEID), "escape" => false]);

        return $this->render_from_template('core/login', $context);
    }

    public function favicon() {
        return $this->page->theme->setting_file_url('favicon', 'favicon');
    }

    public function headingfont() {
        $theme = theme_config::load('fordson');
        $setting = isset($theme->settings->headingfont) ? $theme->settings->headingfont : '';
        return $setting != '' ? $setting : '';
    }

    public function pagefont() {
        $theme = theme_config::load('fordson');
        $setting = isset($theme->settings->pagefont) ? $theme->settings->pagefont : '';
        return $setting != '' ? $setting : '';
    }

    /**
     * Get messages function implementation.
     *
     * @since  2.8
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     * @param  int      $useridto       the user id who received the message
     * @param  int      $useridfrom     the user id who send the message. -10 or -20 for no-reply or support user
     * @param  string   $type           type of message to return, expected values: notifications, conversations and both
     * @param  bool     $read           true for retreiving read messages, false for unread
     * @param  bool     $newestfirst    true for ordering by newest first, false for oldest first
     * @param  int      $limitfrom      limit from
     * @param  int      $limitnum       limit num
     * @return external_description
     */
    public function get_all_messages($useridto = 0, $useridfrom = 0, $type = 'both', $read = true,
                                        $newestfirst = true, $limitfrom = 0, $limitnum = 10) {
        global $CFG, $USER;

        $warnings = array();

        $params = array(
            'useridto' => $USER->id,
            'useridfrom' => $useridfrom,
            'type' => $type,
            'read' => $read,
            'newestfirst' => $newestfirst,
            'limitfrom' => $limitfrom,
            'limitnum' => $limitnum
        );

        $context = \context_system::instance();

        $useridto = $params['useridto'];
        $useridfrom = $params['useridfrom'];
        $type = $params['type'];
        $read = $params['read'];
        $newestfirst = $params['newestfirst'];
        $limitfrom = $params['limitfrom'];
        $limitnum = $params['limitnum'];

        $allowedvalues = array('notifications', 'conversations', 'both');
        if (!in_array($type, $allowedvalues)) {
            throw new invalid_parameter_exception('Invalid value for type parameter (value: ' . $type . '),' .
                'allowed values are: ' . implode(',', $allowedvalues));
        }

        // Check if private messaging between users is allowed.
        if (empty($CFG->messaging)) {
            // If we are retreiving only conversations, and messaging is disabled, throw an exception.
            if ($type == "conversations") {
                throw new moodle_exception('disabled', 'message');
            }
            if ($type == "both") {
                $warning = array();
                $warning['item'] = 'message';
                $warning['itemid'] = $USER->id;
                $warning['warningcode'] = '1';
                $warning['message'] = 'Private messages (conversations) are not enabled in this site.
                    Only notifications will be returned';
                $warnings[] = $warning;
            }
        }

        if (!empty($useridto)) {
            if (\core_user::is_real_user($useridto)) {
                $userto = \core_user::get_user($useridto, '*', MUST_EXIST);
            } else {
                throw new moodle_exception('invaliduser');
            }
        }

        if (!empty($useridfrom)) {
            // We use get_user here because the from user can be the noreply or support user.
            $userfrom = core_user::get_user($useridfrom, '*', MUST_EXIST);
        }

        // Check if the current user is the sender/receiver or just a privileged user.
        if ($useridto != $USER->id and $useridfrom != $USER->id and
             !has_capability('moodle/site:readallmessages', $context)) {
            throw new moodle_exception('accessdenied', 'admin');
        }

        // Which type of messages to retrieve.
        $notifications = -1;
        if ($type != 'both') {
            $notifications = ($type == 'notifications') ? 1 : 0;
        }

        $orderdirection = $newestfirst ? 'DESC' : 'ASC';
        $sort = "mr.timecreated $orderdirection";

            $readmessages = message_get_messages($useridto, $useridfrom, $notifications, true, $sort, $limitfrom, $limitnum);

            // arg #4 should be var $read - see above, but we merge arrays to display both
            $unreadmessages = message_get_messages($useridto, $useridfrom, $notifications, false, $sort, $limitfrom, $limitnum);
            // $messages = array_merge($readmessages, $unreadmessages);
            $canviewfullname = has_capability('moodle/site:viewfullnames', $context);

            // get only unread messages from each user
            $messages = array();
            foreach($unreadmessages as $item){
                $messages[$item->useridfrom] = $item;
            }

            // In some cases, we don't need to get the to/from user objects from the sql query.
            $userfromfullname = '';
            $usertofullname = '';

            // In this case, the useridto field is not empty, so we can get the user destinatary fullname from there.
            if (!empty($useridto)) {
                $usertofullname = fullname($userto, $canviewfullname);
                // The user from may or may not be filled.
                if (!empty($useridfrom)) {
                    $userfromfullname = fullname($userfrom, $canviewfullname);
                }
            } else {
                // If the useridto field is empty, the useridfrom must be filled.
                $userfromfullname = fullname($userfrom, $canviewfullname);
            }
            foreach ($messages as $mid => $message) {

                // Do not return deleted messages.
                if (($useridto == $USER->id and (isset($message->timeusertodeleted) && $message->timeusertodeleted)) or
                        ($useridfrom == $USER->id and $message->timeuserfromdeleted)) {

                    unset($messages[$mid]);
                    continue;
                }

                // We need to get the user from the query.
                if (empty($userfromfullname)) {
                    // Check for non-reply and support users.
                    if (\core_user::is_real_user($message->useridfrom)) {
                        $user = new stdClass();
                        $user = username_load_fields_from_object($user, $message, 'userfrom');
                        $message->userfromfullname = fullname($user, $canviewfullname);
                    } else {
                        $user = \core_user::get_user($message->useridfrom);
                        $message->userfromfullname = fullname($user, $canviewfullname);
                    }
                } else {
                    $message->userfromfullname = $userfromfullname;
                }

                // We need to get the user from the query.
                if (empty($usertofullname)) {
                    $user = new stdClass();
                    $user = username_load_fields_from_object($user, $message, 'userto');
                    $message->usertofullname = fullname($user, $canviewfullname);
                } else {
                    $message->usertofullname = $usertofullname;
                }

                // This field is only available in the message_read table.
                if (!isset($message->timeread)) {
                    $message->timeread = 0;
                }

                $message->text = message_format_message_text($message);
                $messages[$mid] = (array) $message;
            }


        $results = array(
            'messages' => array_values($messages),
            'warnings' => $warnings
        );
        return $results;
    }


/**
 * Get badges for user
 */

 public function get_user_badges(){
    global $CFG, $USER, $OUTPUT;

    $badges = badges_get_user_badges($USER->id);
    foreach ($badges as $badge) {
        $context = ($badge->type == BADGE_TYPE_SITE) ? \context_system::instance() : \context_course::instance($badge->courseid);
        $badge->imageurl = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $badge->id, '/', 'f1', false)->out();
    }
    $badges = array_values($badges);

    $result = array(
      'badges' => $badges,
      'count'  => count($badges)
    );
    return $result;
 }

 /**
 * Get certificates for user
 */
public function get_user_certificates(){
    global $CFG, $DB, $USER;
    $certificates = array();

    // get all user certificates from DB
    $ucertdbraw  = $DB->get_records_sql("
        SELECT
             ci.*, c.*, ci.timecreated as ctimecreated
        FROM
            {certificate_issues} ci
            INNER JOIN {certificate} c ON c.id=ci.certificateid
        WHERE
            ci.userid = ?", array($USER->id));

    // process each cert to add info
    foreach ($ucertdbraw as $ucert) {
        list($ccourse, $ccm) = get_course_and_cm_from_instance($ucert->id, 'certificate');
        $ucert->cmid = $ccm->id;
        $certificates[] = $ucert; // reorder certificates for mustache
    }

    return $certificates;
 }

    /**
     * Outputs the user menu.
     * @return custom_menu object
     */
    /*
    public function custom_menu_user() {
        // Die if executed during install.
        if (during_initial_install()) {
            return false;
        }

        global $USER, $CFG, $DB;
        $loginurl = get_login_url();

        $usermenu = html_writer::start_tag('ul', array('class' => 'nav'));
        $usermenu .= html_writer::start_tag('li', array('class' => 'dropdown'));

        if (!isloggedin()) {
            if ($this->page->pagelayout != 'login') {
                $userpic = '<em>'.$this->getfontawesomemarkup('sign-in').get_string('login').'</em>';
                $usermenu .= html_writer::link($loginurl, $userpic, array('class' => 'loginurl'));
            }
        } else if (isguestuser()) {
            $userurl = new moodle_url('#');
            $userpic = parent::user_picture($USER, array('link' => false, 'popup' => false));
            $caret = $this->getfontawesomemarkup('caret-right');
            $userclass = array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown');
            $usermenu .= html_writer::link($userurl, $userpic.get_string('guest').$caret, $userclass);

            // Render direct login link.
            $classes = 'dropdown-menu';
            if ($this->left) {
                $classes .= ' pull-right';
            }
            $usermenu .= html_writer::start_tag('ul', array('class' => $classes));
            $branchlabel = '<em>'.$this->getfontawesomemarkup('sign-in').get_string('login').'</em>';
            $branchurl = new moodle_url('/login/index.php');
            $usermenu .= html_writer::tag('li', html_writer::link($branchurl, $branchlabel));

            // Render Help Link.
            // TODO: Fix me? (nadavkav)
            //$usermenu .= $this->theme_stardust_render_helplink();

            $usermenu .= html_writer::end_tag('ul');

        } else {
            $course = $this->page->course;
            $context = context_course::instance($course->id);

            // Output Profile link.
            $userurl = new moodle_url('#');
            $userpic = parent::user_picture($USER, array('link' => false, 'popup' => false));
            $caret = $this->getfontawesomemarkup('caret-right');
            $userclass = array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown');

            if (!empty($USER->alternatename)) {
                $usermenu .= html_writer::link($userurl, $userpic.$USER->alternatename.$caret, $userclass);
            } else {
                $usermenu .= html_writer::link($userurl, $userpic.$USER->firstname.$caret, $userclass);
            }

            // Start dropdown menu items.
            $classes = 'dropdown-menu';
            if ($this->left) {
                $classes .= ' pull-right';
            }
            $usermenu .= html_writer::start_tag('ul', array('class' => $classes));

            if (\core\session\manager::is_loggedinas()) {
                $realuser = \core\session\manager::get_realuser();
                $branchlabel = '<em>'.$this->getfontawesomemarkup('key').fullname($realuser, true).
                    get_string('loggedinas', 'theme_stardust').fullname($USER, true).'</em>';
            } else {
                $branchlabel = '<em>'.$this->getfontawesomemarkup('user').fullname($USER, true).'</em>';
            }
            $branchurl = new moodle_url('/user/profile.php', array('id' => $USER->id));
            $usermenu .= html_writer::tag('li', html_writer::link($branchurl, $branchlabel));

            if (is_mnet_remote_user($USER) && $idprovider = $DB->get_record('mnet_host', array('id' => $USER->mnethostid))) {
                $branchlabel = '<em>'.$this->getfontawesomemarkup('users').get_string('loggedinfrom', 'theme_stardust').
                    $idprovider->name.'</em>';
                $branchurl = new moodle_url($idprovider->wwwroot);
                $usermenu .= html_writer::tag('li', html_writer::link($branchurl, $branchlabel));
            }

            if (is_role_switched($course->id)) { // Has switched roles.
                $branchlabel = '<em>'.$this->getfontawesomemarkup('users').get_string('switchrolereturn').'</em>';
                $branchurl = new moodle_url('/course/switchrole.php', array('id' => $course->id, 'sesskey' => sesskey(),
                    'switchrole' => 0, 'returnurl' => $this->page->url->out_as_local_url(false)));
                $usermenu .= html_writer::tag('li', html_writer::link($branchurl, $branchlabel));
            }

            // Add preferences submenu.
            $usermenu .= $this->theme_stardust_render_preferences($context);

            $usermenu .= html_writer::empty_tag('hr', array('class' => 'sep'));

            // Output Calendar link if user is allowed to edit own calendar entries.
            if (has_capability('moodle/calendar:manageownentries', $context)) {
                $branchlabel = '<em>'.$this->getfontawesomemarkup('calendar').
                    get_string('pluginname', 'block_calendar_month').'</em>';
                $branchurl = new moodle_url('/calendar/view.php');
                $usermenu .= html_writer::tag('li', html_writer::link($branchurl, $branchlabel));
            }

            if (is_siteadmin()) { // hanna 25/1/17  people dont use ?
                // Check if messaging is enabled.
                if (!empty($CFG->messaging)) {
                    // If messaging disabled for that user, don't show link.
                    $usermessagesdisabled = get_user_preferences('messagesdisabled', 1, $USER);
                    if ($usermessagesdisabled == 1) {
                        $branchlabel = '<em>' . $this->getfontawesomemarkup('envelope') . get_string('pluginname', 'block_messages') . '</em>';
                        $branchurl = new moodle_url('/message/index.php');
                        $usermenu .= html_writer::tag('li', html_writer::link($branchurl, $branchlabel));
                    }
                }
            }  // end if siteadmin

            // Check if user is allowed to manage files.
            if (has_capability('moodle/user:manageownfiles', $context)) {
                $branchlabel = '<em>'.$this->getfontawesomemarkup('file').get_string('privatefiles', 'block_private_files').'</em>';
                $branchurl = new moodle_url('/user/files.php');
                $usermenu .= html_writer::tag('li', html_writer::link($branchurl, $branchlabel));
            }

            if (is_siteadmin()) { // hanna 25/1/17  people dont use ?
                // Check if user is allowed to view discussions.
                if (has_capability('mod/forum:viewdiscussion', $context)) {
                    $branchlabel = '<em>' . $this->getfontawesomemarkup('list-alt') . get_string('forumposts', 'mod_forum') . '</em>';
                    $branchurl = new moodle_url('/mod/forum/user.php', array('id' => $USER->id));
                    $usermenu .= html_writer::tag('li', html_writer::link($branchurl, $branchlabel));

                    $branchlabel = '<em>' . $this->getfontawesomemarkup('list') . get_string('discussions', 'mod_forum') . '</em>';
                    $branchurl = new moodle_url('/mod/forum/user.php', array('id' => $USER->id, 'mode' => 'discussions'));
                    $usermenu .= html_writer::tag('li', html_writer::link($branchurl, $branchlabel));

                    $usermenu .= html_writer::empty_tag('hr', array('class' => 'sep'));
                }
            }  // end if siteadmin

            // Output user grade links, course sensitive where appropriate.
            if ($course->id == SITEID) {
                $branchlabel = '<em>'.$this->getfontawesomemarkup('list-alt').get_string('mygrades', 'theme_stardust').'</em>';
                $branchurl = new moodle_url('/grade/report/overview/index.php', array('userid' => $USER->id));
                $usermenu .= html_writer::tag('li', html_writer::link($branchurl, $branchlabel));
            } else {
                if (has_capability('gradereport/overview:view', $context)) {
                    $branchlabel = '<em>'.$this->getfontawesomemarkup('list-alt').get_string('mygrades', 'theme_stardust').'</em>';
                    $params = array('userid' => $USER->id);
                    if ($course->showgrades) {
                        $params['id'] = $course->id;
                    }
                    $branchurl = new moodle_url('/grade/report/overview/index.php', $params);
                    $usermenu .= html_writer::tag('li', html_writer::link($branchurl, $branchlabel));
                }

                if (has_capability('gradereport/user:view', $context) && $course->showgrades) {
                    // In Course also output Course grade links.
                    $branchlabel = '<em>'.$this->getfontawesomemarkup('list-alt').
                        get_string('coursegrades', 'theme_stardust').'</em>';
                    $branchurl = new moodle_url('/grade/report/user/index.php', array('id' => $course->id, 'userid' => $USER->id));
                    $usermenu .= html_writer::tag('li', html_writer::link($branchurl, $branchlabel));
                }
            }

            if (is_siteadmin()) { // hanna 25/1/17  people dont use ?
                // Check if badges are enabled.
                if (!empty($CFG->enablebadges) && has_capability('moodle/badges:manageownbadges', $context)) {
                    $branchlabel = '<em>' . $this->getfontawesomemarkup('certificate') . get_string('badges') . '</em>';
                    $branchurl = new moodle_url('/badges/mybadges.php');
                    $usermenu .= html_writer::tag('li', html_writer::link($branchurl, $branchlabel));
                }
            }  // end if siteadmin
            $usermenu .= html_writer::empty_tag('hr', array('class' => 'sep'));

            // Render direct logout link.
            $branchlabel = '<em>'.$this->getfontawesomemarkup('sign-out').get_string('logout').'</em>';
            if (\core\session\manager::is_loggedinas()) {
                $branchurl = new moodle_url('/course/loginas.php', array('id' => $course->id, 'sesskey' => sesskey()));
            } else {
                $branchurl = new moodle_url('/login/logout.php', array('sesskey' => sesskey()));
            }
            $usermenu .= html_writer::tag('li', html_writer::link($branchurl, $branchlabel));

            // Render Help Link.
            // TODO: fix me (nadavkav)
            //$usermenu .= $this->theme_stardust_render_helplink();

            $usermenu .= html_writer::end_tag('ul');
        }

        $usermenu .= html_writer::end_tag('li');
        $usermenu .= html_writer::end_tag('ul');

        return $usermenu;
    }
*/

    /* Quick action menu for each user, when user image is clicked.
   * integrated by : nadavkav@gmail.com
   */
    protected function render_user_picture(\user_picture $userpicture) {
        global $CFG, $DB, $PAGE, $USER;

        $user = $userpicture->user;

        if ($userpicture->alttext) {
            if (!empty($user->imagealt)) {
                $alt = $user->imagealt;
            } else {
                $alt = get_string('pictureof', '', fullname($user));
            }
        } else {
            $alt = '';
        }

        if (empty($userpicture->size)) {
            $size = 35;
        } else if ($userpicture->size === true or $userpicture->size == 1) {
            $size = 100;
        } else {
            $size = $userpicture->size;
        }

        $class = $userpicture->class;

        if ($user->picture == 0) {
            $class .= ' defaultuserpic';
        }

        if (user_has_role_assignment($user->id, 3 /* editingteacher */, $PAGE->context->id)) {
            $class .= ' teacher';
        }

        $src = $userpicture->get_url($this->page, $this);

        $attributes = array('src'=>$src, 'alt'=>$alt, 'title'=>$alt, 'class'=>$class, 'width'=>$size, 'height'=>$size);
        if (!$userpicture->visibletoscreenreaders) {
            $attributes['role'] = 'presentation';
        }

        if (empty($userpicture->courseid)) {
            $courseid = $this->page->course->id;
        } else {
            $courseid = $userpicture->courseid;
        }

        // get the image html output fisrt
        $output = html_writer::start_tag('div', array('class'=>'profilepicture'));
        if ((user_has_role_assignment($USER->id, 3 /* editingteacher */, $PAGE->context->id)
                OR user_has_role_assignment($USER->id, 1 /* manager */, $PAGE->context->id)
                OR array_key_exists($USER->id, get_admins()) )
                AND ($userpicture->link and $size >= 35) ) {
            //if ($userpicture->link and $size >= 35) {
            $output .= $this->user_action_menu($user->id, $courseid, $attributes);
            //}
        } else {
            $output .= html_writer::empty_tag('img', $attributes);
        }

        // Show fullname together with the picture when desired.
        if ($userpicture->includefullname) {
            $output .= html_writer::div(fullname($userpicture->user), 'fullname');
        }
        $output .= html_writer::end_tag('div');

        /*
        if (user_has_role_assignment($user->id,3,$PAGE->context->id)) {
            $output .= html_writer::start_tag('div',array('style'=>'position: relative;top: -20px;right: 20px;'));
            $output .= html_writer::empty_tag('img', array('id'=>'roleimg',
                'src'=>new moodle_url('/theme/essential/pix_core/i/grademark.png')) );
            $output .= html_writer::end_tag('div');
        }
        */

        // then wrap it in link if needed
        if (!$userpicture->link) {
            return $output;
        }

        if ($courseid == SITEID) {
            $url = new moodle_url('/user/profile.php', array('id' => $user->id));
        } else {
            $url = new moodle_url('/user/view.php', array('id' => $user->id, 'course' => $courseid));
        }

        $attributes = array('href' => $url);
        if (!$userpicture->visibletoscreenreaders) {
            $attributes['tabindex'] = '-1';
            $attributes['aria-hidden'] = 'true';
        }

        /* Disabled. Now it is used for User's Action menu.
        if ($userpicture->popup) {

            $id = html_writer::random_id('userpicture');
            $attributes['id'] = $id;
            $this->add_action_handler(new popup_action('click', $url), $id);

        }
        */
        return html_writer::tag('a', $output, $attributes);

        //return $output;
        //return html_writer::tag('div', $output, array('onclick'=>'alert("hello")'));
    }

    private function user_action_menu($userid, $courseid = SITEID, $attributes ) {

        global $USER, $CFG, $DB;

        $edit = '';
        $actions = array();

        // Action URLs

        // View user's profile
        if ($courseid == SITEID) {
            $url = new moodle_url('/user/profile.php', array('id' => $userid));
        } else {
            $url = new moodle_url('/user/view.php', array('id' => $userid, 'course' => $courseid));
        }
        $actions[$url->out(false)] = get_string('user_viewprofile','theme_stardust');

        // View user's complete report
        $url = new moodle_url('/report/outline/user.php',
            array('id' => $userid, 'course'=>$courseid, 'mode'=>'complete'));
        $actions[$url->out(false)] = get_string('user_completereport','theme_stardust');

        // View user's outline report
        $url = new moodle_url('/report/outline/user.php',
            array('id' => $userid, 'course'=>$courseid, 'mode'=>'outline'));
        $actions[$url->out(false)] = get_string('user_outlinereport','theme_stardust');

        // Edit user's profile
        $url = new moodle_url('/user/editadvanced.php', array('id' => $userid, 'course'=>$courseid));
        $actions[$url->out(false)] = get_string('user_editprofile','theme_stardust');

        // Send private message
        if ($USER->id != $userid) {
            $url = new moodle_url('/message/index.php', array('id'=>$userid));
            $actions[$url->out(false)] = get_string('user_sendmessage','theme_stardust');
        }

        // Completion enabled in course? Display user's link to completion report.
        $coursecompletion = $DB->get_field('course', 'enablecompletion', array('id' => $courseid));
        if (!empty($CFG->enablecompletion) AND $coursecompletion) {
            $url = new moodle_url('/blocks/completionstatus/details.php', array('user' => $userid, 'course'=>$courseid));
            $actions[$url->out(false)] = get_string('user_coursecompletion','theme_stardust');
        }

        // All user's mdl_log HITS
        $url = new moodle_url('/report/log/user.php', array('id' => $userid, 'course'=>$courseid, 'mode'=>'all'));
        $actions[$url->out(false)] = get_string('user_courselogs','theme_stardust');

        // User's grades in course ID
        $url = new moodle_url('/grade/report/user/index.php', array('userid' => $userid, 'id'=>$courseid));
        $actions[$url->out(false)] = get_string('user_coursegrades','theme_stardust');

        // Login as ...
        $coursecontext = context_course::instance($courseid);
        if ($USER->id != $userid && !\core\session\manager::is_loggedinas() && has_capability('moodle/user:loginas', $coursecontext) && !is_siteadmin($userid)) {
            $url = new moodle_url('/course/loginas.php', array('id'=>$courseid, 'user'=>$userid, 'sesskey'=>sesskey()));
            $actions[$url->out(false)] = get_string('user_loginas','theme_stardust');
        }

        // Reset user's password to original password (stored in user.url profile field)
        $coursecontext = context_course::instance($courseid);

        if ($USER->id != $userid && !\core\session\manager::is_loggedinas() || in_array($USER->id, get_admins()) ) {
            $resetpasswordurl = new moodle_url('/report/roster/resetpassword.php', array('userid' => $userid, 'sesskey' => sesskey()));
            $actions[$resetpasswordurl->out(false)] = get_string('resetpassword','report_roster');
        }

        // Setup the menu
        $edit .= $this->container_start(array('yui3-menu', 'yui3-menubuttonnav', 'useractionmenu'), 'useractionselect' . $userid);
        $edit .= $this->container_start(array('yui3-menu-content'));
        $edit .= html_writer::start_tag('ul');
        $edit .= html_writer::start_tag('li', array('class'=>'menuicon'));
        //$menuicon = $this->pix_icon('t/contextmenu', get_string('actions'));
        //$menuicon = $this->pix_icon('t/switch_minus', get_string('actions'));
        $menuicon = html_writer::empty_tag('img', $attributes); //$attributes['src'];
        $edit .= $this->action_link('#menu' . $userid, $menuicon, null, array('class'=>'yui3-menu-label'));
        $edit .= $this->container_start(array('yui3-menu', 'yui3-loading'), 'menu' . $userid);
        $edit .= $this->container_start(array('yui3-menu-content'));
        $edit .= html_writer::start_tag('ul');
        foreach ($actions as $url => $description) {
            $edit .= html_writer::start_tag('li', array('class'=>'yui3-menuitem'));
            $edit .= $this->action_link($url, $description, null, array('class'=>'yui3-menuitem-content', 'target'=>'_new'));
            //$edit .= $this->add_action_handler(new popup_action('click', $url), array('id'=>html_writer::random_id('userpicture')));
            $edit .= html_writer::end_tag('li');
        }
        $edit .= html_writer::end_tag('ul');
        $edit .= $this->container_end();
        $edit .= $this->container_end();
        $edit .= html_writer::end_tag('li');
        $edit .= html_writer::end_tag('ul');
        $edit .= $this->container_end();
        $edit .= $this->container_end();

        return $edit;
    }

    /**
     * Renders preferences submenu
     *
     * @param integer $context
     * @return string $preferences
     */
    protected function theme_stardust_render_preferences(\context $context) {
        global $USER, $CFG;
        //$label = '<em>'.$this->getfontawesomemarkup('cog').get_string('preferences').'</em>'; // hanna 23/11/15
        $label = '<em><i class="fa fa-cog"></i>' . get_string('personaldetails','core_davidson') . '</em>'; // hanna 23/11/15
        $preferences = html_writer::start_tag('li', array('class' => 'dropdown-submenu preferences'));
        $preferences .= html_writer::link(new moodle_url('#'), $label,
            array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown'));
        $preferences .= html_writer::start_tag('ul', array('class' => 'dropdown-menu'));

        if (has_capability('moodle/user:editownprofile', $context)) {
            //    if (is_siteadmin()) { // hanna 7/10/15
            //       $branchlabel = '<em>' . $this->getfontawesomemarkup('user') . get_string('user', 'moodle') . '</em>';
            $branchlabel = '<em>' . $this->getfontawesomemarkup('user') . get_string('preferences', 'moodle') . '</em>';
            $branchurl = new moodle_url('/user/preferences.php', array('userid' => $USER->id));
            $preferences .= html_writer::tag('li', html_writer::link($branchurl, $branchlabel));
        }
        // Check if user is allowed to edit profile.
        if (has_capability('moodle/user:editownprofile', $context)) {
            $branchlabel = '<em>'.$this->getfontawesomemarkup('info-circle').get_string('editmyprofile').'</em>';
            $branchurl = new moodle_url('/user/edit.php', array('id' => $USER->id));
            $preferences .= html_writer::tag('li', html_writer::link($branchurl, $branchlabel));
        }
        if (has_capability('moodle/user:changeownpassword', $context)) {
            $branchlabel = '<em>'.$this->getfontawesomemarkup('key').get_string('changepassword').'</em>';
            $branchurl = new moodle_url('/login/change_password.php');
            $preferences .= html_writer::tag('li', html_writer::link($branchurl, $branchlabel));
        }
        //if (has_capability('moodle/user:editownmessageprofile', $context)) {
        if (is_siteadmin()) { // hanna 21/9/15
            $branchlabel = '<em>'.$this->getfontawesomemarkup('comments').get_string('message', 'message').'</em>';
            $branchurl = new moodle_url('/message/edit.php', array('id' => $USER->id));
            $preferences .= html_writer::tag('li', html_writer::link($branchurl, $branchlabel));
        }
        if ($CFG->enableblogs) {
            $branchlabel = '<em>'.$this->getfontawesomemarkup('rss-square').get_string('blog', 'blog').'</em>';
            $branchurl = new moodle_url('/blog/preferences.php');
            $preferences .= html_writer::tag('li', html_writer::link($branchurl, $branchlabel));
        }
        if (is_siteadmin()) { // hanna 25/1/17  people dont use ?
            if ($CFG->enablebadges && has_capability('moodle/badges:manageownbadges', $context)) {
                $branchlabel = '<em>' . $this->getfontawesomemarkup('certificate') .
                    get_string('badgepreferences', 'theme_stardust') . '</em>';
                $branchurl = new moodle_url('/badges/preferences.php');
                $preferences .= html_writer::tag('li', html_writer::link($branchurl, $branchlabel));
            }
        }  // end if siteadmin
        $preferences .= html_writer::end_tag('ul');
        $preferences .= html_writer::end_tag('li');
        return $preferences;
    }

    private function getfontawesomemarkup($theicon, $classes = array(), $attributes = array(), $content = '') {
        $classes[] = 'fa fa-'.$theicon;
        $attributes['aria-hidden'] = 'true';
        $attributes['class'] = implode(' ', $classes);
        return html_writer::tag('span', $content, $attributes);
    }

    /**
     * Outputs a heading
     *
     * @param string $text The text of the heading
     * @param int $level The level of importance of the heading. Defaulting to 2
     * @param string $classes A space-separated list of CSS classes. Defaulting to null
     * @param string $id An optional ID
     * @return string the HTML to output.
     */
    public function heading($text, $level = 2, $classes = null, $id = null) {
        global $PAGE;
        $level = (integer) $level;
        if ($level < 1 or $level > 6) {
            throw new coding_exception('Heading level must be an integer between 1 and 6.');
        }
        // SG - T-298 - change heading at course/edit.php
        if ($PAGE->url->compare(new moodle_url('/course/edit.php'), URL_MATCH_BASE)) {
            return html_writer::tag('h' . $level, $PAGE->course->shortname, array('id' => $id, 'class' => \renderer_base::prepare_classes($classes)));
        } else {
            // SG - render all other headings as usual
            return html_writer::tag('h' . $level, $text, array('id' => $id, 'class' => \renderer_base::prepare_classes($classes)));
        }

    }

    /**
     * Prepare links for Footer from theme's settings
     * Is called from template directly
     * Example: {{output.footerlinks.youtube}}
     */
    public function footerlinks() {
        global $CFG, $PAGE;

        $footerlinks = array();
        $footerlinks['mainlogo'] = empty($PAGE->theme->settings->footersettigs_mainlogo_url) ? '#' : $PAGE->theme->settings->footersettigs_mainlogo_url;
        $footerlinks['youtube'] = empty($PAGE->theme->settings->footersettigs_youtube_url) ? '#' : $PAGE->theme->settings->footersettigs_youtube_url;
        $footerlinks['facebook'] = empty($PAGE->theme->settings->footersettigs_facebook_url) ? '#' : $PAGE->theme->settings->footersettigs_facebook_url;
        $footerlinks['twitter'] = empty($PAGE->theme->settings->footersettigs_twitter_url) ? '#' : $PAGE->theme->settings->footersettigs_twitter_url;
        $footerlinks['instagram'] = empty($PAGE->theme->settings->footersettigs_instagram_url) ? '#' : $PAGE->theme->settings->footersettigs_instagram_url;
        $footerlinks['homepage'] = empty($PAGE->theme->settings->footersettigs_homepage_url) ? $CFG->wwwroot : $PAGE->theme->settings->footersettigs_homepage_url;
        $footerlinks['aboutus'] = empty($PAGE->theme->settings->footersettigs_aboutus_url) ? '#' : $PAGE->theme->settings->footersettigs_aboutus_url;
        $footerlinks['contactus'] = empty($PAGE->theme->settings->footersettigs_contactus_url) ? '#' : $PAGE->theme->settings->footersettigs_contactus_url;
        $footerlinks['privacypolicy'] = empty($PAGE->theme->settings->footersettigs_privacypolicy_url) ? '#' : $PAGE->theme->settings->footersettigs_privacypolicy_url;
        $footerlinks['termsofuse'] = empty($PAGE->theme->settings->footersettigs_termsofuse_url) ? '#' : $PAGE->theme->settings->footersettigs_termsofuse_url;
        $footerlinks['accessibilitystatement'] = empty($PAGE->theme->settings->footersettigs_accessibilitystatement_url) ? '#' : $PAGE->theme->settings->footersettigs_accessibilitystatement_url;
        $footerlinks['listofcourses'] = empty($PAGE->theme->settings->footersettigs_listofcourses_url) ? '#' : $PAGE->theme->settings->footersettigs_listofcourses_url;
        $footerlinks['davidsonsite'] = empty($PAGE->theme->settings->footersettigs_davidsonsite_url) ? '#' : $PAGE->theme->settings->footersettigs_davidsonsite_url;
        $footerlinks['appstore'] = empty($PAGE->theme->settings->footersettigs_appstore_url) ? '#' : $PAGE->theme->settings->footersettigs_appstore_url;
        $footerlinks['googleplay'] = empty($PAGE->theme->settings->footersettigs_googleplay_url) ? '#' : $PAGE->theme->settings->footersettigs_googleplay_url;

        return $footerlinks;
    }
}

// SG - 20181214 - T-263, T-341 -  we have rewritten the renderer to reduce the profile page load, but it breaks some other user view pages. So comment for now
// namespace theme_stardust\output\core_user\myprofile;
// /**
//  * Override user profile renderer
//  *
//  * We disable profile TREE render to not overload page with extra information
//  */

// class renderer extends \core_user\output\myprofile\renderer {

//     public function render_tree(\core_user\output\myprofile\tree $tree) {
//         return "";
//     }
// }
