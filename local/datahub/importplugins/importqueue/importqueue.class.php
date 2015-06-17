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

require_once($CFG->dirroot.'/local/datahub/lib/rlip_importplugin.class.php');
require_once($CFG->dirroot.'/local/datahub/importplugins/version1/lib.php');
require_once($CFG->dirroot.'/local/datahub/importplugins/version1/version1.class.php');
require_once($CFG->dirroot.'/local/datahub/importplugins/importqueue/importqueueprovidercsv.php');
require_once($CFG->dirroot.'/local/datahub/importplugins/importqueue/classes/event/import_user_deleted.php');
require_once($CFG->dirroot.'/user/lib.php');

define('IMPORTQUEUE_DOESNOTEXIST', 1);
define('IMPORTQUEUE_USERDELETED', 2);
define('IMPORTQUEUE_EXISTS', 3);

/**
 * Test plugin used to test a simple entity and action
 */
class rlip_importplugin_importqueue extends rlip_importplugin_version1 {
    /**
     * @var array Required variable definition.
     */
    static public $import_fields_importqueueentity_importqueueaction = array('importqueuefield');
    /**
     * @var int $queueid id of queue currently being processed.
     */
    private $queueid = 0;
    /**
     * @var string $usersolutionidfield Shortname of Moodle user field containing Solution id.
     */
    private $usersolutionidfield = '';
    /**
     * @var array $solutionidmap Map of solution id string to solution id user sets.
     */
    private $solutionidmap = array();
    /**
     * @var array $learningpathmap Map of learning path string to learning path user sets.
     */
    private $learningpathmap = array();
    /**
     * @var object $auth Kronos authentication plugin object.
     */
    private $auth = array();
    /**
     * @var string $deletesolutionid Solution id for which delete users are moved to.
     */
    private $deletesolutionid = 'delete';

    /**
     * Import queue plugin constructor
     *
     * @param object $provider The import file provider that will be used to
     *                         obtain any applicable import files
     * @param boolean $manual  Set to true if a manual run
     */
    public function __construct($provider = null, $manual = false) {
        global $DB;
        $this->auth = get_auth_plugin('kronosportal');
        if ($this->auth->is_configuration_valid()) {
            // Get short names of user and user set solution id fields.
            $this->usersolutionidfield = 'profile_field_'.kronosportal_get_solutionfield();
        } else {
            $this->usersolutionidfield = 'profile_field_solutionid';
        }
        if (empty($provider)) {
            // Only avaiablity is being checked for if no provider is passed.
            return;
        }
        // Override csv import provider.
        // Check if this is a manual import.
        if (!($provider instanceof rlip_importprovider_moodlefile)) {
            $provider = new importqueueprovidercsv($provider->entity_types, $provider->files);
            $this->queueid = $provider->get_queueid();
        }
        $this->provider = $provider;
        // Solution id to move deleted users to.
        $this->deletesolutionid = get_config('block_importqueue', 'deletesolutionid');
        parent::__construct($provider, $manual);
    }

