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
 * Kronos HTML block.  This file processes AJAX actions and returns JSON for the Kronos HTML User Set auto-complete.
 *
 * @package    block_kronoshtml
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2014 Remote Learner.net Inc http://www.remote-learner.net
 */

define('AJAX_SCRIPT', true);

require('../../config.php');

$contextid = required_param('blockcontext', PARAM_INT);
$query = required_param('usrset', PARAM_RAW);
$query = preg_replace('/[^A-Za-z0-9_-\s]/i', '', $query);
$query = trim($query);
$callback = optional_param('callback', '', PARAM_TEXT);

$PAGE->set_url(new moodle_url('/blocks/kronoshtml/ajax.php', array('usrset' => $query)));

require_login(null, false);
require_capability('block/kronoshtml:selectuserset', context_block::instance($contextid));

$like = $DB->sql_like('name', '?', false);
$sql = "SELECT *
          FROM {local_elisprogram_uset}
         WHERE {$like}
      ORDER BY name ASC
         LIMIT 50";
$param = array('%'.$query.'%');
$records = $DB->get_recordset_sql($sql, $param);
$formattedresult = array('result' => array());

foreach ($records as $record) {
    $formattedresult['result'][] = $record;
}

$records->close();

echo $callback.'('.json_encode($formattedresult).')';
die();