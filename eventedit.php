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
 * Remote Learner Update Manager Schedule
 *
 * @package    blocks
 * @subpackage rlagent
 * @author     Remoter-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (c) 2012 Remote Learner.net Inc http://www.remote-learner.net
 */

require('../../config.php');
require('lib/form_event.php');

require_login(SITEID);

$PAGE->set_url('/blocks/rlagent/schedule.php');
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title(get_string('pluginname', 'block_rlagent'));
$PAGE->set_pagelayout('popup');

if (!has_capability('moodle/site:config', context_system::instance())) {
    print_error('siteadminonly');
}

$done   = false;
$id     = required_param('id', PARAM_INT);

$event = $DB->get_record('block_rlagent_schedule', array('id' => $id));

if (empty($event)) {
    print_error('eventnotfound', 'block_rlagent');
}

$data = array();
$data['id'] = $id;
$data['name'] = $event->description;
$data['scheduleddate'] = $event->scheduleddate;
$data['originaldate']  = userdate($event->originaldate);
$data['startdate'] = $event->startdate;
$data['enddate'] = $event->enddate;

$span = new object();
$span->start = $event->startdate;
$span->end   = $event->enddate;

$data['updateperiod']  = get_string('updatespan', 'block_rlagent', $span);

$form = new form_event();
$form->set_data($data);

$data = $form->get_data();

if (!empty($data) && ($data->action = 'update')) {
    $event->scheduleddate = $data->scheduleddate;
    $DB->update_record('block_rlagent_schedule', $event);
    $done = true;
} else  if ($form->is_cancelled()) {
    $done = true;
}

print($OUTPUT->header());

if (! $done) {
    $form->display();
} else {
    print('<div class="close"><a href="Javascript:self.close();">'
        . get_string('closewindow') .'</a></div>');
}

print($OUTPUT->footer());
