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
 * Remote Learner Update Manager - Moodle Addon Self Service action page
 *
 * @package   block_rlagent
 * @copyright 2014 Remote Learner Inc http://www.remote-learner.net
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$dir = dirname(__FILE__);
require_once($dir.'/../../../config.php');
require_once($dir.'/../lib.php');

require_login(SITEID);
global $USER;

if (!has_capability('moodle/site:config', context_system::instance())) {
    print_error('siteadminonly');
}

$contents = array($CFG->dirroot, $USER->email);
$messages = block_rlagent_write_incron_commands($contents, 'refresh_', $CFG->dataroot.'/manager/refresh/commands');
$return = json_encode($messages);
print($return);
