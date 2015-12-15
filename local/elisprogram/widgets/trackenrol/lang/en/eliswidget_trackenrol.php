<?php
/**
 * ELIS(TM): Enterprise Learning Intelligence Suite
 * Copyright (C) 2008-2015 Remote-Learner.net Inc (http://www.remote-learner.net)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    eliswidget_trackenrol
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2014 Onwards Remote-Learner.net Inc (http://www.remote-learner.net)
 * @author     Brent Boghosian <brent.boghosian@remote-learner.net>
 *
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Track Enrolment Widget';
$string['name'] = 'Track Enrolment Widget';
$string['description'] = 'A widget allowing students to manage enrolment in Tracks.';
$string['action_enrol'] = 'Enrol';
$string['action_unenrol'] = 'Unenrol';
$string['allowall'] = 'Allow all';
$string['any'] = 'Any';
$string['data_completetime'] = 'Completed: ';
$string['data_grade'] = 'Grade';
$string['data_status'] = 'Status';
$string['date_na'] = 'Not available';
$string['enddate'] = 'End Date';
$string['enrol_confirm_enrol'] = 'Are you sure you want to enrol in this Track?';
$string['enrol_confirm_unenrol'] = 'Are you sure you want to unenrol from this Track?';
$string['enrol_confirm_unenrol_cascade'] = 'Are you sure you want to unenrol from this Track? You will not be able to recover grade data.  Please go to {$a} and make a copy of your progress report.';
$string['enrol_confirm_title'] = 'Confirmation';
$string['enrolled'] = 'enrolled';
$string['showenrol'] = 'Show only enroled';
$string['showenrolall'] = 'Show both enroled and available';
$string['showenroltitle'] = 'Enroled';
$string['generatortitle'] = 'Add A Filter';
$string['idnumber'] = 'ID Number';
$string['individual_course_progress_report'] = 'Individual course progress report';
$string['less'] = '...less';
$string['max'] = 'max';
$string['more'] = 'more...';
$string['nonefound'] = 'None found';
$string['of'] = 'of';
$string['track_description'] = 'Description';
$string['track_header'] = '{$a->element_idnumber}: {$a->element_name}';
$string['track_idnumber'] = 'ID Number';
$string['track_name'] = 'Name';
$string['track_program'] = 'Program';
$string['tracks'] = 'Tracks';
$string['unenrol_from_track'] = 'Allow Track unenrolments';
$string['unenrol_from_track_desc'] = 'If this is enabled, users will be allowed to self-unenrol from Tracks via the Track Enrolment widget.';
$string['enrol_into_track'] = 'Allow Track enrolments';
$string['enrol_into_track_desc'] = 'If this is enabled, users will be allowed to self-enrol into Tracks via the Track Enrolment widget.';
$string['setting_trackviewcap'] = 'View Track capabilitiy';
$string['setting_trackviewcap_description'] = 'The capability required for a user to be able to associate a specific Track - assigned on ELIS Track context(s) and/or system context.';
$string['setting_viewcap'] = 'View capabilities';
$string['setting_viewcap_description'] = 'Any of the selected capabilities are required for a user to be able to view the Track Enrolment widget.';
$string['setting_viewcontexts'] = 'Allowed contexts';
$string['setting_viewcontexts_description'] = 'Select any allowed contexts, for any selected view capabilities, to view the Track Enrolment widget.';
$string['setting_viewusersets'] = 'Allowed Usersets';
$string['setting_viewusersets_description'] = 'Userset members allowed to view the Track Enrolment widget.';
$string['setting_enabledfields_heading'] = 'Visible Fields';
$string['setting_enabledfields_heading_description'] = 'These settings control which fields/filters will be visible, hidden, defaulted(visible) or locked(hidden).';
$string['setting_enabledfields'] = '{$a} Fields';
$string['setting_enabledfields_description'] = 'The selected {$a} fields will be visible and available for searching.';
$string['setting_orderbyenroled'] = 'Sort Track listing by enroled';
$string['setting_orderbyenroled_description'] = 'Sort the listing of Tracks displaying enroled Tracks first.';
$string['startdate'] = 'Start Date';
$string['status_available'] = 'Available';
$string['status_notenroled'] = 'Not Enrolled';
$string['status_enroled'] = 'Enrolled';
$string['status_passed'] = 'Passed';
$string['status_failed'] = 'Failed';
$string['status_unavailable'] = 'Unavailable';
$string['waiting'] = 'waiting';
$string['working'] = 'Working...';
