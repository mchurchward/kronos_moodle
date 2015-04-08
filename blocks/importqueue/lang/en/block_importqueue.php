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
 * Import queue block.
 *
 * @package    block_importqueue
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2015 Remote Learner.net Inc http://www.remote-learner.net
 */
$string['block_importqueue'] = 'Training manager';
$string['pluginname'] = 'Import queue';
$string['newimportqueue'] = 'Training manager';
$string['newimportqueuecontent'] = '<a href="{$a->wwwroot}/blocks/importqueue/importusers.php">Import Users</a><br><a href="{$a->wwwroot}/blocks/importqueue/queuestatus.php">List imports</a><br>';
$string['configtitle'] = 'Block title';
$string['configcontent'] = 'Content';
$string['importqueue:sitewide'] = 'Access to select from all usersets for importation';
$string['importqueue:addinstance'] = 'Add block instance';
$string['importqueue:upload'] = 'Upload users to userset';
$string['configauthkronos'] = 'Please configure the Kronos portal authentication plugin.';
$string['csvcannotsavedata'] = 'Temporary file is not writable, please contact the system adminstrator';
$string['csvinvalidcolumnformat'] = 'The uploaded header is incorrect, missing {$a}';
$string['csvinvalidrow'] = 'Invalid row data, line {$a->linenumber}, column {$a->columnname}';
$string['csvadded'] = 'CSV File added to queue';
$string['importuserstitle'] = 'Import users';
$string['importusersheading'] = 'Import users';
$string['importusersformheader'] = 'Import users';
$string['selecteduserset'] = 'Select Userset';
$string['placeholder'] = 'Type the name of a User Set';
$string['autocompletedesc'] = 'Type the name of a User Set for users uploaded in the csv to be enroled in.';
$string['importusersuccess'] = 'Import started';
$string['importuserstatus'] = 'View status';
$string['uploadrequired'] = 'Please select a CSV file to upload';
$string['missinguserset'] = 'Please select a userset to add users to.';
$string['queuestatustitle'] = 'Most recent import uploads';
$string['queuestatusheading'] = 'Import status';
$string['queuelogtitle'] = 'Import logs';
$string['queuelogheading'] = 'All import logs';
$string['queuelogheadingfail'] = 'Failed import logs';
$string['queuelogheadingsuccess'] = 'Successful import logs';
$string['columnstatus'] = 'Status';
$string['columntimemodified'] = 'Last update';
$string['columntimecreated'] = 'Created';
$string['columntype'] = 'Type';
$string['columnline'] = 'Line';
$string['columnlogs'] = 'Import logs';
$string['columnmessage'] = 'Message';
$string['logs'] = 'Results';
$string['viewerrors'] = '| Errors';
$string['queued'] = 'Waiting';
$string['complete'] = 'Complete';
$string['errors'] = 'Errors';
$string['processing'] = 'Processing';
$string['error'] = 'Error';
$string['success'] = 'Success';
$string['successlogs'] = 'Successful';
$string['faillogs'] = 'Failed';
$string['alllogs'] = 'All';
$string['show'] = 'Show';
$string['importusersqueue'] = 'Total user imports: {$a}';
$string['importusersviewqueue'] = 'List imports';
$string['refreshstatus'] = 'Refresh status';
$string['usersetnotfound'] = 'Userset not found for solution id: {$a->solutionid}';
$string['solutionidnotset'] = 'Solution id not set';
$string['noresults'] = 'No results found for {$a}';
