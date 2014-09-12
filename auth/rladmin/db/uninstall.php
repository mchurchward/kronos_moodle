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
 * Uninstall for the RL Admin plugin.
 *
 * @package    auth_rladmin
 * @copyright  2013 Tim Gusak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_auth_rladmin_uninstall() {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    // Drop the rladmin database table.
    $table = new xmldb_table('rladmin');
    if ($dbman->table_exists($table)) {
        $dbman->drop_table($table);
    }

    if (empty($CFG->auth)) {
        $authsenabled = array();
    } else {
        $authsenabled = explode(',', $CFG->auth);
    }

    // Remove the rladmin entry from the config table.
    $key = array_search('rladmin', $authsenabled);
    if ($key !== false) {
        unset($authsenabled[$key]);
        set_config('auth', implode(',', $authsenabled));
    }

    return true;
}
