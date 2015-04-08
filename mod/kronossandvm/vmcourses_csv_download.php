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
 * Kronos virtual machine manager.
 *
 * @package    mod_kronossandvm
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2015 Remote Learner.net Inc http://www.remote-learner.net
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/mod/kronossandvm/lib.php');

require_login();

$context = context_system::instance();
$PAGE->set_context($context);

// Set type to csv and name the file to be downloaded vmtemplates.csv.
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=vmtemplates.csv');

if (!kronossandvm_canconfig()) {
    print_error('nopermissiontoshow');
}

$columns = array("action", "id", "otcourseno", "coursename", "imageid",
        "imagename", "vmwareno", "isactive", "imagesource", "imagetype",
        "tusername", "tpassword");
$records = $DB->get_records('vm_courses');
$data = array();
// Create a file pointer connected to the output stream.
$output = fopen('php://output', 'w');
fputcsv($output, $columns);
// Remove action column.
array_shift($columns);

// Output records using fputcsv to format into csv.
foreach ($records as $record) {
    $record = (array)$record;
    $csv = array("update");
    foreach ($columns as $name) {
        $csv[] = $record[$name];
    }
    fputcsv($output, $csv);
}

fclose($output);
