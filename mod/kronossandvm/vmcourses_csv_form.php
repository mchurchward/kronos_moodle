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
 * Kronos virtual machine manager.
 *
 * @package    mod_kronossandvm
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2015 Remote Learner.net Inc http://www.remote-learner.net
 */

require_once($CFG->libdir.'/formslib.php');

/**
 * Class to define the upload csv form for virtual machine template.
 *
 * @see moodleform
 */
class vmcourses_csv_form extends moodleform {

    /**
     * Method that defines all of the elements of the form.
     *
     */
    public function definition() {
        $mform =& $this->_form;
        $mform->addElement('filepicker', 'csvfile', get_string('file'), null,
                array('maxbytes' => 1048576, 'accepted_types' => '*'));
        $mform->addElement('submit', 'submitbutton', get_string('upload'));
    }
}
