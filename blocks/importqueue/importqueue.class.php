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
 * Utilty class to add files to data hub import.
 */
class importqueue {
     /**
      * @var string base Path for csv files to be stored.
      */
     private $base = '';

     /**
      * Constructor.
      */
    public function __construct() {
        global $CFG;
        $schedulefilespath = get_config('dhimport_importqueue', 'schedule_files_path');
        $this->base = $CFG->dataroot.DIRECTORY_SEPARATOR.$schedulefilespath.DIRECTORY_SEPARATOR;
        if (!file_exists($this->base)) {
            mkdir($this->base, 0777, true);
        }
        $this->userfile = get_config('dhimport_importqueue', 'user_schedule_file');
        $this->coursefile = get_config('dhimport_importqueue', 'course_schedule_file');
        $this->enrolmentfile = get_config('dhimport_importqueue', 'enrolment_schedule_file');
    }

     /**
      * Copy data files to datahub directory and add import queue record.
      *
      * @param string $user File name for user csv file to be copied to datahub directory.
      * @param string $course File name for course csv file.
      * @param string $enrolment File name for enrolment csv file.
      * @param int $id Optional id of import queue to add files to, if null new queue record is added.
      * @return int Returns id of import queue record.
      */
    public function addtoqueue($user, $course, $enrolment, $id = null) {
        global $DB, $CFG, $USER;
        if (empty($id)) {
            $record = new stdClass();
            $record->status = 0;
            $record->timemodified = time();
            $record->timecreated = time();
            $record->userid = $USER->id;
            $id = $DB->insert_record('dhimport_importqueue', $record);
        }
        $userfile = preg_replace('/\.csv/', $id.'.csv', $this->userfile);
        $coursefile = preg_replace('/\.csv/', $id.'.csv', $this->coursefile);
        $enrolmentfile = preg_replace('/\.csv/', $id.'.csv', $this->enrolmentfile);
        if (file_exists($user)) {
            copy($user, $this->base.$userfile);
        }
        if (file_exists($course)) {
            copy($course, $this->base.$coursefile);
        }
        if (file_exists($enrolment)) {
            copy($enrolment, $this->base.$enrolmentfile);
        }
        return $id;
    }

    /**
     * Enrol a batch of test users.
     *
     * @param string $userset User set to enrol users into.
     * @param array $users Users
     * @return string Temp file name containing enrolments.
     */
    public function enrol_users_userset($userset, $users) {
        $tempdir = make_temp_directory('/importqueue');
        $tempfile = tempnam($tempdir, 'enrol');
        $fd = fopen($tempfile, "w");
        fputcsv($fd, array("action", "context" , "user_username"));
        $count = count($users);
        for ($i = 0; $i < $count; $i++) {
            fputcsv($fd, array("create", "cluster_$userset", $users[$i]));
        }
        fclose($fd);
        return $tempfile;
    }
}