    /**
     * Mainline for running the import, if import is finished mark queued job as finished.
     *
     * @param int $targetstarttime The timestamp representing the theoretical
     *                             time when this task was meant to be run
     * @param int $lastruntime     The last time the export was run
     *                             (N/A for import)
     * @param int $maxruntime      The max time in seconds to complete import
     *                             default: 0 => unlimited time
     * @param object $state        Previous ran state data to continue from
     *
     * @return object              State data to pass back on re-entry,
     *                             null on success!
     *         ->result            false on error, i.e. time limit exceeded.
     */
    public function run($targetstarttime = 0, $lastruntime = 0, $maxruntime = 0, $state = null) {
        global $DB;
        if (empty($this->queueid)) {
            // This is a manual upload, run with out queue.
            if ($this->provider instanceof rlip_importprovider_moodlefile) {
                return parent::run($targetstarttime, $lastruntime, $maxruntime, $state);
            }
            return null;
        }

        $record = $DB->get_record('dhimport_importqueue', array('id' => $this->queueid));
        if (empty($record)) {
             // Should never happen.
             return null;
        }
        // Queued import is in progress.
        $record->status = 3;
        $DB->update_record('dhimport_importqueue', $record);
        $result = parent::run($targetstarttime, $lastruntime, $maxruntime, $state);
        if ($result !== null) {
            // Job not is finished and state is saved for next cron job run.
            $record->status = 0;
            $DB->update_record('dhimport_importqueue', $record);
            return $result;
        }
        // Queued import has been completed.
        $count = $DB->count_records('dhimport_importqueuelog', array('queueid' => $record->id, 'status' => 0));
        if ($count) {
            // There is errors.
            $record->status = 2;
        } else {
            $record->status = 1;
        }
        $DB->update_record('dhimport_importqueue', $record);
        $entities = array('user', 'enrolment', 'course');
        $files = array();
        do {
            if ($maxruntime && (time() - $targetstarttime) > $maxruntime) {
                return $result;
            }
            $provider = new importqueueprovidercsv($entities, $files);
            if ($provider->get_queueid()) {
                // There is another request to import in the queue.
                $import = new rlip_importplugin_importqueue($provider, false);
                $result = $import->run($targetstarttime, $lastruntime, $maxruntime, null);
                if ($result !== null) {
                    // Job not is finished and state is saved for next cron job run.
                    return $result;
                }
            }
        } while ($provider->get_queueid());
        return $result;
    }

