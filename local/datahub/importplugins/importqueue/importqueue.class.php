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
require_once($CFG->dirroot.'/local/datahub/importplugins/version1elis/version1elis.class.php');
require_once($CFG->dirroot.'/local/datahub/importplugins/importqueue/importqueueprovidercsv.php');

/**
 * Test plugin used to test a simple entity and action
 */
class rlip_importplugin_importqueue extends rlip_importplugin_version1elis {
    /**
     * @var array Required variable definition.
     */
    static public $import_fields_importqueueentity_importqueueaction = array('importqueuefield');
    /**
     * @var int $queueid id of queue currently being processed.
     */
    private $queueid = 0;

    /**
     * Import queue plugin constructor
     *
     * @param object $provider The import file provider that will be used to
     *                         obtain any applicable import files
     * @param boolean $manual  Set to true if a manual run
     */
    public function __construct($provider = null, $manual = false) {
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
}
