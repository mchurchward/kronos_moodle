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
 * @package    block_rladmin
 * @copyright  2012 Remote Learner Inc http://www.remote-learner.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'RL Update Manager';

$string['applied_filters'] = 'Applied Filters';
$string['block_instructions'] = '<p>The MASS interface allows you to install, upgrade, and rate plugins on your sandbox site.</p>';
$string['btn_addfilters'] = 'Add Filters';
$string['btn_install'] = 'Install and Upgrade Add-Ons';
$string['btn_rate'] = 'Rate Plugins';
$string['cachedef_addondata'] = 'Cache for storing MASS Add-on data.';
$string['cancelled'] = 'Cancelled';
$string['change'] = 'Change';
$string['clear_filters'] = 'Clear Filters';
$string['communication_error'] = 'There was a communication problem while attempting to fetch remote data.';
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
$string['filter_auth'] = 'Authentication';
$string['filter_block'] = 'Block';
$string['filter_enrol'] = 'Enrolment';
$string['filter_filter'] = 'Filter';
$string['filter_format'] = 'Course Format';
$string['filter_gradeexport'] = 'Grade Export';
$string['filter_installed'] = 'Installed';
$string['filter_local'] = 'Local';
$string['filter_not_installed'] = 'Not Installed';
$string['filter_plagiarism'] = 'Plagiarism';
$string['filter_qtype'] = 'Question Type';
$string['filter_repository'] = 'Repository';
$string['filter_theme'] = 'Theme';
$string['filter_tinymce'] = 'TinyMCE';
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
$string['install_instructions'] = 'Configure and test your plugins on your sandbox site, then request that the changes be moved to your production site.';
$string['log'] = 'Log:';
$string['manageaddon'] = 'Manage Addons';
$string['name'] = 'Name:';
$string['newdate'] = 'New time:';
$string['nextupdate'] = 'Your next update is:';
$string['notchanged'] = 'The selected date is identical to the currently scheduled date';
$string['notification_email'] = 'Notification Email: ';
$string['notifyonsuccess'] = 'Notify on success:';
$string['notifyonsuccessdesc'] = 'Check this option to send an email to the recipient list every time a successful update happens.';
$string['notinrange'] = 'The selected date does not fall within the specified update period';
$string['notstarted'] = 'Not Started';
$string['noupdate'] = 'No update currently scheduled';
$string['pagetitle'] = 'Install and Upgrade Add-Ons';
$string['permission_denied'] = 'Permission to contact plugin server was denied.';
$string['recipients'] = 'Recipients';
$string['recipientsdesc'] = 'List of email addresses to receive notification emails (one per line)';
$string['rlagent:addinstance'] = 'Add a new RL Update Manager block';
$string['save'] = 'Save';
$string['schedule'] = 'Schedule';
$string['scheduleddate'] = 'Scheduled time:';
$string['scheduledevents'] = 'Update Schedule';
$string['selected_plugins_queue'] = 'Selected Plugins Queue';
$string['settings'] = 'Settings';
$string['siteadminonly'] = 'This page is for site administrators only.';
$string['skipped'] = 'Skipped';
$string['skipupdate'] = 'Skip';
$string['status'] = 'Status:';
$string['syncsite'] = 'Perform Site Sync';
$string['type_filter'] = 'Type filter and select Enter';
$string['unknown_addon'] = 'Unknown addon';
$string['update_available'] = '
    <p>This data for this staging site was last synced with the production site more than 7 days ago. If you wish to add or remove plugins with more current data, select the <strong>Update Data</strong> button below.</p>
    <p>Please note that this site sync will take several minutes. It will rewrite all site data, including this Web page. Your user account will be emailed when the update is complete. If the email below is not correct, please update your user account email before beginning this process.';
$string['update_available_heading'] = 'Update Available';
$string['updatedisabled'] = 'Updates have been disabled';
$string['updateend'] = 'Update Block End';
$string['updateenddesc'] = 'The end of the maintenance period when updates can be performed.';
$string['updateperiod'] = 'Update period:';
$string['updatescheduling'] = 'Update Scheduling';
$string['update_selected_plugins'] = 'Update/Install Selected Plugins';
$string['updatespan'] =  '{$a->start} to {$a->end}';
$string['updatestart'] = 'Update Block Start';
$string['updatestartdesc'] = 'The start of the maintenance period when updates can be performed.';
$string['updatingdata'] = 'Updating Site Data';
$string['warning'] = 'Warning:';
