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
            // Something went wrong when processing this queue. The file was marked as processing,
            // but it did not finish gracefully and update its status to 0.
            // See run function in importqueue.class.php.
            $current->status = 4;
            $DB->update_record('dhimport_importqueue', $current);
            $this->send_debug_email($current->id);
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
     * Get or make the user objects for the provided email addresses
     * @param array $emails An array of emails.
     * @return array $users An array of real and/or manufactured Mooodle users.
     */
    protected function get_email_users($emails) {
        global $DB;
        $found = array();
        foreach ($emails as $key => $email) {
            $email = trim($email);
            $emails[$key] = $email;
            $found[$email] = false;
        }
        $users  = $DB->get_records_list('user', 'email', $emails);
        foreach ($users as $user) {
            $found[$user->email] = true;
        }
        foreach ($found as $email => $exist) {
            if (!$exist) {
                $user = new \stdClass();
                $user->id    = 1;
                $user->email = $email;
                $users[] = $user;
            }
        }
        return $users;
    }

    /**
     * Send debug email to debug email addresses.
     *
     * @param int $queueid The ID of the queue to alert debug recipients about.
     */
    protected function send_debug_email($queueid) {
        global $CFG;

        $data = new \stdClass();
        $data->queueid = $queueid;
        $data->wwwroot = $CFG->wwwroot;

        $from = get_string('email_debug_error_from', 'dhimport_importqueue');
        $subject = get_string('email_debug_error_subject', 'dhimport_importqueue', $data);
        $message = get_string('email_debug_error_message', 'dhimport_importqueue', $data);

        $debugemails = get_config('dhimport_importqueue', 'debugnotification');
        $emails = explode(",", $debugemails);
        $users = $this->get_email_users($emails);
        foreach ($users as $user) {
            ob_start();
            if (!defined('BEHAT_TEST')) {
                email_to_user($user, $from, $subject, $message, '');
            }
            ob_end_flush();
        }
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
