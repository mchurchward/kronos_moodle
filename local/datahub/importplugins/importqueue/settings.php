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

defined('MOODLE_INTERNAL') || die;

// Start of "scheduling" section.
$settings->add(new admin_setting_heading('dhimport_importqueue/scheduling', get_string('importfilesheading', 'dhimport_importqueue'), ''));

// Setting for schedule_files_path.
$settings->add(new admin_setting_configtext('dhimport_importqueue/schedule_files_path', get_string('import_files_path', 'dhimport_importqueue'),
        get_string('config_schedule_files_path', 'dhimport_importqueue'), '/datahub/dhimport_importqueue'));

// Setting for user_schedule_file.
$settings->add(new admin_setting_configtext('dhimport_importqueue/user_schedule_file', get_string('user_schedule_file', 'dhimport_importqueue'),
        get_string('config_user_schedule_file', 'dhimport_importqueue'), 'user.csv'));

// Setting for course_schedule_file.
$settings->add(new admin_setting_configtext('dhimport_importqueue/course_schedule_file', get_string('course_schedule_file', 'dhimport_importqueue'),
        get_string('config_course_schedule_file', 'dhimport_importqueue'), 'course.csv'));

// Setting for enrolment_schedule_file.
$settings->add(new admin_setting_configtext('dhimport_importqueue/enrolment_schedule_file', get_string('enrolment_schedule_file', 'dhimport_importqueue'),
        get_string('config_enrolment_schedule_file', 'dhimport_importqueue'), 'enrol.csv'));

// Start of "logging" section.
$settings->add(new admin_setting_heading('dhimport_importqueue/logging', get_string('logging', 'dhimport_importqueue'), ''));

// Log file location.
$settings->add(new admin_setting_configtext('dhimport_importqueue/logfilelocation', get_string('logfilelocation', 'dhimport_importqueue'),
        get_string('configlogfilelocation', 'dhimport_importqueue'), RLIP_DEFAULT_LOG_PATH));

// Email notification.
$settings->add(new admin_setting_configtext('dhimport_importqueue/emailnotification', get_string('emailnotification', 'dhimport_importqueue'),
        get_string('configemailnotification', 'dhimport_importqueue'), ''));

// Debug email notification for files that have not processed.
$settings->add(new admin_setting_configtext('dhimport_importqueue/debugnotification', get_string('debugnotification', 'dhimport_importqueue'),
        get_string('configdebugnotification', 'dhimport_importqueue'), ''));
