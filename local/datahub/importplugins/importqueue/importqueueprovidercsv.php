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

require_once($CFG->dirroot.'/local/datahub/lib/rlip_fslogger.class.php');
require_once($CFG->dirroot.'/local/datahub/importplugins/importqueue/importqueuedblogger.php');

class importqueueprovidercsv extends rlip_importprovider_csv {
    /**
     * @var int $queueid id of queue currently being processed.
     */
    private $queueid = 0;

    /**
     * Constructor.
     *
     * @param array $entitytypes Array of entity types to accept.
     * @param array Associative array of entity types and files for the entity.
     */
    public function __construct($entitytypes, $files) {
        global $DB;
        // Check in progress import. 0 = queued, 1 = finished, 2 = finished with errors, 3 = processing.
        $queue = $DB->get_records('dhimport_importqueue', array('status' => 3), 'id desc');
        if (!empty($queue)) {
            $current = array_pop($queue);
            $files = $this->build_files($current->id);
            // Record currently is being processed, by default the csv files will not exist.
            // When files do not exist there is no processing done by import plugin.
            parent::__construct($entitytypes, $files);
            return;
        }

        // Nothing is currently being processed, checking for unprocessed.
        $queue = $DB->get_records('dhimport_importqueue', array('status' => 0), 'id desc');
        if (!empty($queue)) {
            $next = array_pop($queue);
            $this->queueid = $next->id;
            $files = $this->build_files($this->queueid);
        }
        parent::__construct($entitytypes, $files);
    }

    /**
     * Build files array for provider.
     *
     * @param int $queueid Id of queue.
     * @return array Array of files, empty array if files do not exist.
     */
    public function build_files($queueid) {
        global $CFG;
        $schedulefilespath = get_config('dhimport_importqueue', 'schedule_files_path');
        $userfile = get_config('dhimport_importqueue', 'user_schedule_file');
        $userfile = preg_replace('/\.csv/', $queueid.'.csv', $userfile);
        $coursefile = get_config('dhimport_importqueue', 'course_schedule_file');
        $coursefile = preg_replace('/\.csv/', $queueid.'.csv', $coursefile);
        $enrolfile = get_config('dhimport_importqueue', 'enrolment_schedule_file');
        $enrolfile = preg_replace('/\.csv/', $queueid.'.csv', $enrolfile);
        $base = $CFG->dataroot.DIRECTORY_SEPARATOR.$schedulefilespath.DIRECTORY_SEPARATOR;
        $files = array();
        $files['user'] = $base.$userfile;
        $files['course'] = $base.$coursefile;
        $files['enrolment'] = $base.$enrolfile;
        return $files;
    }

    /**
     * Get current queue id provider is processing.
     *
     * @return int Id of queue.
     */
    public function get_queueid() {
        return $this->queueid;
    }

    /**
     * Return loggger which saves message to the database.
     *
     * @return object importqueuedblogger class.
     */
    public function get_fslogger($plugin, $entity = '', $manual = false, $starttime = 0) {
        return new importqueuedblogger($this->queueid);
    }
}