    /**
     * Entry point for processing a single create record, checks if record can be created.
     *
     * @param string $entity The type of entity
     * @param object $record One record of import data
     * @param string $filename Import file name to user for logging
     * @param boolean $isupdate True if updating record, false if not.
     * @return boolean true on success, otherwise false
     */
    public function create($entity, $record, $filename, $isupdate = false) {
        global $DB;
        $usersolutionidfield = $this->usersolutionidfield;

        // Check to see if the user exists when updating.
        $status = $this->userstatus($record);
        if ($isupdate && $status != IMPORTQUEUE_EXISTS) {
            $this->linenumber++;
            if ($status == IMPORTQUEUE_USERDELETED) {
                $this->fslogger->log_failure(get_string('failuserdeleted' , 'dhimport_importqueue', $record),
                        0, $filename, $this->linenumber, $record, 'user');
                return false;
            } else {
                $this->fslogger->log_failure(get_string('failnouser' , 'dhimport_importqueue', $record),
                        0, $filename, $this->linenumber, $record, 'user');
                return false;
            }
        }

        // Ensures user does not exist and is not deleted.
        if (!$isupdate && $status != IMPORTQUEUE_DOESNOTEXIST) {
                $this->linenumber++;
            if ($status == IMPORTQUEUE_USERDELETED) {
                $this->fslogger->log_failure(get_string('failuserdeleted' , 'dhimport_importqueue', $record),
                        0, $filename, $this->linenumber, $record, 'user');
                return false;
            } else if ($this->canupdate($record, true)) {
                // User exist and can be updated.
                $this->fslogger->log_failure(get_string('failuserexistscanupdate' , 'dhimport_importqueue', $record),
                        0, $filename, $this->linenumber, $record, 'user');
                return false;
            } else {
                $this->fslogger->log_failure(get_string('failuserexistscannotupdate' , 'dhimport_importqueue', $record),
                        0, $filename, $this->linenumber, $record, 'user');
                return false;
            }
        }

        // Ensure user can be updated.
        if (!$this->canupdate($record, $isupdate)) {
            $this->linenumber++;
            $this->fslogger->log_failure(get_string('failcanupdate' , 'dhimport_importqueue', $record->idnumber),
                    0, $filename, $this->linenumber, $record, 'user');
            return false;
        }

        // Apply the field mapping.
        $mappedrecord = $this->apply_mapping($entity, $record);
        // Clone $record to prevent parent::process_record from removing learningpath column.
        $recordclone = json_decode(json_encode($record));
        // If solution id is being set to the deleted value than the record is being deleted, retrieve existing solution id.
        $currentsolutionid = '';
        if (!empty($recordclone->idnumber) && $recordclone->$usersolutionidfield == $this->deletesolutionid) {
            $user = $DB->get_record('user', array('idnumber' => $recordclone->idnumber));
            // Ensure user exists.
            if (!empty($user)) {
                profile_load_data($user);
                $currentsolutionid = $user->$usersolutionidfield;
            }
        }

        // Create the user first.
        $result = parent::process_record($entity, $mappedrecord, $filename);

        // If solution id is being set to the deleted value than the record is being deleted, suspend and make log entry.
        if (!empty($recordclone->idnumber) && $recordclone->$usersolutionidfield == $this->deletesolutionid) {
            $user = $DB->get_record('user', array('idnumber' => $recordclone->idnumber));
            // Ensure user exists.
            if (empty($user)) {
                $this->fslogger->log_failure(get_string('failuserdeleted' , 'dhimport_importqueue', $record),
                        0, $filename, $this->linenumber, $record, 'user');
                return false;
            }
            $user->suspended = 1;
            // Update user, do not change password and do not trigger event.
            user_update_user($user, false, false);
            // Remove users from learning paths.
            $elisuser = usermoodle::find(array(new field_filter('muserid', $user->id)));
            $elisuser = $elisuser->valid() ? $elisuser->current() : null;
            // If no solution id than ignore request.
            if (!empty($currentsolutionid) && !empty($elisuser)) {
                $solutionuserset = $this->auth->userset_solutionid_exists($currentsolutionid);
                // Ensure user set solution id exists.
                if (!empty($solutionuserset) && !empty($solutionuserset->usersetid)) {
                    $this->deassign_subusersets($elisuser->cuserid, $solutionuserset->usersetid);
                }
            }
            // Force logout.
            \core\session\manager::kill_user_sessions($user->id);
            // Create log entry.
            $queue = $DB->get_record('dhimport_importqueue', array('id' => $this->queueid));
            $message = "Userid: {$user->id}, Idnumber: {$user->idnumber}, Username: {$user->username},";
            $message .= " Training manager userid: {$queue->userid}, Solution id: {$currentsolutionid}";
            $event = \dhimport_importqueue\event\importqueue_import_user_deleted::create(array(
                'userid' => $queue->userid,
                'relateduserid' => $user->id,
                'other' => array(
                    'message' => $message,
                    'username' => $user->username,
                    'trainingmangerid' => $queue->userid,
                    'solutionid' => $currentsolutionid
                )
            ));
            $event->trigger();
        }

        // Check to see if it was created.
        if (!empty($recordclone->learningpath) && $this->canupdate($recordclone, true)) {
            // Validate learning path.
            if ($this->validlearningpath($recordclone->$usersolutionidfield, $recordclone->learningpath)) {
                if ($this->addtolearningpath($filename, $recordclone, $recordclone->$usersolutionidfield, $recordclone->learningpath)) {
                    $this->fslogger->log_success(get_string('successlearningpath', 'dhimport_importqueue', $recordclone),
                            0, $filename, $this->linenumber, $recordclone, "user");
                }
            } else {
                $this->fslogger->log_failure(get_string('faillearningpathinvalid', 'dhimport_importqueue', $recordclone),
                            0, $filename, $this->linenumber, $recordclone, "user");
            }
        }
        return $result;
    }

    /**
     * Remove user from all subuserssets of solution id.
     * @param int $userid ELIS user id to deassign subuser sets from.
     * @param int $usersetid Id of parent user set to locate sub users sets to deassign.
     */
    public function deassign_subusersets($userid, $usersetid) {
        global $DB;
        if (!empty($usersetid)) {
            $usersetcontextinstance = \local_elisprogram\context\userset::instance($usersetid);
            $targetusersetpath = $usersetcontextinstance->path;
        } else {
            return;
        }
        $like = $DB->sql_like('ctx.path', '?', true, true, false);

        $sql = "SELECT clst.id
                  FROM {".userset::TABLE."} clst
                  JOIN {context} ctx ON ctx.instanceid = clst.id
                       AND ctx.contextlevel = ?
                 WHERE {$like}
                       AND ctx.instanceid != ?";

        $params = array(CONTEXT_ELIS_USERSET, "{$targetusersetpath}/%", $usersetcontextinstance->id);
        $usersets = $DB->get_records_sql($sql, $params);
        foreach ($usersets as $userset) {
            cluster_deassign_user($userset->id, $userid);
        }
    }

