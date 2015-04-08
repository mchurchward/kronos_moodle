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
class importqueue_table extends table_sql {
    /**
     * Constructor
     * @param int $uniqueid all tables have to have a unique id, this is used
     *      as a key when storing table properties like sort order in the session.
     */
    public function __construct($uniqueid) {
        global $CFG, $USER;
        parent::__construct($uniqueid);
        // Define the list of columns to show.
        $columns = array('status', 'timemodified', 'id');
        $this->define_columns($columns);
        // Define the titles of columns to show in header.
        $headers = array(get_string('columnstatus', 'block_importqueue'));
        $headers[] = get_string('columntimemodified', 'block_importqueue');
        $headers[] = get_string('columnlogs', 'block_importqueue');
        $this->define_headers($headers);
        $this->sortable(true, 'id', SORT_DESC);
        // Set sql for table.
        $fields = "q.id, q.status, q.timemodified";
        $from = "{dhimport_importqueue} q";
        $this->set_sql($fields, $from, 'userid = ?', array ($USER->id));
        $this->no_sorting("id");
        $this->define_baseurl("$CFG->wwwroot/blocks/importqueue/queuestatus.php");
    }

    /**
     * This function is called for each data row to allow processing of the
     * timemodified value.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return formated date.
     */
    public function col_timemodified($values) {
        return userdate($values->timemodified);
    }

    /**
     * Converts int status to text.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return text status..
     */
    public function col_status($values) {
        $text = '';
        $options = array();
        switch ($values->status) {
            case 0:
                $text = get_string('queued', 'block_importqueue');
                $options['style'] = 'color: green';
                break;
            case 1:
                $text = get_string('complete', 'block_importqueue');
                break;
            case 2:
                $text = get_string('errors', 'block_importqueue');
                $options['style'] = 'color: red';
                break;
            case 3:
                $text = get_string('processing', 'block_importqueue');
                $options['style'] = 'color: green';
                break;
        }
        return html_writer::tag('span', $text, $options);
    }

    /**
     * The id column is replaced with a link to import logs.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return link to logs.
     */
    public function col_id($values) {
        global $CFG;
        $html = '';
        if ($values->status == 2) {
            $link = new moodle_url($CFG->wwwroot.'/blocks/importqueue/queuelog.php', array('id' => $values->id));
            $html = html_writer::tag('a', get_string('logs', 'block_importqueue'), array('href' => $link));
            // Show link to show errors only.
            $link = new moodle_url($CFG->wwwroot.'/blocks/importqueue/queuelog.php', array('id' => $values->id, 'errors' => 1));
            $options = array('href' => $link, 'style' => 'color: red');
            $html .= ' '.html_writer::tag('a', get_string('viewerrors', 'block_importqueue'), $options);
        } else if ($values->status != 0) {
            $link = new moodle_url($CFG->wwwroot.'/blocks/importqueue/queuelog.php', array('id' => $values->id));
            $html = html_writer::tag('a', get_string('logs', 'block_importqueue'), array('href' => $link));
        }
        return $html;
    }
}
