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
class vmcourses_table extends table_sql {
    /**
     * Constructor
     * @param int $uniqueid all tables have to have a unique id, this is used
     *      as a key when storing table properties like sort order in the session.
     */
    public function __construct($uniqueid) {
        global $CFG;
        parent::__construct($uniqueid);
        // Define the list of columns to show.
        $columns = array('coursename', 'imageid', 'imagetype', 'instances', 'id');
        $this->define_columns($columns);
        // Define the titles of columns to show in header.
        $headers = array(get_string('coursename', 'mod_kronossandvm'));
        $headers[] = get_string('imageid', 'mod_kronossandvm');
        $headers[] = get_string('imagetype', 'mod_kronossandvm');
        $headers[] = get_string('instances', 'mod_kronossandvm');
        $headers[] = '';
        $this->define_headers($headers);
        $this->sortable(true, 'id', SORT_DESC);
        // Set sql for table.
        $fields = "c.id, c.coursename, c.imageid, c.imagetype, (SELECT COUNT(*) FROM {kronossandvm} k WHERE k.otcourseid = c.id) instances";
        $from = "{vm_courses} c";
        $this->set_sql($fields, $from, '1 = 1', null);
        $this->no_sorting("id");
        $this->define_baseurl("$CFG->wwwroot/mod/vmcourses.php");
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
     * The id column is replaced with a link to a list of instances for the template.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return instances.
     */
    public function col_instances($values) {
        global $CFG;
        if (!empty($values->instances)) {
            $link = new moodle_url($CFG->wwwroot.'/mod/kronossandvm/vmcourses.php', array('action' => 'instances', 'id' => $values->id));
            return html_writer::tag('a', $values->instances, array('href' => $link));
        }
        return $values->instances;
    }

    /**
     * The id column is replaced with a edit and delete link.
     *
     * @param object $values Contains object with all the values of record.
     * @return $string Return link to edit and delete link html.
     */
    public function col_id($values) {
        global $CFG;
        $link = new moodle_url($CFG->wwwroot.'/mod/kronossandvm/vmcourses.php', array('action' => 'edit', 'id' => $values->id));
        $html = html_writer::tag('a', get_string('edit'), array('href' => $link));
        $link = new moodle_url($CFG->wwwroot.'/mod/kronossandvm/delete.php', array('id' => $values->id));
        $options = array('href' => $link, 'onclick' => "return confirm('".get_string('confirmdelete', 'mod_kronossandvm')."')");
        $html .= ' ';
        $html .= html_writer::tag('a', get_string('delete'), $options);
        return $html;
    }
}
