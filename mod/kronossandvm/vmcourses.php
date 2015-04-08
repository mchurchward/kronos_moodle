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
require_once($CFG->dirroot.'/mod/kronossandvm/vmcourses_table.php');
require_once($CFG->dirroot.'/mod/kronossandvm/vmcourses_form.php');
require_once($CFG->dirroot.'/mod/kronossandvm/instances_table.php');
require_once($CFG->libdir.'/adminlib.php');
require_login();

$PAGE->set_pagelayout('admin');

$action = optional_param('action', 'list', PARAM_TEXT);
$id = optional_param('id', 0, PARAM_INT);

$PAGE->set_url('/mod/kronossandvm/vmcourses.php');

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('vmcourseslist', 'mod_kronossandvm'));
$PAGE->set_heading(get_string('vmcourseslist', 'mod_kronossandvm'));


if (!kronossandvm_canconfig()) {
    print_error('nopermissiontoshow');
}

if ($action == 'list') {
    $data = new stdClass();
    $data->action = 'list';
    $mform = new vmcourses_form($PAGE->url, $data);
    if ($mform->is_cancelled()) {
        redirect(new moodle_url($CFG->wwwroot.'/mod/kronossandvm/vmcourses.php', array('action' => 'list')));
    }
    $formdata = $mform->get_data();
    if ($formdata) {
        if (empty($formdata->isactive)) {
            $formdata->isactive = 0;
        }
        $errors = kronossandvm_vm_courses_is_unique($formdata->otcourseno, $formdata->coursename);
        if (empty($errors)) {
            $formdata->timemodified = time();
            $formdata->timecreated = time();
            $DB->insert_record('vm_courses', $formdata);
            redirect(new moodle_url($CFG->wwwroot.'/mod/kronossandvm/vmcourses.php', array('action' => 'list')));
        }
    }
    echo $OUTPUT->header();
    $table = new vmcourses_table('admin');
    $table->out(25, true);
    echo html_writer::tag('h3', get_string('addtemplate', 'mod_kronossandvm'));
    $mform->display();
    echo $OUTPUT->footer();
} else if (!empty($id) && $action == 'edit') {
    $data = $DB->get_record('vm_courses', array('id' => $id));
    $data->action = 'edit';
    $mform = new vmcourses_form($PAGE->url, $data);

    $data = $mform->get_data();
    if ($mform->is_cancelled()) {
        redirect(new moodle_url($CFG->wwwroot.'/mod/kronossandvm/vmcourses.php', array('action' => 'list')));
    } else if ($mform->is_submitted() && !empty($data)) {
        if (empty($data->isactive)) {
            $data->isactive = 0;
        }
        $data->timemodified = time();
        $DB->update_record('vm_courses', $data);
        redirect(new moodle_url($CFG->wwwroot.'/mod/kronossandvm/vmcourses.php', array('action' => 'list')));
    } else {
        echo $OUTPUT->header();
        echo html_writer::tag('h1', get_string('edittemplate', 'mod_kronossandvm'));
        $mform->display();
        echo $OUTPUT->footer();
    }
} else if (!empty($id) && ($action == 'instances' || $action = 'instanceswarning')) {
    echo $OUTPUT->header();
    $data = $DB->get_record('vm_courses', array('id' => $id));
    if ($action == 'instanceswarning') {
        echo html_writer::tag('h4', get_string('vmcoursesexist', 'mod_kronossandvm', $data));
    } else {
        echo html_writer::tag('h4', get_string('vmcoursesinstances', 'mod_kronossandvm', $data));
    }

    // There is instances using this template currently.
    $table = new instances_table('instances', $id, $action);
    $table->out(25, true);
    echo html_writer::empty_tag('br');
    echo html_writer::empty_tag('br');
    // Button to navigate back to virtual template list for ease of use.
    $link = new moodle_url('/mod/kronossandvm/vmcourses.php', array('action' => 'list'));
    $options = array('type' => 'button', "onclick" => 'window.location=\''.$link->out()."'");
    echo html_writer::tag('button', get_string('managetemplates', 'mod_kronossandvm'), $options);
    echo $OUTPUT->footer();
}
