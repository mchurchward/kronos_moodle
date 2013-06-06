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
 * Strings for component 'block_rlagent', language 'en'
 *
 * @package    blocks
 * @subpackage block_rladmin
 * @author     Remoter-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (c) 2012 Remote Learner.net Inc http://www.remote-learner.net
 */

$string['pluginname'] = 'RL Update Manager';

$string['cancelled'] = 'Cancelled';
$string['change'] = 'Change';
$string['completed'] = 'Completed';
$string['defaultdate'] = 'Default time:';
$string['enable'] = 'Enable';
$string['email_text_completed'] = 'Your Moodle site ({$a->www}) has been updated!';
$string['email_text_error'] = 'An error occurred during the automatic update of your site ({$a->www}):
{$a->log}';
$string['email_text_skipped'] = 'An update to your Moodle site ({$a->www}) was skipped:
{$a->log}';
$string['email_html_completed'] = '';
$string['email_html_error'] = '';
$string['email_html_skipped'] = '';
$string['email_sub_completed'] = 'Successful Site Update';
$string['email_sub_error'] = 'Error During Site Update';
$string['email_sub_skipped'] = 'Site Update Skipped';
$string['disabled'] = 'The RL Update Manager block has been manually disabled, no updates will occur until the block is enabled.';
$string['disabledesc'] = 'Disabling automatic updates will prevent the automatic application of bug fixes and security updates.';
$string['error'] = 'Error';
$string['error_brokengit'] = 'There was an error while checking your git status.';
$string['error_changedfiles'] = 'Your Moodle files have unapproved changes.';
$string['error_conflicts'] = 'Your Moodle repository has unresolved conflicts.';
$string['error_diverged'] = 'Your Moodle repository has unapproved changes.';
$string['error_updatefailed'] = 'An error occurred during the update process.';
$string['eventnotfound'] = 'Sorry, that scheduled update could be found in the database';
$string['inprogress'] = 'In Progress';
$string['log'] = 'Log:';
$string['name'] = 'Name:';
$string['newdate'] = 'New time:';
$string['nextupdate'] = 'Your next update is:';
$string['notchanged'] = 'The selected date is identical to the currently scheduled date';
$string['notifyonsuccess'] = 'Notify on success:';
$string['notifyonsuccessdesc'] = 'Check this option to send an email to the recipient list every time a successful update happens.';
$string['notinrange'] = 'The selected date does not fall within the specified update period';
$string['notstarted'] = 'Not Started';
$string['noupdate'] = 'No update currently scheduled';
$string['recipients'] = 'Recipients';
$string['recipientsdesc'] = 'List of email addresses to receive notification emails (one per line)';
$string['rlagent:addinstance'] = 'Add a new RL Update Manager block';
$string['save'] = 'Save';
$string['schedule'] = 'Schedule';
$string['scheduleddate'] = 'Scheduled time:';
$string['scheduledevents'] = 'Update Schedule';
$string['settings'] = 'Settings';
$string['siteadminonly'] = 'This page is for site administrators only.';
$string['skipped'] = 'Skipped';
$string['status'] = 'Status:';
$string['updatedisabled'] = 'Updates have been disabled';
$string['updateend'] = 'Update Block End';
$string['updateenddesc'] = 'The end of the maintenance period when updates can be performed.';
$string['updateperiod'] = 'Update period:';
$string['updatescheduling'] = 'Update Scheduling';
$string['updatespan'] =  '{$a->start} to {$a->end}';
$string['updatestart'] = 'Update Block Start';
$string['updatestartdesc'] = 'The start of the maintenance period when updates can be performed.';
$string['warning'] = 'Warning:';
