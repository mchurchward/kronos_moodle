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
 * Remote Learner Agent Schedule
 *
 * @package    blocks
 * @subpackage rlagent
 * @author     Remoter-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (c) 2012 Remote Learner.net Inc http://www.remote-learner.net
 */

class table_schedule extends table_sql {
    const NOT_STARTED = 0;
    const COMPLETED   = 1;
    const ERROR       = 2;
    const SKIPPED     = 3;
    const CANCELLED   = 4;

    protected $strings = array(
        self::NOT_STARTED => 'notstarted',
        self::COMPLETED   => 'completed',
        self::ERROR       => 'error',
        self::SKIPPED     => 'skipped',
        self::CANCELLED   => 'cancelled',
    );

    public $dateformat = 'Y-m-d g:i:s A';
    protected $block = 'block_rlagent';

    /**
     * Format the original date
     *
     * @param array $row A row of data
     */
    function col_originaldate($row) {
        return date($this->dateformat, $row->originaldate);
    }

    /**
     * Format the scheduled date
     *
     * @param array $row A row of data
     */
    function col_scheduleddate($row) {
        return date($this->dateformat, $row->scheduleddate);
    }

    /**
     * Format the scheduled date
     *
     * @param array $row A row of data
     */
    function col_status($row) {
        return get_string($this->strings[$row->status], $this->block);
    }

    /**
     * Print headers
     *
     * This table uses no headers.
     */
    function print_headers() {
    }

    /**
     * Override row printing to print nice rows
     *
     * $row[0] = original date
     * $row[1] = scheduled date
     * $row[2] = period start date
     * $row[3] = period end date
     * $row[4] = description/title
     * $row[5] = status
     * $row[6] = log
     */
    function print_row($row, $classname = '') {
        static $suppress_lastrow = NULL;
        static $oddeven = 1;
        $rowclasses = array('r' . $oddeven);
        $oddeven = $oddeven ? 0 : 1;

        if ($classname) {
            $rowclasses[] = $classname;
        }

        echo html_writer::start_tag('tr', array('class' => implode(' ', $rowclasses)));

       // If we have a separator, print it
        if ($row === NULL) {
            $colcount = count($this->columns);
            echo html_writer::tag('td', html_writer::tag('div', '',
                    array('class' => 'tabledivider')), array('colspan' => $colcount));

        } else {
            $a = new object();
            $a->start = $row[2];
            $a->end   = $row[3];

            $content = array();
            $content[] = html_writer::tag('div', $row[4], array('class' => 'title'));
            $content[] = html_writer::tag('div', get_string('updateperiod', $this->block, $a));

            if ($row[0] != $row[1]) {
                $content[] = html_writer::tag('div', get_string('defaultdate', $this->block, $row[0]));
            }
            $content[] = html_writer::tag('div', get_string('scheduleddate', $this->block, $row[1]));

            if (! empty($log)) {
                $log = get_string('log', $this->block) . html_writer::tag('div', $row[6]);
                $content[] = html_writer::tag('div', $log);
            }
            $div  = html_writer::tag('div', implode("\n", $content), array('class' => 'event'));
            echo html_writer::tag('td', $div);
        }

        echo html_writer::end_tag('tr');

        $suppress_enabled = array_sum($this->column_suppress);
        if ($suppress_enabled) {
            $suppress_lastrow = $row;
        }
    }
}