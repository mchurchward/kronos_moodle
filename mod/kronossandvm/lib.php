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
 * Kronos sandbox activity.
 *
 * @package    mod_kronossandvm
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2015 Remote Learner.net Inc http://www.remote-learner.net
 */

require_once($CFG->libdir.'/eventslib.php');

/**
 * Add instance.
 *
 * @param object $kronossandvm Activity to add.
 * @return int Id of added instance.
 */
function kronossandvm_add_instance($kronossandvm) {
    global $DB;
    $kronossandvm->timecreated  = time();
    $kronossandvm->timemodified = $kronossandvm->timecreated;
    $returnid = $DB->insert_record("kronossandvm", $kronossandvm);
    return $returnid;
}

/**
 * Update instance.
 *
 * @param object $kronossandvm Activity object.
 * @return bool success.
 */
function kronossandvm_update_instance($kronossandvm) {
    global $DB;
    $kronossandvm->timemodified = time();
    $kronossandvm->id           = $kronossandvm->instance;
    $DB->update_record("kronossandvm", $kronossandvm);
    return true;
}

/**
 * Update instance.
 *
 * @param int $id kronossandvm id
 * @return bool success
 */
function kronossandvm_delete_instance($id) {
    global $DB;
    if (!$kronossandvm = $DB->get_record('kronossandvm', array('id' => $id))) {
        return false;
    }

    if (!$cm = get_coursemodule_from_instance('kronossandvm', $id)) {
        return false;
    }

    if (!$context = context_module::instance($cm->id, IGNORE_MISSING)) {
        return false;
    }

    $DB->delete_records('kronossandvm', array('id' => $id));
    return true;
}

/**
 * Form for requesting virtual machine.
 *
 * @param int $instanceid Instance id of activity.
 * @return string HTML of request virtual machine form.
 */
function kronossandvm_get_request_form($instanceid) {
    $msg  = html_writer::start_tag('form', array('method' => 'post', 'action' => 'view.php'));
    $options = array('type' => 'submit', 'name' => 'submit', 'value' => get_string('requestsystem', 'kronossandvm'));
    $msg .= html_writer::empty_tag('input', $options);
    $msg .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'submitted', 'value' => 1));
    $msg .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'id', 'value' => $instanceid));
    return $msg;
}

/**
 * Form for updating status.
 *
 * @param int $instanceid Instance id of activity.
 * @return string HTML of request update form.
 */
function kronossandvm_get_update_form($instanceid) {
    $msg  = html_writer::start_tag('form', array('method' => 'post', 'action' => 'view.php'));
    $options = array('type' => 'submit', 'name' => 'submit', 'value' => get_string('updatestatus', 'kronossandvm'));
    $msg .= html_writer::empty_tag('input', $options);
    $msg .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'update', 'value' => 1));
    $msg .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'id', 'value' => $instanceid));
    return $msg;
}

/**
 * Check if user can request a virtual machine and return a form or a status message.
 *
 * @param object $context Page context.
 * @param int $instanceid Instance id of activity.
 * @return array Array containing int if request is allowed and string for message.
 */
