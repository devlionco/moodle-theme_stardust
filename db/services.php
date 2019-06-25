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
 * Core external functions and service definitions.
 *
 * The functions and services defined on this file are
 * processed and registered into the Moodle DB after any
 * install or upgrade operation. All plugins support this.
 *
 * For more information, take a look to the documentation available:
 *     - Webservices API: {@link http://docs.moodle.org/dev/Web_services_API}
 *     - External API: {@link http://docs.moodle.org/dev/External_functions_API}
 *     - Upgrade API: {@link http://docs.moodle.org/dev/Upgrade_API}
 *
 * @package    theme_stardust
 * @category   webservice
 * @copyright  2009 Petr Skodak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$functions = array(
    'theme_stardust_send_mail_to_teacher' => array(
        'classname'     => 'theme_stardust_external',
        'methodname'    => 'send_mail_to_teacher',
        'classpath'     => 'theme/stardust/externallib.php',
        'description'   => 'Send message to the teacher',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => true,
        //'capabilities'  => 'mod/assign:grade',
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
    'theme_stardust_get_reminders' => array(
        'classname'     => 'theme_stardust_external',
        'methodname'    => 'get_reminders',
        'classpath'     => 'theme/stardust/externallib.php',
        'description'   => 'Get all user reminders',
        'type'          => 'read',
        'ajax'          => true,
        'loginrequired' => true,
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
    'theme_stardust_add_reminder' => array(
        'classname'     => 'theme_stardust_external',
        'methodname'    => 'add_reminder',
        'classpath'     => 'theme/stardust/externallib.php',
        'description'   => 'Add reminder',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => true,
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
    'theme_stardust_del_reminder' => array(
        'classname'     => 'theme_stardust_external',
        'methodname'    => 'del_reminder',
        'classpath'     => 'theme/stardust/externallib.php',
        'description'   => 'Delete reminder',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => true,
        'services'      => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    ),
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
    'Send message to the teacher' => array(
        'functions' => array ('theme_stardust_send_mail_to_teacher'),
        'restrictedusers' => 0,
        'enabled'=>1,
    ),
    'Reminder services' => array(
        'functions' => array (
            'theme_stardust_get_reminders',
            'theme_stardust_add_reminder',
            'theme_stardust_del_reminder',
        ),
        'enabled'=>1,
        'shortname'=>'reminders'
    )
);
