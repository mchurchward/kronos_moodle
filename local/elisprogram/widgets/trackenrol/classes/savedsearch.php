<?php
/**
 * ELIS(TM): Enterprise Learning Intelligence Suite
 * Copyright (C) 2008-2016 Remote-Learner.net Inc (http://www.remote-learner.net)
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
 * @package    eliswidget_enrolment
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2016 Onwards Remote-Learner.net Inc (http://www.remote-learner.net)
 * @author     Remote-Learner.net Inc
 *
 */

namespace eliswidget_trackenrol;

require_once($CFG->dirroot.'/local/elisprogram/lib/deepsight/lib/lib.php');

/**
 * Saved search AJAX response class.
 *
 * This receives AJAX requests to save, update and delete saved searches for the Track enrol widget.
 */
class savedsearch extends \deepsight_savedsearch {

    /**
     * Constructor.
     */
    public function __construct($context, $pagename) {
        parent::__construct($context, $pagename);
        // Override permissions TBD.
        $this->cansavesearches = true;
    }

    /**
     * Save or update search.
     *
     * @param object $search Search object containing name and filters.
     * @return int Id of saved search or false on failure.
     */
    public function save($search) {
        global $DB, $USER;
        if (!$this->cansavesearches()) {
            return false;
        }
        // Validate required data for search.
        if (empty($search) || empty($search->name) || empty($search->data)) {
            return false;
        }
        if (!empty($search->contextid) && $search->contextid != $this->context->id) {
            // Set context to context we want to save to.
            $this->set_context(context::instance_by_id($search->contextid));
            if (!$this->cansavesearches()) {
                return false;
            }
        }
        $search->contextid = $this->context->id;
        $search->pagename = $this->pagename;
        $search->data = json_encode($search->data);
        if (!empty($search->fieldsort)) {
            $search->fieldsort = json_encode($search->fieldsort);
        }
        $search->userid = $USER->id;
        if (empty($search->id)) {
            // Adding.
            $id = $DB->insert_record('local_elisprogram_deepsight', $search);
        } else {
            // Saving.
            $record = $DB->get_record('local_elisprogram_deepsight', array('id' => $search->id, 'contextid' => $this->context->id));
            if (!empty($record)) {
                $DB->update_record('local_elisprogram_deepsight', $search);
                $id = $search->id;
            } else {
                unset($search->id);
                $search->contextid = $this->context->id;
                $id = $DB->insert_record('local_elisprogram_deepsight', $search);
            }
        }
        if (!empty($id) && !empty($search->isdefault)) {
            $DB->execute('UPDATE {local_elisprogram_deepsight} SET isdefault = 0 WHERE id != ? AND contextid = ? AND pagename = ?', array($id, $this->context->id, $this->pagename));
        }
        return $id;
    }

}