function kronossandvm_get_message($context, $instanceid) {
    global $DB, $CFG, $USER;
    $today = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
    if (has_capability('mod/kronossandvm:employee', $context, $USER->id)) {
        $msg = get_string('restrictkronosemployee', 'kronossandvm');
        return array(0, $msg);
    }

    // System count per user per day.
    $sql = 'SELECT COUNT(*) FROM {vm_requests} r WHERE r.userid= ? AND r.requesttime > ?';
    $max = $CFG->mod_kronossandvm_requestsuserperday;
    if ($DB->count_records_sql($sql, array($USER->id, $today)) >= $max) {
        $obj = new stdClass();
        $obj->limit = get_string('one', 'kronossandvm');
        if ($CFG->mod_kronossandvm_requestsuserperday > 1) {
            $obj->limit = $CFG->mod_kronossandvm_requestsuserperday;
        }
        $msg = get_string('peruserrestriction', 'kronossandvm', $obj);
        return array(0, $msg);
    }

    // System count per solution per day.
    $sql = 'SELECT udf.id, ud.data
              FROM {user_info_data} ud, {user_info_field} udf
             WHERE ud.userid = ?
                   AND ud.fieldid = udf.id
                   AND udf.shortname = \'solutionid\'';
    $solutionid = $DB->get_record_sql($sql, array($USER->id));
    // If the student has not been assigned a soltuion id than do not allow access.
    if (empty($solutionid)) {
        $msg = get_string('missingsolutionid', 'kronossandvm');
        return array(0, $msg);
    }

    $sql = 'SELECT COUNT(*)
              FROM {vm_requests} r, {user_info_data} d
             WHERE d.userid = r.userid
                   AND d.fieldid = ?
                   AND d.data = ?
                   AND r.requesttime > ?';
    $max = $CFG->mod_kronossandvm_requestssolutionperday;
    $sql1 = 'SELECT r.*
               FROM {vm_requests} r, {user_info_data} d
              WHERE d.userid = r.userid
                    AND d.fieldid = ?
                    AND d.data = ?';
    $total = $DB->count_records_sql($sql, array($solutionid->id, $solutionid->data, $today));
    if ($total >= $max) {
        $msg = get_string('persolutionrestriction', 'kronossandvm', $CFG);
        return array(0, $msg);
    }

    // System count concurrently at any time.
    $sql = 'SELECT COUNT(*)
              FROM {vm_requests} r, {user_info_data} d
             WHERE d.userid = r.userid
                   AND r.isactive = 1
                   AND d.fieldid = ?
                   AND d.data = ?
                   AND r.requesttime > ?';
    $max = $CFG->mod_kronossandvm_requestsconcurrentpercustomer;
    $total = $DB->count_records_sql($sql, array($solutionid->id, $solutionid->data, $today));
    if ($total >= $max) {
        $msg = get_string('conpersolutionrestriction', 'kronossandvm', $CFG);
        return array(0, $msg);
    }

    // No issues found so returning form to request virtual machine.
    $msg = kronossandvm_get_request_form($instanceid);
    return array(1, $msg);
}

/**
 * Add a virtual machine request.
 *
 * @param object $context Context object.
 * @param object $kronossandvm Activity object.
 * @param object $vmrequest Request to add.
 * @return int Vmrequest id.
 */
function kronossandvm_add_vmrequest($context, $kronossandvm, $vmrequest) {
    global $DB;
    $vmrequest->requesttime = time();
    $params = array(
        'objectid' => $kronossandvm->id,
        'context' => $context
    );
    $event = \mod_kronossandvm\event\vmrequest_created::create($params);
    $event->trigger();
    return $DB->insert_record('vm_requests', $vmrequest);
}

/**
 * Build url to link to virtual machine.
 *
 * @param object $vmrequest Request to build url for.
 * @return string URL with tokens replaced with values.
 */
function kronossandvm_buildurl($vmrequest) {
    global $CFG;
    // Create link from template.
    $link = $CFG->mod_kronossandvm_requesturl;
    foreach ((array)$vmrequest as $name => $value) {
        $link = preg_replace('/\{'.$name.'\}/', urlencode($value), $link);
    }
    return $link;
}

/**
 * Checks to see if user can configure the virtual templates.
 *
 * @param object $context Context.
 * @return boolean True on has capability to edit virtual machine templates.
 */
function kronossandvm_canconfig($context = null) {
    global $USER;
    if ($context == null) {
        $context = context_system::instance();
    }
    if (has_capability('moodle/site:config', $context, $USER->id)) {
        return true;
    }
    return false;
}

/**
 * Format a csv table row for a message.
 *
 * @param array $row Array of items in csv row.
 * @return object stdClass object containing row field.
 */
function kronossandvm_csv2message($row) {
    $message = new stdClass();
    $message->row = '';
    $comma = '';
    foreach ($row as $item) {
        $message->row .= $comma.'"'.$item.'"';
        $comma = ',';
    }
    return $message;
}

