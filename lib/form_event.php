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
 * Remote Learner Update Manager Schedule
 *
 * @package    blocks
 * @subpackage rlagent
 * @author     Remoter-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (c) 2012 Remote Learner.net Inc http://www.remote-learner.net
 */

require_once($CFG->libdir . '/formslib.php');

/**
 * Form event class for editing an event's date and time
 */
class form_event extends moodleform {
    protected $plugin = 'block_rlagent';
    /**
     * Define the form
     */
    public function definition() {
        $this->_form->addElement('header', 'eventtime', get_string('updatescheduling', $this->plugin));

        $this->_form->addElement('hidden', 'id');
        $this->_form->addElement('hidden', 'action', 'update');

        $this->_form->addelement('static', 'name', get_string('name', $this->plugin));
        $this->_form->addelement('static', 'updateperiod', get_string('updateperiod', $this->plugin));
        $this->_form->addelement('static', 'originaldate', get_string('defaultdate', $this->plugin));

        $this->_form->addElement('date_time_selector', 'scheduleddate', get_string('newdate', $this->plugin));

        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        $errors = array();

        if ($this->_form->_defaultValues['scheduleddate'] != $data['scheduleddate']) {
            $startyear  = substr($this->_form->_defaultValues['startdate'],0,4);
            $startmonth = substr($this->_form->_defaultValues['startdate'],5,2);
            $startday   = substr($this->_form->_defaultValues['startdate'],8,2);

            $endyear    = substr($this->_form->_defaultValues['enddate'],0,4);
            $endmonth   = substr($this->_form->_defaultValues['enddate'],5,2);
            $endday     = substr($this->_form->_defaultValues['enddate'],8,2);

            $starttime  = mktime(0,0,0,$startmonth,$startday,$startyear);
            $endtime    = mktime(23,59,0,$endmonth,$endday,$endyear);


            if (($data['scheduleddate'] < $starttime ) || ($endtime < $data['scheduleddate'])) {
                $errors['scheduleddate'] = get_string('notinrange', $this->plugin);
            }
        } else {
            $errors['scheduleddate'] = get_string('notchanged', $this->plugin);
        }

        return $errors;
    }
}
