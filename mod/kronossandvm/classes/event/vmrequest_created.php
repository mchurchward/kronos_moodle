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
 * Kronos virtual machine request created.
 *
 * @package    mod_kronossandvm
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2014 Remote Learner.net Inc http://www.remote-learner.net
 */

namespace mod_kronossandvm\event;
defined('MOODLE_INTERNAL') || die();
/**
 * The mod_kronossandvm virtual machine request event.
 **/
class vmrequest_created extends \core\event\base {
    /**
     * Initialize data.
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'vm_requests';
    }

    /**
     * Return name of event.
     */
    public static function get_name() {
        return get_string('eventvmrequestcreated', 'mod_kronossandvm');
    }

    /**
     * Return description of event.
     */
    public function get_description() {
        return get_string('eventvmrequestcreateddescription', 'mod_kronossandvm', $this);
    }

    /**
     * Migration of add_to_log call.
     */
    public function get_legacy_logdata() {
        return array($this->courseid, 'kronossandvm', 'view', 'view.php?id='.$this->contextinstanceid, $this->objectid, $this->contextinstanceid);
    }
}
