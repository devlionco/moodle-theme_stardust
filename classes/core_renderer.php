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

namespace theme_stardust\output;

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

require_once ($CFG->dirroot . "/message/lib.php");
require_once ($CFG->libdir . '/badgeslib.php');
require_once ($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/message/output/popup/lib.php');

/**
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_stardust
 * @copyright  2018 Devlion.co
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends \theme_fordson\output\core_renderer {

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
                if (isset($value->titleidentifier) && $value->titleidentifier == 'messages,message') {
                    if (!$this->is_user_messaging_enabled())
                        continue; // Skip message menu item for some users.
                }

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

    /**
     * Allow plugins to provide some content to be rendered in the navbar. Without notifications.
     * the HTML they wish to add to the navbar.
     *
     * @return string HTML for the navbar
     */
    public function notifications_output() {
        $result = message_popup_render_navbar_output($this);
        $output = preg_replace('/(<!--topblockpopover-region-messages--><div)/m', '(<!--topblockpopover-region-messages--><div style="display: none;" ', $result);
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
        global $OUTPUT, $PAGE;
        
        $outputen = $PAGE->theme->setting_file_url('headerlogo', 'headerlogo');
        $outputhe = $PAGE->theme->setting_file_url('headerlogohe', 'headerlogohe');

        if (current_language() == "he") {
            $output = $outputhe ? $outputhe : $outputen; // Is logo for Hebrew not configured, use logo for English.
        } else {
            $output = $outputen ? $outputen : $outputhe; // Use English logo for other languages, if no English choose Hebrew.
        }
        if (!$output) {
            $logourl = (current_language() == "he") ? 'header/logo_davidson_he' : 'header/logo_davidson_eng'; // Default logo, if no logos configured.
            $output = $OUTPUT->image_url($logourl, 'theme');
        }
        return $output;
    }

    public function get_stardust_moodle_logo() {
        global $OUTPUT;
        $output = $OUTPUT->image_url('header/logo_moodle', 'theme');

        return $output;
    }

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
            $html .= html_writer::div($this->context_header_settings_menu(), 'pull-xs-right context-header-settings-menu');
        } else if (isset($COURSE->id) && $COURSE->id == 1) {
            $html .= html_writer::div($this->context_header_settings_menu(), 'pull-xs-right context-header-settings-menu');
        }
        $html .= html_writer::start_div('pull-xs-left');
        $context_header = $this->context_header();
        $html .= html_writer::link(new moodle_url('/course/view.php', array('id' => $PAGE->course->id)), $context_header);
        $html .= html_writer::end_div();
        $pageheadingbutton = $this->page_heading_button();
        if (empty($PAGE->layout_options['nonavbar'])) {
            $html .= html_writer::start_div('clearfix w-100 pull-xs-left', array(
                        'id' => 'page-navbar'
            ));
            $html .= html_writer::tag('div', $this->navbar(), array(
                        'class' => 'breadcrumb-nav'
            ));
            $html .= html_writer::div($pageheadingbutton, 'breadcrumb-button pull-xs-right');
            $html .= html_writer::end_div();
        } else if ($pageheadingbutton) {
            $html .= html_writer::div($pageheadingbutton, 'breadcrumb-button nonavbar pull-xs-right');
        }
        $html .= html_writer::tag('div', $this->course_header(), array(
                    'id' => 'course-header'
        ));
        $html .= html_writer::end_div();
        $html .= html_writer::end_div();
        $html .= html_writer::end_div();
        $html .= html_writer::end_div();
        $html .= html_writer::end_tag('header');
        return $html;
    }

    public function headingfont() {
        $theme = theme_config::load('stardust');
        $setting = isset($theme->settings->headingfont) ? $theme->settings->headingfont : '';
        return $setting != '' ? $setting : '';
    }

    public function pagefont() {
        $theme = theme_config::load('stardust');
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
        if ($useridto != $USER->id and $useridfrom != $USER->id and ! has_capability('moodle/site:readallmessages', $context)) {
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
        foreach ($unreadmessages as $item) {
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
            if (($useridto == $USER->id and ( isset($message->timeusertodeleted) && $message->timeusertodeleted)) or ( $useridfrom == $USER->id and $message->timeuserfromdeleted)) {

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
     * Defines either messaging system is enabled for user or not
     * @return bool
     */
    public function is_user_messaging_enabled() {
        global $USER;
        $usermessagesdisabled = get_user_preferences('messagesdisabled', 1, $USER);
        if ($usermessagesdisabled == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get badges for user
     */
    public function get_user_badges() {
        global $CFG, $USER, $OUTPUT;

        $badges = badges_get_user_badges($USER->id);
        foreach ($badges as $badge) {
            $context = ($badge->type == BADGE_TYPE_SITE) ? \context_system::instance() : \context_course::instance($badge->courseid);
            $badge->imageurl = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $badge->id, '/', 'f1', false)->out();
        }
        $badges = array_values($badges);

        $result = array(
            'badges' => $badges,
            'count' => count($badges)
        );
        return $result;
    }

    /**
     * Get certificates for user
     */
    public function get_user_certificates() {
        global $CFG, $DB, $USER;
        $certificates = array();

        // get all user certificates from DB
        $ucertdbraw = $DB->get_records_sql("
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

        $attributes = array('src' => $src, 'alt' => $alt, 'title' => $alt, 'class' => $class, 'width' => $size, 'height' => $size);
        if (!$userpicture->visibletoscreenreaders) {
            $attributes['role'] = 'presentation';
        }

        if (empty($userpicture->courseid)) {
            $courseid = $this->page->course->id;
        } else {
            $courseid = $userpicture->courseid;
        }

        // get the image html output fisrt
        $output = html_writer::start_tag('div', array('class' => 'profilepicture'));
        if ((user_has_role_assignment($USER->id, 3 /* editingteacher */, $PAGE->context->id)
                OR user_has_role_assignment($USER->id, 1 /* manager */, $PAGE->context->id)
                OR array_key_exists($USER->id, get_admins()) )
                AND ( $userpicture->link and $size >= 35)) {
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

    private function user_action_menu($userid, $courseid = SITEID, $attributes) {

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
        $actions[$url->out(false)] = get_string('user_viewprofile', 'theme_stardust');

        // View user's complete report
        $url = new moodle_url('/report/outline/user.php',
                array('id' => $userid, 'course' => $courseid, 'mode' => 'complete'));
        $actions[$url->out(false)] = get_string('user_completereport', 'theme_stardust');

        // View user's outline report
        $url = new moodle_url('/report/outline/user.php',
                array('id' => $userid, 'course' => $courseid, 'mode' => 'outline'));
        $actions[$url->out(false)] = get_string('user_outlinereport', 'theme_stardust');

        // Edit user's profile
        $url = new moodle_url('/user/editadvanced.php', array('id' => $userid, 'course' => $courseid));
        $actions[$url->out(false)] = get_string('user_editprofile', 'theme_stardust');

        // Send private message
        if ($USER->id != $userid) {
            $url = new moodle_url('/message/index.php', array('id' => $userid));
            $actions[$url->out(false)] = get_string('user_sendmessage', 'theme_stardust');
        }

        // Completion enabled in course? Display user's link to completion report.
        $coursecompletion = $DB->get_field('course', 'enablecompletion', array('id' => $courseid));
        if (!empty($CFG->enablecompletion) AND $coursecompletion) {
            $url = new moodle_url('/blocks/completionstatus/details.php', array('user' => $userid, 'course' => $courseid));
            $actions[$url->out(false)] = get_string('user_coursecompletion', 'theme_stardust');
        }

        // All user's mdl_log HITS
        $url = new moodle_url('/report/log/user.php', array('id' => $userid, 'course' => $courseid, 'mode' => 'all'));
        $actions[$url->out(false)] = get_string('user_courselogs', 'theme_stardust');

        // User's grades in course ID
        $url = new moodle_url('/grade/report/user/index.php', array('userid' => $userid, 'id' => $courseid));
        $actions[$url->out(false)] = get_string('user_coursegrades', 'theme_stardust');

        // Login as ...
        $coursecontext = context_course::instance($courseid);
        if ($USER->id != $userid && !\core\session\manager::is_loggedinas() && has_capability('moodle/user:loginas', $coursecontext) && !is_siteadmin($userid)) {
            $url = new moodle_url('/course/loginas.php', array('id' => $courseid, 'user' => $userid, 'sesskey' => sesskey()));
            $actions[$url->out(false)] = get_string('user_loginas', 'theme_stardust');
        }

        // Reset user's password to original password (stored in user.url profile field)
        $coursecontext = context_course::instance($courseid);

        if ($USER->id != $userid && !\core\session\manager::is_loggedinas() || in_array($USER->id, get_admins())) {
            $resetpasswordurl = new moodle_url('/report/roster/resetpassword.php', array('userid' => $userid, 'sesskey' => sesskey()));
            $actions[$resetpasswordurl->out(false)] = get_string('resetpassword', 'report_roster');
        }

        // Setup the menu
        $edit .= $this->container_start(array('yui3-menu', 'yui3-menubuttonnav', 'useractionmenu'), 'useractionselect' . $userid);
        $edit .= $this->container_start(array('yui3-menu-content'));
        $edit .= html_writer::start_tag('ul');
        $edit .= html_writer::start_tag('li', array('class' => 'menuicon'));
        //$menuicon = $this->pix_icon('t/contextmenu', get_string('actions'));
        //$menuicon = $this->pix_icon('t/switch_minus', get_string('actions'));
        $menuicon = html_writer::empty_tag('img', $attributes); //$attributes['src'];
        $edit .= $this->action_link('#menu' . $userid, $menuicon, null, array('class' => 'yui3-menu-label'));
        $edit .= $this->container_start(array('yui3-menu', 'yui3-loading'), 'menu' . $userid);
        $edit .= $this->container_start(array('yui3-menu-content'));
        $edit .= html_writer::start_tag('ul');
        foreach ($actions as $url => $description) {
            $edit .= html_writer::start_tag('li', array('class' => 'yui3-menuitem'));
            $edit .= $this->action_link($url, $description, null, array('class' => 'yui3-menuitem-content', 'target' => '_new'));
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
        $label = '<em><i class="fa fa-cog"></i>' . get_string('personaldetails', 'core_davidson') . '</em>'; // hanna 23/11/15
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
            $branchlabel = '<em>' . $this->getfontawesomemarkup('info-circle') . get_string('editmyprofile') . '</em>';
            $branchurl = new moodle_url('/user/edit.php', array('id' => $USER->id));
            $preferences .= html_writer::tag('li', html_writer::link($branchurl, $branchlabel));
        }
        if (has_capability('moodle/user:changeownpassword', $context)) {
            $branchlabel = '<em>' . $this->getfontawesomemarkup('key') . get_string('changepassword') . '</em>';
            $branchurl = new moodle_url('/login/change_password.php');
            $preferences .= html_writer::tag('li', html_writer::link($branchurl, $branchlabel));
        }
        //if (has_capability('moodle/user:editownmessageprofile', $context)) {
        if (is_siteadmin()) { // hanna 21/9/15
            $branchlabel = '<em>' . $this->getfontawesomemarkup('comments') . get_string('message', 'message') . '</em>';
            $branchurl = new moodle_url('/message/edit.php', array('id' => $USER->id));
            $preferences .= html_writer::tag('li', html_writer::link($branchurl, $branchlabel));
        }
        if ($CFG->enableblogs) {
            $branchlabel = '<em>' . $this->getfontawesomemarkup('rss-square') . get_string('blog', 'blog') . '</em>';
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
        $classes[] = 'fa fa-' . $theicon;
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