    /**
     * Entry point for processing a single record, checks if record is in userset.
     *
     * @param string $entity The type of entity
     * @param object $record One record of import data
     * @param string $filename Import file name to user for logging
     *
     * @return boolean true on success, otherwise false
     */
    public function process_record($entity, $record, $filename) {
        global $DB;
        // Only need to override processing of user upload files.
        if ($entity != 'user') {
            return parent::process_record($entity, $record, $filename);
        }

        $usersolutionidfield = $this->usersolutionidfield;

        if (empty($record->$usersolutionidfield)) {
            $this->linenumber++;
            $this->fslogger->log_failure(get_string('failsolutionidnotset' , 'dhimport_importqueue', $usersolutionidfield),
                    0, $filename, $this->linenumber, $record, 'user');
            return false;
        }

        // This should not happen but checking to make sure.
        if (!$this->validsolutionid($record->$usersolutionidfield)) {
            $this->linenumber++;
            $this->fslogger->log_failure(get_string('failsolutionid' , 'dhimport_importqueue', $record->$usersolutionidfield),
                    0, $filename, $this->linenumber, $record, 'user');
            return false;
        }

        $action = 'create';
        if (!empty($record->action)) {
            $action = $record->action;
        }

        switch ($action) {
            case 'create':
                return $this->create($entity, $record, $filename);
                break;
            case 'update':
                return $this->create($entity, $record, $filename, true);
                break;
            default:
                $this->linenumber++;
                $this->fslogger->log_failure(get_string('failaction' , 'dhimport_importqueue', $action),
                        0, $filename, $this->linenumber, $record, 'user');
                return false;
        }
    }

    /**
     * Detimine if solution id string has a valid solution id user set associated with it.
     * @param string $solutionid Solution id string.
     * @return boolean True on is valid, false on is not.
     */
    public function validsolutionid($solutionid) {
        // Check cache in solution id map to prevent extra query for each record insert.
        if (isset($this->solutionidmap[$solutionid])) {
            if (!$this->solutionidmap[$solutionid]) {
                return false;
            }
            return $this->solutionidmap[$solutionid];
        }
        // Check if solution id is the deletion solution id.
        if ($solutionid == $this->deletesolutionid) {
            return true;
        }
        // Check if the User's User Set exists.
        $solutionuserset = $this->auth->userset_solutionid_exists($solutionid);
        $this->solutionidmap[$solutionid] = $solutionuserset;
        if (!$solutionuserset) {
            return false;
        }
        return $solutionuserset;
    }

    /**
     * Detimine if learning path name is valid learning path for solution id user set.
     * @param string $solutionid Solution id string.
     * @param string $learningpath Learning path display name.
     * @return boolean True on is valid, false on is not.
     */
    public function validlearningpath($solutionid, $learningpath) {
        global $DB;
        // Retrieve solution id user set.
        $solutionuserset = $this->validsolutionid($solutionid);
        if (!$solutionuserset) {
            return false;
        }
        // Check cache in learning path map to prevent extra query for each record insert.
        if (isset($this->learningpathmap[$solutionid.'-'.$learningpath])) {
            return $this->learningpathmap[$solutionid.'-'.$learningpath];
        }
        // Retrieve a User Set whose parent is equal to the solution id and whose display name is equal to Learning Path display name (Subset).
        $userset = userset::find(array(
                new field_filter('parent', $solutionuserset->usersetid),
                new field_filter('displayname', $learningpath)
        ));
        // If a valid user set return the user set other wise. False.
        if ($userset->valid()) {
            $userset = $userset->current();
            $this->learningpathmap[$solutionid.'-'.$learningpath] = $userset;
            return $userset;
        }
        $this->learningpathmap[$solutionid.'-'.$learningpath] = false;
        return false;
    }

