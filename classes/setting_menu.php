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
 * @package     theme_stardust
 * @copyright   2019 Devlion <info@devlion.co>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * @package   theme_stardust
 * @copyright   2019 Devlion <info@devlion.co>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_stardust_setting_menu {

    private $categoryid = 0;
    private $renderablelinks = array();
    private $statusdefault = false;
    private $linkskeys = array();
    private $permission = 'student';
    private $typeurl = '';

    public function __construct() {
        global $CFG, $PAGE;

        $this->get_permission();
        $this->get_type_url();

        if ($this->permission == 'admin' || empty($this->typeurl)) {
            $this->statusdefault = true;
            if (!empty($this->course_links())) {
                $this->renderablelinks = $this->course_links();
            }
            if (!empty($this->quiz_links())) {
                $this->renderablelinks = $this->quiz_links();
            }
        } else {
            switch ($this->typeurl) {
                case 'course_view':
                    $this->renderablelinks = $this->create_menu_by_user($this->course_links(), $this->course_defineded_links());
                    break;
                case 'quiz_view':
                    $this->renderablelinks = $this->create_menu_by_user($this->quiz_links(), $this->quiz_defineded_links());
                    break;
                case 'quiz_edit':
                    $this->renderablelinks = $this->create_menu_by_user($this->quiz_links(), $this->quiz_defineded_links());
                    break;
                case 'quiz_report':
                    $this->renderablelinks = $this->create_menu_by_user($this->quiz_links(), $this->quiz_defineded_links());
                    break;
                case 'quiz_attempt':
                    //$this->renderablelinks = $this->create_menu_by_user($this->quiz_links(), $this->quiz_defineded_links());
                    $this->renderablelinks = $this->create_menu_by_user($this->course_links(), $this->course_defineded_links());
                    break;
                case 'category_index':
                $this->renderablelinks = $this->create_menu_by_user($this->category_links(), $this->category_defineded_links());
                break;
                case 'user_index':
                    $this->renderablelinks = $this->create_menu_by_user($this->user_links(), $this->user_defineded_links());
                    break;

            }
        }
    }

    public function get_permission() {
        global $CFG, $PAGE, $COURSE, $USER, $DB;

        if($COURSE->category) $this->categoryid = $COURSE->category;
        else{
            $this->categoryid = optional_param('categoryid', '0', PARAM_INT);
        }

        //Is permission admin
        if(is_siteadmin()){
            $this->permission = 'admin';
            return 'admin';
        }

        //Is permission category
        if(can_edit_in_category($this->categoryid)){
            $this->permission = 'admincategory';
            return 'admincategory';
        }

        //Is permission teacher
        $context = context_course::instance($COURSE->id);
        if(has_capability('moodle/course:update', $context)) {
            $this->permission = 'teacher';
            return 'teacher';
        }

        return '';
    }

    public function get_type_url() {
        global $CFG, $PAGE;

        if(strpos($PAGE->url, '/course/view.php')){
            $this->typeurl = 'course_view';
            return 'course_view';
        }

        if(strpos($PAGE->url, '/mod/quiz/view.php')){
            $this->typeurl = 'quiz_view';
            return 'quiz_view';
        }

        if(strpos($PAGE->url, '/mod/quiz/edit.php')){
            $this->typeurl = 'quiz_edit';
            return 'quiz_edit';
        }

        if(strpos($PAGE->url, '/mod/quiz/report.php')){
            $this->typeurl = 'quiz_report';
            return 'quiz_report';
        }

        if(strpos($PAGE->url, '/mod/quiz/attempt.php')){
            $this->typeurl = 'quiz_attempt';
            return 'quiz_attempt';
        }

        if(strpos($PAGE->url, '/course/index.php')){
            $this->typeurl = 'category_index';
            return 'category_index';
        }

        if(strpos($PAGE->url, '/user/index.php')){
            $this->typeurl = 'user_index';
            return 'user_index';
        }

        return '';
    }

    public function create_menu_by_user($alllinks, $define) {
        $result = array();
        if(!empty($define[$this->permission])){

            if(isset($define[$this->permission][$this->typeurl])) $arr = $define[$this->permission][$this->typeurl];
            else $arr = $define[$this->permission];

            foreach($arr as $item){
                if(isset($alllinks[$item])) $result[] = $alllinks[$item];
            }
        }

        return $result;
    }

    public function get_menu() {
        $menu = new action_menu();
        if(!empty($this->renderablelinks)){
            foreach($this->renderablelinks as $item){
                if ($item['icon'] == "trash") {
                    $item['icon'] = "i/trash";
                }
                $link = $this->create_link($item);
                $menu->add_secondary_action($link);
                $menu->set_owner_selector('editsettings');
            }
        }
        return $menu;
    }

    private function create_link($link){
        return new action_link($link['url'], $link['text'], null, null, new pix_icon($link['icon'], $link['text']));
    }

    private function unique_action_name($action){
        global $CFG;

        $str = str_replace($CFG->wwwroot, '', $action->url->__toString());
        $ar = explode('?', $str);

        $path = str_replace(array('.php'), '', $ar[0]);
        $path = str_replace(array('/'), '-', $path);
        $path = ltrim($path, '-');

        //Check params
        $arr = array();
        foreach($action->url->params() as $key => $value){
            if(is_numeric($value) || $value == sesskey()) continue;
            $arr[] = $key;
            $arr[] = $value;
        }

        $params = implode('-', $arr);
        if(!empty($params)) $params = '-'.$params;

        $name = $path.$params;
        return $name;
    }

    private function course_links(){
        global $CFG, $PAGE, $USER, $COURSE, $DB;

        $generatemenu = array();
        $basicmenu = $this->get_header_settings_menu();

        if(!empty($basicmenu->get_secondary_actions())) {
            foreach ($basicmenu->get_secondary_actions() as $key => $action) {
                $tmp = array();
                $tmp['text'] = $action->text;
                $tmp['url'] = $action->url;
                $tmp['icon'] = $action->icon->pix;

                $name = $this->unique_action_name($action);
                if(isset($generatemenu[$name])) $name = $name.'-repeat';
                $generatemenu[$name] = $tmp;
            }

            if($this->statusdefault) return $generatemenu;

            $addelements = array(
                'user-index' => array(
                    'text' => get_string('participants'),
                    'url' => new moodle_url('/user/index.php', array('id' => $PAGE->course->id)),
                    'icon' => 'i/users_menu'
                ),
                'grade-report-index' => array(
                    'text' => get_string('grades'),
                    'url' => new moodle_url('/grade/report/index.php', array('id' => $PAGE->course->id)),
                    'icon' => 'i/report'
                ),
            );

            // duplicate course item
            $duplicateelement = array();
            $availablemods = core_plugin_manager::instance()->get_plugins_of_type('local');
            $appsupportedmodules = array('courseduplicate');
            foreach ($availablemods as $mod) {
                if (in_array($mod->name, $appsupportedmodules)) {
                    require_once($CFG->dirroot.'/local/courseduplicate/lib.php');
                    if (!local_courseduplicate_checkprogress($PAGE->course, $USER->id)) {
                        $duplicateelement = array(
                            'duplicate-index' => array(
                                'text' => get_string('menu_node', 'local_courseduplicate'),
                                'url' => new moodle_url('/course/view.php', array('id' => $PAGE->course->id, 'popupcopycourse' => 1)),
                                'icon' => 'i/settings'
                            ),
                        );
                    }
                    break;
                }
            }

            $generatemenu = array_merge($generatemenu, $addelements, $duplicateelement);
        }

        //Student
        if($this->permission == 'student' && $this->typeurl == 'course_view'){
            $addelements = array(
                'grade-report-index' => array(
                    'text' => get_string('grades'),
                    'url' => new moodle_url('/grade/report/index.php', array('id' => $PAGE->course->id)),
                    'icon' => 'i/report'
                ),
            );

            $generatemenu = array_merge($generatemenu, $addelements);
        }

        return $generatemenu;
    }


    private function quiz_links(){
        global $CFG, $PAGE, $USER, $COURSE;

        $generatemenu = array();
        $basicmenu = $this->get_header_region_settings_menu();

        if(!empty($basicmenu->get_secondary_actions())) {
            foreach ($basicmenu->get_secondary_actions() as $key => $action) {
                $tmp = array();
                $tmp['text'] = $action->text;
                $tmp['url'] = $action->url;
                $tmp['icon'] = $action->icon->pix;

                $name = $this->unique_action_name($action);
                if(isset($generatemenu[$name])) $name = $name.'-repeat';
                $generatemenu[$name] = $tmp;
            }

            if($this->statusdefault) return $generatemenu;

            $addelements = array(
            );

            $generatemenu = array_merge($generatemenu, $addelements);
        }

        return $generatemenu;
    }

    private function category_links(){
        global $CFG, $PAGE, $USER, $COURSE;

        $generatemenu = array();
        $basicmenu = $this->get_header_region_settings_menu();

        if(!empty($basicmenu->get_secondary_actions())) {
            foreach ($basicmenu->get_secondary_actions() as $key => $action) {
                $tmp = array();
                $tmp['text'] = $action->text;
                $tmp['url'] = $action->url;
                $tmp['icon'] = $action->icon->pix;

                $name = $this->unique_action_name($action);
                if(isset($generatemenu[$name])) $name = $name.'-repeat';
                $generatemenu[$name] = $tmp;
            }

            if($this->statusdefault) return $generatemenu;

            $addelements = array(
            );

            $generatemenu = array_merge($generatemenu, $addelements);
        }

        return $generatemenu;
    }

    private function user_links(){
        global $CFG, $PAGE, $USER, $COURSE;

        $generatemenu = array();
        $basicmenu = $this->get_header_region_settings_menu();

        if(!empty($basicmenu->get_secondary_actions())) {
            foreach ($basicmenu->get_secondary_actions() as $key => $action) {
                $tmp = array();
                $tmp['text'] = $action->text;
                $tmp['url'] = $action->url;
                $tmp['icon'] = $action->icon->pix;

                $name = $this->unique_action_name($action);
                if(isset($generatemenu[$name])) $name = $name.'-repeat';
                $generatemenu[$name] = $tmp;
            }

            if($this->statusdefault) return $generatemenu;

            $addelements = array(
            );

            $generatemenu = array_merge($generatemenu, $addelements);
        }

        return $generatemenu;
    }

    private function course_defineded_links(){
        return array(
            'admincategory' => array(
                'course-edit',
                'course-view-edit-on',
                'course-completion',
                'enrol-manual-unenrolself',
                'filter-manage',
                'grade-edit-tree-index',
                'backup-backup',
                'backup-restorefile',
                'backup-import',
                'course-publish-index',
                'course-reset',
                'course-admin'
            ),
            'teacher' => array(
                'course-edit',
                'course-admin',
                'user-index',
                'grade-report-index',
                'duplicate-index'
            ),
            'student' => array(
                'grade-report-index'
            )
        );
    }

    private function quiz_defineded_links(){
        return array(
            'admincategory' => array(
                'course-modedit',
                'mod-quiz-overrides-mode-group',
                'mod-quiz-overrides-mode-user',
                'mod-quiz-edit',
                'mod-quiz-startattempt',
                'mod-quiz-report-mode-overview',
                'mod-quiz-report-mode-teacheroverview',
                'mod-quiz-report-mode-responses',
                'mod-quiz-report-mode-statistics',
                'mod-quiz-report-mode-grading',
                'local-estimate-setestimatequiz',
                'admin-roles-assign',
                'admin-roles-permissions',
                'admin-roles-check',
                'filter-manage',
                'report-log-index',
                'backup-backup',
                'backup-restorefile',
                'question-edit',
                'question-edit-repeat',
                'question-category',
                'question-import',
                'question-export',
                'local-purgequestioncategory-category',
                'local-metadata-index-action-moduledata',
                'local-weizman_essay_question-showtable',
                'local-weizman_export_quiz-download_report_quiz'
            ),
            'teacher' => array(
                'quiz_view' => array(
                    'course-modedit',
                    'mod-quiz-edit',
                    'mod-quiz-startattempt',
                    'mod-quiz-report-mode-teacheroverview',
                    'mod-quiz-report-mode-responses',
                    'mod-quiz-report-mode-statistics',
                    'mod-quiz-report-mode-grading',
                    'local-weizman_essay_question-showtable',
                    'local-weizman_export_quiz-download_report_quiz',
                ),
                'quiz_report' => array(
                    'course-modedit',
                    'mod-quiz-overrides-mode-group',
                    'mod-quiz-overrides-mode-user',
                    'mod-quiz-edit',
                    'mod-quiz-startattempt',
                    'mod-quiz-report-mode-teacheroverview',
                    'mod-quiz-report-mode-responses',
                    'mod-quiz-report-mode-statistics',
                    'mod-quiz-report-mode-grading',
                    'admin-roles-assign',
                    'local-weizman_essay_question-showtable',
                    'local-weizman_export_quiz-download_report_quiz'
                ),
                'quiz_attempt' => array(
                    'course-modedit',
                    'mod-quiz-overrides-mode-group',
                    'mod-quiz-overrides-mode-user',
                    'mod-quiz-edit',
                    'mod-quiz-startattempt',
                    'mod-quiz-report-mode-teacheroverview',
                    'mod-quiz-report-mode-responses',
                    'mod-quiz-report-mode-statistics',
                    'mod-quiz-report-mode-grading',
                    'admin-roles-assign',
                    'local-weizman_essay_question-showtable',
                    'local-weizman_export_quiz-download_report_quiz'
                )
            ),
            'student' => array(
                'quiz_view' => array(
                ),
                'quiz_report' => array(
                ),
                'quiz_attempt' => array(
                )

            )
        );
    }

    private function category_defineded_links(){
        return array(
            'admincategory' => array(
                'course-management',
            ),
            'teacher' => array(
            ),
            'student' => array(
            )
        );
    }

    private function user_defineded_links(){
        return array(
            'admincategory' => array(
                'enrol-users',
                'enrol-instances',
                'enrol-editinstance-type-manual',
                'group-index',
                'admin-roles-permissions',
                'admin-roles-check',
                'enrol-otherusers',
                'blocks-configurable_reports-viewreport',
            ),
            'teacher' => array(
                'enrol-users',
                'enrol-instances',
                'group-index',
            ),
            'student' => array(
            )
        );
    }

//////////////////////////////////////////////////////////////////////////////////////////////////////////
    public function get_header_region_settings_menu() {
        global $CFG, $PAGE;

        $context = $PAGE->context;
        $menu = new action_menu();

        if ($context->contextlevel == CONTEXT_MODULE) {

            $PAGE->navigation->initialise();
            $node = $PAGE->navigation->find_active_node();
            $buildmenu = false;
            // If the settings menu has been forced then show the menu.
            if ($PAGE->is_settings_menu_forced()) {
                $buildmenu = true;
            } else if (!empty($node) && ($node->type == navigation_node::TYPE_ACTIVITY ||
                    $node->type == navigation_node::TYPE_RESOURCE)) {

                $items = $PAGE->navbar->get_items();
                $navbarnode = end($items);
                // We only want to show the menu on the first page of the activity. This means
                // the breadcrumb has no additional nodes.

                //TODO improve menu views for the next versions
                if ($navbarnode->key=='quiz_report_overview'){
                    $buildmenu = true;
                }else
                    if ($navbarnode && ($navbarnode->key === $node->key && $navbarnode->type == $node->type)) {
                        $buildmenu = true;
                    }
            }
            if ($buildmenu) {
                // Get the course admin node from the settings navigation.
                $node = $PAGE->settingsnav->find('modulesettings', navigation_node::TYPE_SETTING);
                if ($node) {
                    // Build an action menu based on the visible nodes from this navigation tree.
                    $this->build_action_menu_from_navigation($menu, $node);
                }
            }

        } else if ($context->contextlevel == CONTEXT_COURSECAT) {
            // For course category context, show category settings menu, if we're on the course category page.
            if ($PAGE->pagetype === 'course-index-category') {
                $node = $PAGE->settingsnav->find('categorysettings', navigation_node::TYPE_CONTAINER);
                if ($node) {
                    // Build an action menu based on the visible nodes from this navigation tree.
                    $this->build_action_menu_from_navigation($menu, $node);
                }
            }

        } else {
            $items = $PAGE->navbar->get_items();
            $navbarnode = end($items);

            if ($navbarnode && ($navbarnode->key === 'participants')) {
                $node = $PAGE->settingsnav->find('users', navigation_node::TYPE_CONTAINER);
                if ($node) {
                    // Build an action menu based on the visible nodes from this navigation tree.
                    $this->build_action_menu_from_navigation($menu, $node);
                }

            }
        }
        return $menu;
    }

    public function get_header_settings_menu() {
        global $CFG, $PAGE;

        $context = $PAGE->context;
        $menu = new action_menu();

        $items = $PAGE->navbar->get_items();
        $currentnode = end($items);

        $showcoursemenu = false;
        $showfrontpagemenu = false;
        $showusermenu = false;

        // We are on the course home page.
        if (($context->contextlevel == CONTEXT_COURSE) &&
            !empty($currentnode) &&
            ($currentnode->type == navigation_node::TYPE_COURSE || $currentnode->type == navigation_node::TYPE_SECTION)) {
            $showcoursemenu = true;
        }

        if ($PAGE->is_settings_menu_forced()) {
            $showcoursemenu = true;
        }

        $courseformat = course_get_format($PAGE->course);
        // This is a single activity course format, always show the course menu on the activity main page.
        if ($context->contextlevel == CONTEXT_MODULE) {

            $PAGE->navigation->initialise();
            $activenode = $PAGE->navigation->find_active_node();
            // If the settings menu has been forced then show the menu.
            if ($PAGE->is_settings_menu_forced()) {
                $showcoursemenu = true;
            } else if (!empty($activenode) && ($activenode->type == navigation_node::TYPE_ACTIVITY ||
                    $activenode->type == navigation_node::TYPE_RESOURCE)) {

                $showcoursemenu = true;
                // We only want to show the menu on the first page of the activity. This means
                // the breadcrumb has no additional nodes.
                if ($currentnode && ($currentnode->key == $activenode->key && $currentnode->type == $activenode->type)) {
                    $showcoursemenu = true;
                }
            }
        }

        // This is the site front page.
        if ($context->contextlevel == CONTEXT_COURSE &&
            !empty($currentnode) &&
            $currentnode->key === 'home') {
            $showfrontpagemenu = true;
        }

        // This is the user profile page.
        if ($context->contextlevel == CONTEXT_USER &&
            !empty($currentnode) &&
            ($currentnode->key === 'myprofile')) {
            $showusermenu = true;
        }


        if ($showfrontpagemenu) {
            $settingsnode = $PAGE->settingsnav->find('frontpage', navigation_node::TYPE_SETTING);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $skipped = $this->build_action_menu_from_navigation($menu, $settingsnode, false, true);

                // We only add a list to the full settings menu if we didn't include every node in the short menu.
                if ($skipped) {
                    $text = get_string('morenavigationlinks');
                    $url = new moodle_url('/course/admin.php', array('courseid' => $PAGE->course->id));
                    $link = new action_link($url, $text, null, null, new pix_icon('t/edit', $text));
                    $menu->add_secondary_action($link);
                }
            }
        } else if ($showcoursemenu) {
            $settingsnode = $PAGE->settingsnav->find('courseadmin', navigation_node::TYPE_COURSE);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $skipped = $this->build_action_menu_from_navigation($menu, $settingsnode, false, true);

                // We only add a list to the full settings menu if we didn't include every node in the short menu.
                if ($skipped) {
                    $text = get_string('morenavigationlinks');
                    $url = new moodle_url('/course/admin.php', array('courseid' => $PAGE->course->id));
                    $link = new action_link($url, $text, null, null, new pix_icon('t/edit', $text));
                    $menu->add_secondary_action($link);
                }
            }
        } else if ($showusermenu) {
            // Get the course admin node from the settings navigation.
            $settingsnode = $PAGE->settingsnav->find('useraccount', navigation_node::TYPE_CONTAINER);
            if ($settingsnode) {
                // Build an action menu based on the visible nodes from this navigation tree.
                $this->build_action_menu_from_navigation($menu, $settingsnode);
            }
        }

        return $menu;
    }

    private function build_action_menu_from_navigation(action_menu $menu,
                                                       navigation_node $node,
                                                       $indent = false,
                                                       $onlytopleafnodes = false) {
        $skipped = false;
        // Build an action menu based on the visible nodes from this navigation tree.
        foreach ($node->children as $menuitem) {
            if ($menuitem->display) {
                if ($onlytopleafnodes && $menuitem->children->count()) {
                    $skipped = true;
                    continue;
                }
                if ($menuitem->action) {
                    if ($menuitem->action instanceof action_link) {
                        $link = $menuitem->action;
                        // Give preference to setting icon over action icon.
                        if (!empty($menuitem->icon)) {
                            $link->icon = $menuitem->icon;
                        }
                    } else {
                        $link = new action_link($menuitem->action, $menuitem->text, null, null, $menuitem->icon);
                    }

                    $this->linkskeys[] = $menuitem->key;

                } else {
                    if ($onlytopleafnodes) {
                        $skipped = true;
                        continue;
                    }
                    $link = new action_link(new moodle_url('#'), $menuitem->text, null, ['disabled' => true], $menuitem->icon);

                    $this->linkskeys[] = $menuitem->key;
                }
                if ($indent) {
                    $link->add_class('m-l-1');
                }
                if (!empty($menuitem->classes)) {
                    $link->add_class(implode(" ", $menuitem->classes));
                }

                $menu->add_secondary_action($link);
                $skipped = $skipped || $this->build_action_menu_from_navigation($menu, $menuitem, true);
            }
        }
        return $skipped;
    }

}
