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
require_once($CFG->dirroot.'/mod/kronossandvm/vmcourses_csv_form.php');
require_login();

$PAGE->set_pagelayout('admin');
$PAGE->set_url('/mod/kronossandvm/vmcourses_csv.php');

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('vmcourseslist', 'mod_kronossandvm'));
$PAGE->set_heading(get_string('vmcourseslist', 'mod_kronossandvm'));

if (!kronossandvm_canconfig()) {
    print_error('nopermissiontoshow');
}

echo $OUTPUT->header();

echo html_writer::tag('h3', get_string('csvmanager', 'mod_kronossandvm'));

echo html_writer::link('vmcourses_csv_download.php', get_string('csvdownloadcsv', 'mod_kronossandvm'));
echo html_writer::empty_tag('br');
echo html_writer::empty_tag('br');
$mform = new vmcourses_csv_form($PAGE->url);
$formdata = $mform->get_data();
if (empty($formdata)) {
    $mform->display();
} else {
    // Process upload.
    $content = $mform->get_file_content('csvfile');
    $tempfile = tempnam(make_temp_directory('/csvimport'), 'tmp');
    if (!$fp = fopen($tempfile, 'w+b')) {
        $this->_error = get_string('csvcannotsavedata', 'error');
        @unlink($tempfile);
        return false;
    }
    fwrite($fp, $content);
    fseek($fp, 0);

    $columns = array("action", "id", "otcourseno", "coursename", "imageid",
            "imagename", "vmwareno", "isactive", "imagesource", "imagetype",
            "tusername", "tpassword");
    $requiredcolumns = array("otcourseno", "coursename", "imageid", "imagename", "vmwareno",
            "isactive", "imagesource", "imagetype");

    $redoptions = array('style' => 'color: red');
    $row = fgetcsv($fp);
    $total = count($columns);
    for ($i = 0; $i < $total; $i++) {
        if ($row[$i] != $columns[$i]) {
            echo html_writer::tag('h3', get_string('csvinvalidcolumnformat', 'mod_kronossandvm'), $redoptions);
            $mform->display();
            echo $OUTPUT->footer();
            exit;
        }
    }
    $count = 0;
    while ($row = fgetcsv($fp)) {
        $handled = false;
        if ($row[0] == 'delete' && is_numeric($row[1])) {
            $handled = true;
            $record = $DB->get_record('vm_courses', array('id' => $row[1]));
            if (empty($record)) {
                echo html_writer::tag('p', get_string('csvrecordmissingdelete', 'mod_kronossandvm', $row[1]), $redoptions);
            } else {
                // Do check on instances.
                $sql = 'SELECT c.id, c.fullname
                          FROM {kronossandvm} k, {course} c
                         WHERE c.id = k.course
                               AND k.otcourseid = ?
                         LIMIT 1';
                $results = $DB->get_records_sql($sql, array($row[1]));
                if (empty($results)) {
                    $DB->delete_records('vm_courses', array('id' => $row[1]));
                    echo html_writer::tag('p', get_string('csvdelete', 'mod_kronossandvm', $record));
                } else {
                    echo html_writer::tag('p', get_string('csvdeletehasinstance', 'mod_kronossandvm', $record), $redoptions);
                }
            }
        }
        // Update record.
        if (($row[0] == 'update' || $row[0] == '') && is_numeric($row[1])) {
            $handled = true;
            $record = $DB->get_record('vm_courses', array('id' => $row[1]));
            if (empty($record)) {
                $message = kronossandvm_csv2message($row);
                $message->id = $row[1];
                $message->line = $count;
                echo html_writer::tag('p', get_string('csvrecordmissingupdate', 'mod_kronossandvm', $message), $redoptions);
            } else {
                $result = kronossandvm_csv2object($columns, $requiredcolumns, $row);
                if (is_object($result)) {
                    $DB->update_record('vm_courses', $result);
                    echo html_writer::tag('p', get_string('csvupdate', 'mod_kronossandvm', $result));
                } else {
                    echo $result;
                    $count++;
                    continue;
                }
            }
        }
        // Create record if action is blank and id column is empty.
        if (($row[0] == 'create' || $row[0] == '') && (!is_numeric($row[1]) || empty($row[1]))) {
            $handled = true;
            $result = kronossandvm_csv2object($columns, $requiredcolumns, $row);
            if (!is_object($result)) {
                echo $result;
                $count++;
                continue;
            }
            $i = 0;
            $result->timemodified = time();
            $result->timecreated = time();
            $newid = $DB->insert_record('vm_courses', $result);
            $result->id = $newid;
            echo html_writer::tag('p', get_string('csvcreate', 'mod_kronossandvm', $result));
        }
        if (!$handled) {
            // Record did not update, delete or insert for some reason.
            $message = kronossandvm_csv2message($row);
            echo html_writer::tag('p', get_string('csvrecorderror', 'mod_kronossandvm', $message), $redoptions);
        }
        $count++;
    }
    echo html_writer::tag('p', get_string('csvfileprocessed', 'mod_kronossandvm', $count));
    fclose($fp);
}
echo $OUTPUT->footer();