    /**
     * Add user to learning path.
     * @param string $filename Filename current being processed.
     * @param object $record Current record being processed.
     * @param string $solutionid Solution id string.
     * @param string $learningpath Learning path display name.
     * @return boolean True on success, false on failure.
     */
    public function addtolearningpath($filename, $record, $solutionid, $learningpath) {
        global $DB, $CFG;
        $email = $record->email;
        $muser = $DB->get_record('user', array('username' => $email, 'deleted' => 0, 'mnethostid' => (string)$CFG->mnet_localhost_id));
        if (empty($muser)) {
            $a = new stdClass();
            $a->email = $email;
            $a->learningpath = $learningpath;
            $this->fslogger->log_failure(get_string('faillearningpathinvaliduser', 'dhimport_importqueue', $a),
                0, $filename, $this->linenumber, $record, "user");
            return false;
        }
        // Retrieve the ELIS user record.
        $elisuser = usermoodle::find(array(new field_filter('muserid', $muser->id)));

        $userset = $this->validlearningpath($solutionid, $learningpath);
        if (empty($userset)) {
            $a = new stdClass();
            $a->email = $email;
            $a->learningpath = $learningpath;
            $this->fslogger->log_failure(get_string('faillearningpathinvalid', 'dhimport_importqueue', $a),
                0, $filename, $this->linenumber, $record, "user");
            return false;
        }

        $elisuser = ($elisuser->valid() && !is_null($userset)) ? $elisuser->current() : null;

        if (!is_null($elisuser)) {
            cluster_manual_assign_user($userset->id, $elisuser->cuserid);
            return true;
        } else {
            $a = new stdClass();
            $a->email = $email;
            $a->learningpath = $learningpath;
            $this->fslogger->log_failure(get_string('faillearningpathinvaliduser', 'dhimport_importqueue', $a),
                0, $filename, $this->linenumber, $record, "user");
            return false;
        }
        return false;
    }

    /**
     * Check status of a user, check if the user exists or deleted.
     * @param object $newrecord Record to be updated or created.
     * @param boolean $updateonly False if checking if a record can be added. True if a record is required to exist to update.
     * @return boolean True if record can be updated or created.
     */
    public function userstatus($newrecord, $updateonly = false) {
        global $DB, $CFG;
        $record = $DB->get_record('user', array('idnumber' => $newrecord->idnumber, 'deleted' => 0, 'mnethostid' => (string)$CFG->mnet_localhost_id));
        if (empty($record)) {
            return IMPORTQUEUE_DOESNOTEXIST;
        }
        // Retrieve custom user fields.
        profile_load_data($record);
        $usersolutionidfield = $this->usersolutionidfield;
        if (!empty($record->$usersolutionidfield) && $this->deletesolutionid == $record->$usersolutionidfield) {
            // User is deleted.
            return IMPORTQUEUE_USERDELETED;
        }
        return IMPORTQUEUE_EXISTS;
    }

    /**
     * Check if a record can be updated.
     * @param object $newrecord Record to be updated or created.
     * @param boolean $updateonly False if checking if a record can be added. True if a record is required to exist to update.
     * @return boolean True if record can be updated or created.
     */
    public function canupdate($newrecord, $updateonly = false) {
        global $DB, $CFG;
        $record = $DB->get_record('user', array('idnumber' => $newrecord->idnumber, 'deleted' => 0, 'mnethostid' => (string)$CFG->mnet_localhost_id));
        if (empty($record)) {
            // If the user cannot be found than they could be created.
            return !$updateonly;
        }
        // If site admin or has sitewide access than update or create is allowed.
        $queue = $DB->get_record('dhimport_importqueue', array('id' => $this->queueid));
        $context = context_system::instance();
        if (is_siteadmin($queue->userid) || has_capability('block/importqueue:sitewide', $context, $queue->userid)) {
            return true;
        }
        // Retrieve custom user fields.
        profile_load_data($record);
        $usersolutionidfield = $this->usersolutionidfield;
        // Check if solution id is the deletion solution id.
        if (!empty($newrecord->$usersolutionidfield) && $newrecord->$usersolutionidfield == $this->deletesolutionid) {
            $auth = get_auth_plugin('kronosportal');
            $solutionid = $auth->get_user_solution_id($queue->userid);
            // Ensure the user owns the user.
            if (!empty($record->$usersolutionidfield) && $record->$usersolutionidfield == $solutionid) {
                return true;
            }
            return false;
        }
        // Check to see if solution id's match.
        if (empty($record->$usersolutionidfield) || empty($newrecord->$usersolutionidfield) || $record->$usersolutionidfield !== $newrecord->$usersolutionidfield) {
            return false;
        }
        return true;
    }
}
