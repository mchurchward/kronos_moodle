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
 * Import queue log.
 *
 * @package    block_importqueue
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2015 Remote Learner.net Inc http://www.remote-learner.net
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/blocks/importqueue/importqueuelog_table.php');

require_login(null, false);

$errors = optional_param('errors', 0, PARAM_INT);
$queueid = required_param('id', PARAM_INT);

$PAGE->set_url('/blocks/importqueue/queuelog.php', array('id' => $queueid, 'errors' => $errors));
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('queuelogtitle', 'block_importqueue'));

// Require system admin or import block permissions.

echo $OUTPUT->header();
switch ($errors) {
    case 0:
        $heading = 'queuelogheading';
        break;
    case 1:
        $heading = 'queuelogheadingfail';
        break;
    case 2:
        $heading = 'queuelogheadingsuccess';
        break;
}

$options = array('class' => 'queuelog');
echo html_writer::tag('h3', get_string($heading, 'block_importqueue'), $options);

echo html_writer::tag('div', get_config('block_importqueue', 'menu'));

echo html_writer::empty_tag('br');

echo html_writer::tag('b', get_string('show', 'block_importqueue')).': ';
$link = new moodle_url($CFG->wwwroot.'/blocks/importqueue/queuelog.php', array('id' => $queueid, 'errors' => 0));

if ($errors == 0) {
    echo html_writer::tag('b', get_string('alllogs', 'block_importqueue'));
} else {
    echo html_writer::link($link, get_string('alllogs', 'block_importqueue'));
}

echo ' | ';
$link = new moodle_url($CFG->wwwroot.'/blocks/importqueue/queuelog.php', array('id' => $queueid, 'errors' => 1));
if ($errors == 1) {
    echo html_writer::tag('b', get_string('faillogs', 'block_importqueue'));
} else {
    echo html_writer::link($link, get_string('faillogs', 'block_importqueue'));
}

echo ' | ';
$link = new moodle_url($CFG->wwwroot.'/blocks/importqueue/queuelog.php', array('id' => $queueid, 'errors' => 2));
if ($errors == 2) {
    echo html_writer::tag('b', get_string('successlogs', 'block_importqueue'));
} else {
    echo html_writer::link($link, get_string('successlogs', 'block_importqueue'));
}

$table = new importqueuelog_table('admin', $queueid);
$table->out(25, true);
echo $OUTPUT->footer();
