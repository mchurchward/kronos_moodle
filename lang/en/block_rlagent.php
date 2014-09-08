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

$string['actions_in_progress'] = 'Actions in Progress';
$string['actions_completed_success'] = 'Actions completed successfully';
$string['actions_completed_failure'] = 'Actions not completed successfully';
$string['add'] = 'Install';
$string['add_or_update_rating_bold'] = 'Add or update your rating';
$string['add_or_update_rating_normal'] = ' for this add on';
$string['ajax_request_failed'] = 'AJAX request failed.';
$string['applied_filters'] = 'Applied Filters';
$string['average_rating'] = 'Average Rating';
$string['block_instructions'] = '<p>The MASS interface allows you to install, upgrade, and rate add-ons on your sandbox site.</p>';
$string['btn_addfilters'] = 'Add Filters';
$string['btn_install'] = 'Install and Upgrade Add Ons';
$string['btn_rate'] = 'Rate Add Ons';
$string['cachedef_addondata'] = 'Cache for storing MASS add on data.';
$string['cancel'] = 'Cancel';
$string['cancelled'] = 'Cancelled';
$string['change'] = 'Change';
$string['clear_filters'] = 'Clear Filters';
$string['close'] = 'Close';
$string['communication_error'] = 'There was a communication problem while attempting to fetch remote data.';
$string['completed'] = 'Completed';
$string['confirm'] = 'Confirm';
$string['continue'] = 'Continue';
$string['defaultdate'] = 'Default time:';
$string['dependencies'] = 'Dependencies...';
$string['dependency_will_be_added'] = 'The following add on that {$a->source} depends on will also be added: {$a->target}.';
$string['dependency_will_be_removed'] = 'The following add on that depends on {$a->source} will be also be removed: {$a->target}.';
$string['disabled'] = 'The RL Update Manager block has been manually disabled, no updates will occur until the block is enabled.';
$string['disabledesc'] = 'Disabling automatic updates will prevent the automatic application of bug fixes and security updates.';
$string['enable'] = 'Enable';
$string['email_html_completed'] = '';
$string['email_html_error'] = '';
$string['email_html_skipped'] = '';
$string['email_sub_completed'] = 'Successful Site Update';
$string['email_sub_error'] = 'Error During Site Update';
$string['email_sub_skipped'] = 'Site Update Skipped';
$string['email_text_completed'] = 'Your Moodle site ({$a->www}) has been updated!';
$string['email_text_error'] = 'An error occurred during the automatic update of your site ({$a->www}):
{$a->log}';
$string['email_text_skipped'] = 'An update to your Moodle site ({$a->www}) was skipped:
{$a->log}';
$string['error'] = 'Error';
$string['error_add_installed'] = 'Add on {$a} is already installed.  Skipping addition.';
$string['error_brokengit'] = 'There was an error while checking your git status.';
$string['error_changedfiles'] = 'Your Moodle files have unapproved changes.';
$string['error_conflicts'] = 'Your Moodle repository has unresolved conflicts.';
$string['error_diverged'] = 'Your Moodle repository has unapproved changes.';
$string['error_remove_notinstalled'] = 'Add on {$a} is not present.  Skipping removal';
$string['error_update_added'] = 'A new version of {$a} will be added, no further update possible.  Skipping update.';
$string['error_update_not_installled'] = 'Add on {$a} is not installed and thus can\'t be updated.  Skipping update.';
$string['error_update_removed'] = 'The {$a} add on will be removed and thus can\'t be updated.  Skipping update.';
$string['error_unable_to_copy_command'] = 'Unable to copy command file to dispatch directory.';
$string['error_unable_to_create_dispatch_dir'] = 'Unable to create dispatch directory: {$a}';
$string['error_unable_to_delete_temp_command_file'] = 'Unable to delete command file from temporary directory.';
$string['error_unable_to_write_temp_command_file'] = 'Unable to write commands to temporary file location.';
$string['error_unknown_addon'] = 'Unknown add on: {$a}.  Skipping.';
$string['error_unknown_addon_type'] = 'Unknown add on type: {$a->type} for add on {$a->name}.  Skipping.';
$string['error_unparseable_name'] = 'Action: {$a->action} - unrecognizable add on name: {$a->subject}.  Skipping.';
$string['error_updatefailed'] = 'An error occurred during the update process.';
$string['eventnotfound'] = 'Sorry, that scheduled update could be found in the database';
$string['failure'] = 'Failure';
$string['for_pricing'] = '<a href="http://support.remote-learner.net/">Contact your<br />Account Manager</a><br />for pricing.';
$string['inprogress'] = 'In Progress';
$string['install_instructions'] = 'Configure and test your add ons on your sandbox site, then request that the changes be moved to your production site.';
$string['log'] = 'Log:';
$string['manageaddon'] = 'Manage Add Ons';
$string['name'] = 'Name:';
$string['newdate'] = 'New time:';
$string['nextupdate'] = 'Your next update is:';
$string['no_dependencies'] = 'No dependencies.';
$string['notchanged'] = 'The selected date is identical to the currently scheduled date';
$string['notification_email'] = 'Notification Email: ';
$string['notifyonsuccess'] = 'Notify on success:';
$string['notifyonsuccessdesc'] = 'Check this option to send an email to the recipient list every time a successful update happens.';
$string['notinrange'] = 'The selected date does not fall within the specified update period';
$string['notstarted'] = 'Not Started';
$string['noupdate'] = 'No update currently scheduled';
$string['pagetitle'] = 'Install and Upgrade Add Ons';
$string['permission_denied'] = 'Permission to contact add on server was denied.';
$string['plugin_description_not_available'] = 'Add on description not available.';
$string['plugin_name_not_available'] = 'Add on name not available.';
$string['plugins_need_help'] = 'If any of your plugins do not appear to be working as expected after you\'ve entered the necessary settings, please <a href="http://support.remote-learner.net/">open a support case</a>.';
$string['plugins_require_configuration'] = 'The following plugins that you have installed require configuration in their settings pages to work:';
$string['plugins_will_be_added'] = 'The following add ons will be added:';
$string['plugins_will_be_updated'] = 'The following add ons will be updated:';
$string['plugins_will_be_removed'] = 'The following add ons will be removed:';
$string['preparing_actions'] = 'Preparing actions...';
$string['recipients'] = 'Recipients';
$string['recipientsdesc'] = 'List of email addresses to receive notification emails (one per line)';
$string['remove'] = 'Uninstall';
$string['remove_action'] = 'Remove action';
$string['remove_filter'] = 'Remove filter';
$string['rlagent:addinstance'] = 'Add a new RL Update Manager block';
$string['save'] = 'Save';
$string['schedule'] = 'Schedule';
$string['scheduleddate'] = 'Scheduled time:';
$string['scheduledevents'] = 'Update Schedule';
$string['selected_plugins_queue'] = 'Selected Add Ons Queue';
$string['settings'] = 'Settings';
$string['siteadminonly'] = 'This page is for site administrators only.';
$string['skipped'] = 'Skipped';
$string['skipupdate'] = 'Skip';
$string['status'] = 'Status:';
$string['success'] = 'Success';
$string['syncsite'] = 'Perform Site Sync';
$string['title_auth'] = 'Authentication';
$string['title_block'] = 'Block';
$string['title_enrol'] = 'Enrolment';
$string['title_filter'] = 'Filter';
$string['title_format'] = 'Course Format';
$string['title_gradeexport'] = 'Grade Export';
$string['title_installed'] = 'Installed';
$string['title_local'] = 'Local';
$string['title_module'] = 'Module';
$string['title_not_installed'] = 'Not Installed';
$string['title_plagiarism'] = 'Plagiarism';
$string['title_qtype'] = 'Question Type';
$string['title_repository'] = 'Repository';
$string['title_theme'] = 'Theme';
$string['title_tinymce'] = 'TinyMCE';
$string['title_updateable'] = 'Updateable';
$string['to_be_added'] = 'To be added';
$string['to_be_removed'] = 'To be removed';
$string['to_be_updated'] = 'To be updated';
$string['trusted_addons_only'] = 'Only show Golden Add Ons';
$string['type_filter'] = 'Type filter and select Enter';
$string['unknown_addon'] = 'Unknown add on';
$string['update'] = 'Update';
$string['update_available'] = '
    <p>This data for this staging site was last synced with the production site more than 7 days ago. If you wish to add or remove add ons with more current data, select the <strong>Update Data</strong> button below.</p>
    <p>Please note that this site sync will take several minutes. It will rewrite all site data, including this Web page. Your user account will be emailed when the update is complete. If the email below is not correct, please update your user account email before beginning this process.';
$string['update_available_heading'] = 'Update Available';
$string['update_continue'] = '
    <p>This sandbox site will now update from your production site.  This is an automated process that may take a few minutes to complete and you will receive an email when the process finishes.</p>
    <p>Please wait until you receive the confirmation email before using the continue button.  This site will be not be accessible during the update, and any changes you make before the update starts will be overwritten during the update.</p>';
$string['updatedisabled'] = 'Updates have been disabled';
$string['updateend'] = 'Update Block End';
$string['updateenddesc'] = 'The end of the maintenance period when updates can be performed.';
$string['updateperiod'] = 'Update period:';
$string['updatescheduling'] = 'Update Scheduling';
$string['update_selected_plugins'] = 'Update/Install/Remove Selected Add Ons';
$string['updatespan'] =  '{$a->start} to {$a->end}';
$string['updatestart'] = 'Update Block Start';
$string['updatestartdesc'] = 'The start of the maintenance period when updates can be performed.';
$string['updatingdata'] = 'Updating Site Data';
$string['warning'] = 'Warning:';
