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
        
    $result = TRUE;
 
    return $result;
}
?>