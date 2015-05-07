<?php
/**
 * ELIS(TM): Enterprise Learning Intelligence Suite
 * Copyright (C) 2008-2015 Remote Learner.net Inc (http://www.remote-learner.net)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    local_elisreports
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2008-2015 Remote-Learner.net Inc (http://www.remote-learner.net)
 *
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Standard Moodle upgrade function defintion
 * @param int $oldversion the timestamp of the old version of plugin
 * @return bool true on success, false otherwise
 */
function xmldb_local_elisreports_upgrade($oldversion = 0) {
    global $CFG, $DB;
    $dbman = $DB->get_manager();
    $result = true;

    if ($result && $oldversion < 2014082504) {
        $table = new xmldb_table('local_elisreports_links');
        if (!$dbman->table_exists($table)) {
            // ELIS-9040: create new report attachment links table
            $dbman->install_one_table_from_xmldb_file($CFG->dirroot.'/local/elisreports/db/install.xml', 'local_elisreports_links');
        }
        upgrade_plugin_savepoint($result, 2014082504, 'local', 'elisreports');
    }

    return $result;
}
