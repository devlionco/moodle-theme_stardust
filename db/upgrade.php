<?php
 
function xmldb_theme_stardust_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();
 
    if ($oldversion < 2018091901) {

        // Define table theme_stardust_messages to be created.
        $table = new xmldb_table('theme_stardust_messages');
        
        // Adding fields to table theme_stardust_messages.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('message', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
        $table->add_field('userfrom', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timevalidbefore', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        
        // Adding keys to table theme_stardust_messages.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        
        // Conditionally launch create table for theme_stardust_messages.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
        
        // Stardust savepoint reached.
        upgrade_plugin_savepoint(true, 2018091901, 'theme', 'stardust');
        }

        if ($oldversion < 2018092802) {

            // Define field status to be added to theme_stardust_messages.
            $table = new xmldb_table('theme_stardust_messages');
            $field = new xmldb_field('status', XMLDB_TYPE_INTEGER, '1', null, null, null, '0', 'timevalidbefore');
            // Conditionally launch add field status.
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }

            // Define field timestatusupdate to be added to theme_stardust_messages.
            $table = new xmldb_table('theme_stardust_messages');
            $field = new xmldb_field('timestatusupdate', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'status');
            // Conditionally launch add field timestatusupdate.
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
    
            // Stardust savepoint reached.
            upgrade_plugin_savepoint(true, 2018092802, 'theme', 'stardust');
        }
        
    $result = TRUE;
 
    return $result;
}
?>