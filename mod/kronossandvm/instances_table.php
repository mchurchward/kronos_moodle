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
 * Table of virtual machine templates.
 *
 * @package    mod_kronossandvm
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2015 Remote Learner.net Inc http://www.remote-learner.net
 */
class instances_table extends table_sql {
    /**
     * Constructor
     * @param string $uniqueid all tables have to have a unique id, this is used
     *      as a key when storing table properties like sort order in the session.
     * @param int $templateid Id of virtual machine template.
     * @param string $action current action.
     */
    public function __construct($uniqueid, $templateid, $action) {
        global $CFG;
        parent::__construct($uniqueid);
        // Define the list of columns to show.
        $columns = array('course', 'name');
        $this->define_columns($columns);
        // Define the titles of columns to show in header.
        $headers = array(get_string('course'), get_string('activity'));
        $this->define_headers($headers);
        $this->sortable(true, 'course', SORT_DESC);
        // Set sql for table.
        $fields = "c.id, c.fullname course, k.name";
        $from = "{kronossandvm} k, {course} c";
        $this->set_sql($fields, $from, 'c.id = k.course AND k.otcourseid = ?', array($templateid));
        $this->no_sorting("id");
        $this->define_baseurl("$CFG->wwwroot/mod/vmcourses.php", array('action' => $action, 'id' => $templateid));
    }

    /**
     * The course full name is replaced with a link to the course.
     *
     * @param object $course Contains object with all the values of record.
     * @return $string Return link to course.
     */
    public function col_course($course) {
        global $CFG;
        $link = new moodle_url($CFG->wwwroot.'/course/view.php', array('id' => $course->id));
        return html_writer::link($link, $course->course);
    }
}
