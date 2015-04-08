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

require_once('../../config.php');
require_once($CFG->dirroot.'/mod/kronossandvm/lib.php');

$id = optional_param ('id', 0, PARAM_INT);
$a = optional_param ('a', 0, PARAM_INT);
$submitted = optional_param ('submitted', '', PARAM_INT);

if ($id) {
    if (!$cm = get_coursemodule_from_id('kronossandvm', $id)) {
        print_error(get_string('errormoduleid', 'mod_kronossandvm'));
    }
    if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
        print_error(get_string('errorcourseconfig', 'mod_kronossandvm'));
    }
    if (!$kronossandvm = $DB->get_record('kronossandvm', array('id' => $cm->instance))) {
        print_error(get_string('errormoduleid', 'mod_kronossandvm'));
    }
} else if ($a) {
    if (!$kronossandvm = $DB->get_record('kronossandvm', 'id', $a)) {
        print_error(get_string('errormoduleid', 'mod_kronossandvm'));
    }
    if (!$course = $DB->get_record('course', 'id', $kronossandvm->course)) {
        print_error(get_string('errorcourseconfig', 'mod_kronossandvm'));
    }
    if (!$cm = get_coursemodule_from_instance('kronossandvm', $kronossandvm->id, $course->id)) {
        print_error(get_string('errormoduleid', 'mod_kronossandvm'));
    }
} else {
    print_error(get_string('errormoduleidorinstance', 'mod_kronossandvm'));
}

$context = context_module::instance($cm->id);
require_course_login($course);
$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/mod/kronossandvm/view.php', array('id' => $id));
$coursecontext = context_course::instance($course->id);

$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
require_course_login($course, true, $cm);
$PAGE->set_activity_record($kronossandvm);
$PAGE->set_title($kronossandvm->name);
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('incourse');
$today = mktime( 0, 0, 0, date ( 'm' ), date ( 'd' ), date ( 'Y' ) );

$msg = '';
$notes = html_writer::start_tag('div', array('id' => 'notes'));
$notes .= html_writer::start_tag('ol');
$notes .= html_writer::tag('li', get_string('instructions1', 'mod_kronossandvm'));
$notes .= html_writer::tag('li', get_string('instructions2', 'mod_kronossandvm'));
$notes .= html_writer::tag('li', get_string('instructions3', 'mod_kronossandvm'));
$notes .= html_writer::end_tag('ol');
$notes .= html_writer::end_tag('div');

$refreshbutton = html_writer::tag('div', kronossandvm_get_update_form($id), array('id' => 'ref'));

// When request submitted it will process here.
if (!empty($submitted)) {
    if ($vmrequests = $DB->get_records('vm_requests', array('vmid' => $kronossandvm->id, 'userid' => $USER->id, 'isactive' => 1))) {
        $PAGE->set_periodic_refresh_delay(60);
        $msg = get_string('systembeingprepared', 'kronossandvm');
    } else {
        list ($allowrequest, $msg) = kronossandvm_get_message($context, $id);
        if ($allowrequest == 0) {
            $notes = '';
        }
        if ($allowrequest == 1) {
            $newreq = new stdClass();
            $newreq->vmid = $kronossandvm->id;
            $newreq->userid = $USER->id;
            $reqid = kronossandvm_add_vmrequest($context, $kronossandvm, $newreq);
            if (!$reqid) {
                $msg = get_string('systemrequestederror', 'kronossandvm');
            } else {
                $msg = get_string('systemrequested', 'kronossandvm');
            }
            redirect(new moodle_url('view.php', array('id' => $id), $msg, 5));
        }
    }
}

// Print the page header.
$strkronossandvms = get_string('modulenameplural', 'kronossandvm');
$strkronossandvm = get_string('modulename', 'kronossandvm');

// Check to see if there user vm is active.
// If it is and check it is ready, it yes then provide vm details, if not ready provide preparing message.
// If there is no active VM, check if able to request a VM.
// If they can then provide the request button, if they cannot then provide warning message.

if ($vmrequests = $DB->get_records('vm_requests', array('vmid' => $kronossandvm->id, 'userid' => $USER->id, 'isactive' => 1))) {
    foreach ($vmrequests as $vmrequest) {
        if (empty($vmrequest->instanceip)) {
            $notes = '';
            $PAGE->set_periodic_refresh_delay(60);
            $msg = get_string('systembeingprepared', 'kronossandvm' );
        } else {
            if ($vmrequest->endtime > time()) {
                $PAGE->set_periodic_refresh_delay(null);
                $refreshbutton = '';
                $notes = '';
                $content = html_writer::tag('b', get_string('systemready', 'kronossandvm'));
                $msg = html_writer::tag('div', $content, array('id' => 'ip'));
                $msg .= html_writer::start_tag('div', array('id' => 'dl'));
                $msg .= get_string('accesstext', 'kronossandvm');
                $link = kronossandvm_buildurl($vmrequest);
                $options = array('target' => '_blank');
                $msg .= html_writer::link($link , get_string('clickhere', 'mod_kronossandvm'), $options);
                $msg .= get_string('downloadinstructions', 'kronossandvm');
                $msg .= html_writer::end_tag('div');
                $msg .= html_writer::start_tag('div', array('id' => 'user'));
                $msg .= html_writer::tag('b', get_string('yourusername', 'kronossandvm'));
                $msg .= $vmrequest->username;
                $msg .= html_writer::end_tag('div');
                $msg .= html_writer::start_tag('div', array('id' => 'pass'));
                $msg .= html_writer::tag('b', get_string('yourpassword', 'kronossandvm'));
                $msg .= $vmrequest->password;
                $msg .= html_writer::end_tag('div');
                $msg .= html_writer::start_tag('div', array('id' => 'oinst'));
                $obj = new stdClass();
                $obj->endtime = userdate($vmrequest->endtime);
                $msg .= get_string('availableuntil', 'kronossandvm', $obj);
                $msg .= html_writer::end_tag('div');
            } else {
                list ($allowrequest, $msg) = kronossandvm_get_message($context, $id);
                $notes = '';
                $refreshbutton = '';
            }
        }
    }
} else {
    list ($allowrequest, $msg) = kronossandvm_get_message($context, $id);
    if ($allowrequest == 0) {
        $notes = '';
    }
    $refreshbutton = '';
}

$vmbody = html_writer::start_tag('div', array('id' => 'content'));
$vmbody .= html_writer::start_tag('div', array('id' => 'name'));
$vmbody .= html_writer::tag('b', $kronossandvm->name);
$vmbody .= html_writer::end_tag('div');
$vmbody .= html_writer::empty_tag('br');
$vmbody .= html_writer::tag('div', $kronossandvm->intro, array('id' => 'intro'));
$vmbody .= html_writer::empty_tag('br');
if ($notes) {
    $vmbody .= $notes;
    $vmbody .= html_writer::empty_tag('br');
}
if ($refreshbutton) {
    $vmbody .= $refreshbutton;
    $vmbody .= html_writer::empty_tag('br');
}
$vmbody .= html_writer::empty_tag('br');
$vmbody .= html_writer::tag('div', $msg, array('id' => 'msg'));
$vmbody .= html_writer::end_tag('div');

echo $OUTPUT->header();
echo $OUTPUT->box($vmbody, 'generalbox', 'vm');
echo $OUTPUT->footer();
