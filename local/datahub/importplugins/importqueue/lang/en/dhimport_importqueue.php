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
 * Import queue. This class overrides provider claass and ensures queue status is maintained.
 *
 * @package    dhimport_importqueue
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2015 Remote Learner.net Inc http://www.remote-learner.net
 */

$rlipshortname = 'DH';
$string['pluginname'] = 'Import queue';
$string['datahandling'] = 'Data handling';
$string['importfilesheading'] = 'Scheduled Import File Locations';
$string['import_files_path'] = 'Import files path';
$string['config_schedule_files_path'] = 'The directory path, relative to moodledata, to the location of import files.';
$string['config_user_schedule_file'] = 'The filename of the \'user\' '.$rlipshortname.' import file.';
$string['user_schedule_file'] = 'User import filename';
$string['config_course_schedule_file'] = 'The filename of the \'course\' '.$rlipshortname.' import file.';
$string['course_schedule_file'] = 'Course import filename';
$string['config_enrolment_schedule_file'] = 'The filename of the \'enrolment\' '.$rlipshortname.' import file.';
$string['enrolment_schedule_file'] = 'Enrolment import filename';
$string['config_enrolment_schedule_file'] = 'The filename of the \'enrolment\' '.$rlipshortname.' import file.';
$string['logging'] = 'Logging';
$string['configlogfilelocation'] = 'The directory path, relative to moodledata, to the location of log files.';
$string['logfilelocation'] = 'Log file location';
$string['configlogfilelocation'] = 'The directory path, relative to moodledata, to the location of log files.';
$string['configemailnotification'] = 'A comma-separated list of email addresses to send import log files to.';
$string['emailnotification'] = 'Email notification';
$string['configemailnotification'] = 'A comma-separated list of email addresses to send import log files to.';
$string['faillearningpathinvalid'] = 'The user {$a->email} has an invalid learning path {$a->learningpath}';
$string['failemail'] = 'Email address is a required field.';
$string['failsolutionid'] = 'Invalid solutionid {$a}';
$string['failsolutionidnotset'] = 'The solution id is not set, add column {$a}';
$string['failaction'] = 'The action {$a} is invalid';
$string['successlearningpath'] = '{$a->email} enrolled into learning path {$a->learningpath}';
$string['failcanupdate'] = 'You do not have the permission to update the user {$a}, please contact support.';
$string['failnouser'] = 'The user {$a->idnumber} does not exist.';
$string['failuserdeleted'] = 'The user {$a->idnumber} currently deleted. Please contact support.';
$string['failuserexistscanupdate'] = 'The user {$a->idnumber} currently exists. Try updating the user.';
$string['failuserexistscannotupdate'] = 'The user {$a->idnumber} currently exists and you do not have premission to update the user. Please contact support.';
$string['faillearningpathinvaliduser'] = 'The user {$a->email} could not be found. Failed enroling the user into the learning path {$a->learningpath}';