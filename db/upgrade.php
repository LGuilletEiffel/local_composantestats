<?php

// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Upgrade DB File.
 *
 * @package     local_composantestats
 * @copyright   2024 Laurent Guillet <laurent.guillet@univ-eiffel.fr>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

function xmldb_local_composantestats_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2024091100) {

        // Define table local_cronstats to be created.
        $table = new xmldb_table('local_cronstats');

        // Adding fields to table local_cronstats.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('composanteid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('modid', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('statsviewed', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('statsused', XMLDB_TYPE_INTEGER, '20', null, null, null, null);

        // Adding keys to table local_cronstats.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_cronstats.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Composantestats savepoint reached.
        upgrade_plugin_savepoint(true, 2024091100, 'local', 'composantestats');
    }
    
    if ($oldversion < 2024091200) {

        // Define field usagecount to be added to local_cronstats.
        $table = new xmldb_table('local_cronstats');
        $field = new xmldb_field('usagecount', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null, 'modid');

        // Conditionally launch add field usagecount.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Composantestats savepoint reached.
        upgrade_plugin_savepoint(true, 2024091200, 'local', 'composantestats');
    }
    
    return true;
}