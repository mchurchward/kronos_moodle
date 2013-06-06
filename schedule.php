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

require_once('../../config.php');
require_once($CFG->libdir .'/tablelib.php');
require_once(dirname(__FILE__) .'/lib/table_schedule.php');

require_login(SITEID);

$pluginname = get_string('pluginname', 'block_rlagent');
$pagetitle  = get_string('scheduledevents', 'block_rlagent');

$PAGE->set_url('/blocks/rlagent/schedule.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title($pluginname);
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add($pluginname);
$PAGE->navbar->add($pagetitle);

if (!has_capability('moodle/site:config', context_system::instance())) {
    print_error('siteadminonly');
}

$fields  = 'id, scheduleddate, originaldate, startdate, enddate, description, status, log';
$from    = $CFG->prefix.'block_rlagent_schedule';
$columns = array('scheduleddate', 'originaldate', 'startdate', 'enddate', 'description', 'status', 'log');
$headers = array('Scheduled Date', 'Original Date', 'Start Date', 'End Date', 'Description', 'Status', 'Log');

$table = new table_schedule('scheduled_update_table');
$table->set_sql($fields, $from, 'true');
$table->define_baseurl($CFG->wwwroot .'/blocks/rlagent/schedule.php');
$table->define_columns($columns);
$table->sortable(true, 'scheduleddate', SORT_DESC);

print($OUTPUT->header($pagetitle));
print($OUTPUT->heading(get_string('scheduledevents', 'block_rlagent')));
$table->out(10, false);
print($OUTPUT->footer());
