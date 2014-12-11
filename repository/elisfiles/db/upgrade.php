<?php
/**
 * ELIS(TM): Enterprise Learning Intelligence Suite
 * Copyright (C) 2014 onwards Remote-Learner.net Inc (http://www.remote-learner.net)
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
 * @package    repository_elisfiles
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2008-2014 Remote-Learner.net Inc (http://www.remote-learner.net)
 *
 */

function xmldb_repository_elisfiles_upgrade($oldversion = 0) {
    global $CFG, $DB;
    $result = true;

    if ($result && $oldversion < 2014082501) {
        $DB->delete_records('events_handlers', array('component' => 'repository_elis_files'));
        upgrade_plugin_savepoint($result, 2014082501, 'repository', 'elisfiles');
    }

    if ($result && $oldversion < 2014082502) {
        require_once($CFG->dirroot.'/repository/lib.php');
        $rlalfresco = 0;
        if (method_exists('repository', 'get_rl_version')) {
            $rlalfresco = repository::get_rl_version();
        }
        if ($rlalfresco >= 2014082502) {
            upgrade_plugin_savepoint($result, 2014082502, 'repository', 'elisfiles');
        } else {
            global $OUTPUT;
            echo $OUTPUT->box('The installed version of core repository is not compatible with this version of ELIS Files.'.
                    '<br/>Please update the core Moodle code base for this site.', 'errorbox');
            $result = false;
        }
    }

    return $result;
}
