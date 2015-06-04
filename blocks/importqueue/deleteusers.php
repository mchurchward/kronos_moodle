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
 * Import users page.
 *
 * @package    block_importqueue
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2015 Remote Learner.net Inc http://www.remote-learner.net
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/blocks/importqueue/importqueue_form.php');

require_login(null, false);

$PAGE->set_url('/blocks/importqueue/importusers.php');
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$confirm = required_param('confirm', PARAM_INT);

if (empty($SESSION->block_importqueue_csvfile) || !file_exists($SESSION->block_importqueue_csvfile)) {
    $confirm = 0;
}

if ($confirm) {
    $count = count(file($SESSION->block_importqueue_csvfile)) - 1;
    $importqueue = new importqueue();
    $queueid = $importqueue->addtoqueue($SESSION->block_importqueue_csvfile, null, null);
    @unlink($SESSION->block_importqueue_csvfile);
    if ($queueid) {
        $status = new stdClass();
        $status->total = $count;
        $options = array('class' => 'deleteconfirmed');
        $PAGE->set_title(get_string('deleteconfirmed', 'block_importqueue', $status));
        echo $OUTPUT->header();
        echo html_writer::tag('h3', get_string('deleteconfirmed', 'block_importqueue', $status), $options);
    }
} else {
    $options = array('class' => 'deleteerror');
    $PAGE->set_title(get_string('deleteerror', 'block_importqueue'));
    echo $OUTPUT->header();
    echo html_writer::tag('h3', get_string('deleteerror', 'block_importqueue'), $options);
}

// Count how many uploads have been completed.
if ($count = $DB->count_records('dhimport_importqueue', array('userid' => $USER->id))) {
    $options = array('class' => 'uploadstatustext');
    echo html_writer::tag('p', get_string('importusersqueue', 'block_importqueue', $count), $options);
    $link = new moodle_url('/blocks/importqueue/queuestatus.php');
    $options = array("onclick" => 'window.location=\''.$link->out()."'", 'class' => 'uploadstatusbutton');
    echo html_writer::tag('button', get_string('importusersviewqueue', 'block_importqueue'), $options);
}

echo $OUTPUT->footer();
