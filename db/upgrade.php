<?php
function xmldb_theme_stardust_upgrade($oldversion) {
    global $CFG, $DB;

    if ($oldversion < 2019052020) {

      // Define table theme_stardust_reminders to be created.
      $table = new xmldb_table('theme_stardust_reminders');

      // Adding fields to table theme_stardust_reminders.
      $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
      $table->add_field('text', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL, null, null);
      $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
      $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
      $table->add_field('timeremind', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

      // Adding keys to table theme_stardust_reminders.
      $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

      // Conditionally launch create table for theme_stardust_reminders.
      if (!$dbman->table_exists($table)) {
          $dbman->create_table($table);
      }

      // Stardust savepoint reached.
      upgrade_plugin_savepoint(true, 2019052000, 'theme', 'stardust');
    }

    $result = TRUE;

    return $result;
}
?>
