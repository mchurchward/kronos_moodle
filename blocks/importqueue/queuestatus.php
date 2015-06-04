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
require_once($CFG->dirroot.'/blocks/importqueue/importqueue_table.php');

require_login(null, false);

$PAGE->set_url('/blocks/importqueue/queuestatus.php');
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('queuestatustitle', 'block_importqueue'));

echo $OUTPUT->header();
$options = array('class' => 'queuestatus');
echo html_writer::tag('h3', get_string('queuestatusheading', 'block_importqueue'), $options);

echo html_writer::tag('div', get_config('block_importqueue', 'menu'));

$link = new moodle_url('/blocks/importqueue/queuestatus.php');
$options = array("onclick" => 'window.location=\''.$link->out()."'");
echo html_writer::tag('button', get_string('refreshstatus', 'block_importqueue'), $options);

$table = new importqueue_table('admin');
$table->out(25, true);
echo $OUTPUT->footer();