/**
 * Convert and validate csv row into a object.
 *
 * @param array $columns Array of row columns.
 * @param array $requiredcolumns Array of required row columns.
 * @param array $row Array of items in csv row.
 * @return object|string stdClass object containing row field or error message.
 */
function kronossandvm_csv2object($columns, $requiredcolumns, $row) {
    global $DB;
    $redoptions = array('style' => 'color: red');
    // Check uniqueness of otcourseid.
    if (!empty($row[1]) && is_numeric($row[1])) {
        // Doing and update as id column has value.
        $sql = 'SELECT * FROM {vm_courses} WHERE otcourseno = ? AND id != ? LIMIT 1';
        $otherrecord = $DB->get_record_sql($sql, array($row[2], $row[1]));
    } else {
        // Doing create.
        $sql = 'SELECT * FROM {vm_courses} WHERE otcourseno = ? LIMIT 1';
        $otherrecord = $DB->get_record_sql($sql, array($row[2]));
    }
    if (!empty($otherrecord)) {
        return html_writer::tag('p', get_string('csvnonuniqueotcourseno', 'mod_kronossandvm', $otherrecord), $redoptions);
    }
    // Check uniqueness of course name.
    if (!empty($row[1]) && is_numeric($row[1])) {
        // Doing and update as id column has value.
        $sql = 'SELECT * FROM {vm_courses} WHERE coursename = ? AND id != ? LIMIT 1';
        $otherrecord = $DB->get_record_sql($sql, array($row[3], $row[1]));
    } else {
        // Doing create.
        $otherrecord = $DB->get_record_sql('SELECT * FROM {vm_courses} WHERE coursename = ? LIMIT 1', array($row[3]));
    }
    if (!empty($otherrecord)) {
        return html_writer::tag('p', get_string('csvnonuniquecoursename', 'mod_kronossandvm', $otherrecord), $redoptions);
    }
    $i = 0;
    $record = new stdClass();
    foreach ($columns as $name) {
        if (isset($row[$i])) {
            $record->$name = $row[$i];
        }
        $i++;
    }
    foreach ($requiredcolumns as $name) {
        if (!isset($record->$name) || strlen($record->$name) == 0) {
            $message = kronossandvm_csv2message($row);
            $message->field = $name;
            return html_writer::tag('p', get_string('csvrequiredfield', 'mod_kronossandvm', $message), $redoptions);
        }
    }
    return $record;
}

/*
 * Checks to see if user can configure the virtual templates.
 *
 * @param string $otcourseno otcourseno name to check if unique.
 * @param string $coursename coursename name to check if unique.
 * @param id $id Optional id of record being edited if editing.
 * @return array List of fields which are not unique.
 */
function kronossandvm_vm_courses_is_unique($otcourseno, $coursename, $id = null) {
    global $DB;
    if ($id !== null) {
        // Doing and update as id column has value.
        $sql = 'SELECT * FROM {vm_courses} WHERE otcourseno = ? AND id != ? LIMIT 1';
        $otherrecord = $DB->get_record_sql($sql, array($otcourseno, $id));
    } else {
        // Doing create.
        $sql = 'SELECT * FROM {vm_courses} WHERE otcourseno = ? LIMIT 1';
        $otherrecord = $DB->get_record_sql($sql, array($otcourseno));
    }
    $error = array();
    if (!empty($otherrecord)) {
        $error[] = 'otcourseno';
    }
    $otherrecord = null;
    if ($id !== null) {
        // Doing and update as id column has value.
        $sql = 'SELECT * FROM {vm_courses} WHERE coursename = ? AND id != ? LIMIT 1';
        $otherrecord = $DB->get_record_sql($sql, array($coursename, $id));
    } else {
        // Doing create.
        $sql = 'SELECT * FROM {vm_courses} WHERE coursename = ? LIMIT 1';
        $otherrecord = $DB->get_record_sql($sql, array($coursename));
    }
    if (!empty($otherrecord)) {
        $error[] = 'coursename';
    }
    return $error;
}
