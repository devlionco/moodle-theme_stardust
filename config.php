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
 * For full information about creating Moodle themes, see:
 * http://docs.moodle.org/dev/Themes_2.0
 *
 * @package   theme_clean
 * @copyright 2013 Moodle, moodle.org
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$THEME->name = 'stardust';

$THEME->doctype = 'html5';
$THEME->parents = array('fordson', 'boost');
// $THEME->sheets = array('custom', 'stardust');
$THEME->sheets = array('davidson');
// $THEME->scss = 'style';
$THEME->scss = function($theme) {
    return theme_boost_get_main_scss_content($theme);
};
$THEME->yuicssmodules = array();
$THEME->enable_dock = true;
$THEME->editor_sheets = array();

$THEME->layouts = [
    // The site home page.
    'frontpage' => array(
        'file' => 'frontpage.php',
        'regions' => array('side-pre', 'fp-a', 'fp-b', 'fp-c'),
        'defaultregion' => 'side-pre',
        'options' => array('nonavbar' => true, 'langmenu' => true),
    ),
    // My dashboard page.
    'mydashboard' => array(
        'file' => 'mydashboard.php',
        'regions' => array('fp-a', 'fp-b', 'fp-c'),
        'defaultregion' => 'fp-c',
        // 'regions' => array('side-pre'),
        // 'defaultregion' => 'side-pre',
        'options' => array('nonavbar' => true, 'langmenu' => true),
    ),
    // My public page.
    'mypublic' => array(
        'file' => 'mypublic.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
        'options' => array('nonavbar' => true, 'langmenu' => true),
    ),
    // Main course page.
    'course' => array(
        'file' => 'course.php',
        'regions' => ['fp-a', 'fp-b', 'fp-c'],
        'defaultregion' => 'fp-c',
        // 'options' => array('nonavbar' => true, 'langmenu' => true),
    ),

    'incourse' => array(
        'file' => 'incourse.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
    'quizattempt' => array(
        'file' => 'quizattempt.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
    'coursecategory' => array(
        'file' => 'columns2.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
    // Server administration scripts.
    'admin' => array(
        'file' => 'columns2.php',
        'regions' => array('side-pre'),
        'defaultregion' => 'side-pre',
    ),
];

$THEME->rendererfactory = 'theme_overridden_renderer_factory';
$THEME->csspostprocess = 'theme_stardust_process_css';

// SG - ugly hack to avoid warnings
// TODO - need to import settings from Fordson correctly in settings.php or here, because now settings are saved under theme_fordson namesapce in DB at config_plugins table
$THEME->settings->blockdisplay = null;
