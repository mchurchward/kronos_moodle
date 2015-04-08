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
 * Kronos virtual machine activity.  This file processes AJAX actions and returns JSON for the Kronos Virtual machine templates.
 *
 * @package    mod_kronossandvm
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2015 Remote Learner.net Inc http://www.remote-learner.net
 */

define('AJAX_SCRIPT', true);

require('../../config.php');

$query = required_param('q', PARAM_RAW);
$course = required_param('course', PARAM_INT);
$query = trim($query);
$callback = optional_param('callback', '', PARAM_TEXT);

$PAGE->set_url(new moodle_url('/mod/kronossandvm/ajax.php', array('q' => $query)));

require_login(null, false);
$context = context_course::instance($course);
require_capability('mod/kronossandvm:addinstance', $context);

$like = '';
$and = '';
$param = array();
foreach (array('otcourseno', 'coursename', 'imageid', 'imagename', 'vmwareno', 'imagesource', 'imagetype', 'tusername', 'tpassword') as $name) {
    $like .= $and.$DB->sql_like($name, '?', false);
    $param[] = '%'.$query.'%';
    $and = ' OR ';
}
$sql = "SELECT id, coursename name
          FROM {vm_courses}
         WHERE isactive = 1 AND ({$like})
      ORDER BY coursename ASC
         LIMIT 50";
$records = $DB->get_recordset_sql($sql, $param);
$formattedresult = array('result' => array());

foreach ($records as $record) {
    $formattedresult['result'][] = $record;
}

$records->close();

echo $callback.'('.json_encode($formattedresult).')';
die();
