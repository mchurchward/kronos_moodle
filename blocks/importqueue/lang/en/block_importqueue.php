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
$string['newimportqueuecontent'] = '<ul><li><a href="{$a->wwwroot}/blocks/importqueue/importusers.php">Import Users</a></li>';
$string['newimportqueuecontent'] .= '<li><a href="{$a->wwwroot}/blocks/importqueue/importusers.php?mode=update">Update Users</a></li>';
$string['newimportqueuecontent'] .= '<li><a href="{$a->wwwroot}/blocks/importqueue/importusers.php?mode=delete">Delete Users</a></li>';
$string['newimportqueuecontent'] .= '<li><a href="{$a->wwwroot}/blocks/importqueue/queuestatus.php">List uploads</a></li></ul>';
$string['menu'] = 'Menu html';
$string['menudesc'] = 'Enter the text which is to display as the menu when navigating the training manager block';
$string['configtitle'] = 'Block title';
$string['configcontent'] = 'Content';
$string['importqueue:sitewide'] = 'Access to select from all usersets for importation';
$string['importqueue:addinstance'] = 'Add block instance';
$string['importqueue:upload'] = 'Upload users to userset';
$string['configauthkronos'] = 'Please configure the Kronos portal authentication plugin.';
$string['csvcannotsavedata'] = 'Temporary file is not writable, please contact the system adminstrator';
$string['csvinvalidcolumnformat'] = 'The uploaded header is incorrect. The {$a->column} column needs to be in column number {$a->index}';
$string['csvinvalidcolumnformatexclude'] = 'The uploaded header is incorrect, column {$a} cannot be used.';
$string['csvinvalidcolumnformatheader'] = 'The uploaded header is incorrect, the following columns are required: {$a}';
$string['csvinvalidrow'] = 'The {$a->columnname} column must have a value, line {$a->linenumber}, email {$a->email}';
$string['csvadded'] = 'CSV File added to queue';
$string['importuserstitle'] = 'Import users';
$string['importusersheading'] = 'Import users';
$string['updatetitle'] = 'Update users';
$string['updateheading'] = 'Update users';
$string['importusersformheader'] = 'Import users';
$string['selecteduserset'] = 'Select Userset';
$string['placeholder'] = 'Type the name of a User Set';
$string['autocompletedesc'] = 'Type the name of a User Set for users uploaded in the csv to be enroled in.';
$string['updatesuccess'] = 'User update started';
$string['importuserssuccess'] = 'Import started';
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
$string['unknownerror'] = 'Unknown Error';
$string['unknownerrordesc'] = 'Unknown Error. Please try uploading this file again.';
$string['error'] = 'Error';
$string['success'] = 'Success';
$string['successlogs'] = 'Successful';
$string['faillogs'] = 'Failed';
$string['alllogs'] = 'All';
$string['show'] = 'Show';
$string['importusersqueue'] = 'Total uploads: {$a}';
$string['importusersviewqueue'] = 'List uploads';
$string['refreshstatus'] = 'Refresh status';
$string['usersetnotfound'] = 'Userset not found for solution id: {$a->solutionid}';
$string['solutionidnotset'] = 'Solution id not set';
$string['noresults'] = 'No results found for {$a}';
$string['importcolumns'] = 'Allowed import columns';
$string['importcolumnsdesc'] = 'Enter a comma seperated list of allowed import columns from the list bellow';
$string['updatecolumns'] = 'Columns allowed to be updated';
$string['updatecolumnsdesc'] = 'Enter a comma seperated list of columns which can be updated from the list bellow';
$string['allowedempty'] = 'Columns allowed to be empty when importing';
$string['allowedemptydesc'] = 'Enter a comma seperated list of columns which are allowed to be empty from the list bellow';
$string['updatesettingsheading'] = 'Update settings';
$string['updateallowedempty'] = 'Columns allowed to be empty when updating';
$string['updateallowedemptydesc'] = 'Enter a comma seperated list of columns which are allowed to be empty from the list bellow';
$string['csvfieldsheading'] = 'List of allowed import columns';
$string['csvfieldsdesc'] = '<ul>{$a}</ul>';
$string['errorcsvfile'] = 'Please select a file to upload';
$string['errorconfig_userset'] = 'Please select a learning path';
$string['invalidsolutionid'] = 'Your solution id ({$a}) is invalid, please contact support';
$string['expiredsolutionid'] = 'Your solution id is expired, please contact support';
$string['learningpath_select'] = 'Enable learning path drop down for training managers';
$string['learningpath_selectdesc'] = 'By enabling the learning path drop down training managers will be able to select a learning path from a drop down.';
$string['learningpath_autocomplete'] = 'Enable auto complete for admin';
$string['learningpath_autocompletedesc'] = 'The auto complete requires admin access or the  block/importqueue:sitewide capability. The auto complete selection will disable the learning path column requirement.';
$string['confirmdelete'] = 'The following users will be deleted';
$string['more'] = 'And {$a} more';
$string['confirmdeletebutton'] = 'Confirm deletion';
$string['deleteheading'] = 'Delete users';
$string['deletesolutionid'] = 'Deleted Solution id';
$string['deletesolutioniddesc'] = 'Users who are deleted will have their solution id set to this value';
$string['deletesettingsheading'] = 'Delete settings';
$string['deleteconfirmed'] = '{$a->total} users added to queue for deletion';
$string['deleteerror'] = 'File already processed';
$string['createsettingsheading'] = 'Create settings';