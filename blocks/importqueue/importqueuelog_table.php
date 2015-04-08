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

require_once("$CFG->libdir/tablelib.php");

/**
 * Table of import requests.
 *
 * @package    block_importqueue
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2015 Remote Learner.net Inc http://www.remote-learner.net
 */
class importqueuelog_table extends table_sql {
    /**
     * Constructor
     * @param int $uniqueid all tables have to have a unique id, this is used
     *      as a key when storing table properties like sort order in the session.
     */
    public function __construct($uniqueid, $queueid) {
        global $CFG, $USER;
        parent::__construct($uniqueid);
        // Define the list of columns to show.
        $columns = array('status', 'message', 'filename', 'line', 'timecreated');
        $this->define_columns($columns);
        // Define the titles of columns to show in header.
        $headers = array(get_string('columnstatus', 'block_importqueue'));
        $headers[] = get_string('columnmessage', 'block_importqueue');
        // Entity description ends up being the file line number.
        $headers[] = get_string('columntype', 'block_importqueue');
        $headers[] = get_string('columnline', 'block_importqueue');
        $headers[] = get_string('columntimecreated', 'block_importqueue');
        $this->define_headers($headers);
        // Set sql for table.
        $fields = "ql.id, ql.message, ql.filename, ql.line, ql.status, ql.timecreated";
        $from = "{dhimport_importqueuelog} ql, {dhimport_importqueue} q";
        $where = 'q.id = ql.queueid AND ql.queueid = ? AND q.userid = ?';
        $errors = optional_param('errors', 0, PARAM_INT);
        if ($errors == 1) {
            // Show only failed logs.
            $where .= ' AND ql.status = 0 ';
        } else if ($errors == 2) {
            // Show only success logs.
            $where .= ' AND ql.status = 1 ';
        }
        $this->set_sql($fields, $from, $where, array($queueid, $USER->id));
        $this->no_sorting("id");
        $this->define_baseurl("$CFG->wwwroot/blocks/importqueue/queuestatus.php");
    }

    /**
     * This function is called for each data row to allow processing of the
     * timecreated value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return formated date.
     */
    public function col_timecreated($values) {
        return userdate($values->timecreated);
    }

    /**
     * Converts int status to text.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return text status..
     */
    public function col_status($values) {
        switch ($values->status) {
            case 1:
                return get_string('success', 'block_importqueue');
            case 0:
                return html_writer::tag('span', get_string('error', 'block_importqueue'), array('style' => 'color: red'));
        }
        return '';
    }

    /**
     * Converts filename to type.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return text type.
     */
    public function col_filename($values) {
        return preg_replace('/([0-9]+).csv$/', '', $values->filename);
    }

    /**
     * Converts int status to text.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return text status.
     */
    public function col_message($values) {
        switch ($values->status) {
            case 1:
                return $values->message;
            case 0:
                return html_writer::tag('span', $values->message, array('style' => 'color: red'));
        }
        return $values->message;
    }
}
