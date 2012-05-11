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
 * Remote Learner Agent Block.
 *
 * @package    blocks
 * @subpackage rlagent
 * @author     Remoter-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (c) 2012 Remote Learner.net Inc http://www.remote-learner.net
 */

/**
 * The Remote Learner agent block class
 */
class block_rlagent extends block_base {

    /**
     * Set the applicable formats for this block to all
     * @return array
     */
    function applicable_formats() {
        return array('site' => true);
    }

    /**
     * Gets the content for this block
     */
    function get_content() {
        global $CFG, $DB;

        if (!has_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM))) {
            return '';
        }

        if ($this->content !== NULL) {
            return $this->content;
        }



        $select = 'status = 0 AND scheduleddate >= '. time();
        $records = $DB->get_records_select('block_rlagent_schedule', $select, array(), 'scheduleddate', '*', 0, 1);

        if (sizeof($records) == 0) {
            $text = get_string('noupdate', $this->blockname);
        } else {
            $record = reset($records);
            $date = date('g:i:s A \o\n l, \t\h\e jS \of F, Y', $record->scheduleddate);
            $text = get_string('nextupdate', $this->blockname, $date);
        }

        $settings = '<div style="float:left"><a href="'. $CFG->wwwroot .'/admin/settings.php?section=blocksettingrlagent">'
              . get_string('settings', $this->blockname) .'</a></div>';
        $schedule = '<div style="float:right"><a href="'. $CFG->wwwroot .'/blocks/rlagent/schedule.php">'
              . get_string('schedule', $this->blockname) .'</a></div>';
        $text = $text ."<br />\n". $settings . $schedule .'<br style="clear: both" />';

        $this->content = new stdClass;
        $this->content->text = $text;
        $this->content->footer = '';

        return $this->content;
    }

    /**
     * Has configuration for disabling automatic updates, scheduling updates and notifications.
     *
     * @return boolean
     */
    function has_config() {
        return true;
    }

    /**
     * Set the initial properties for the block
     */
    function init() {
        $this->blockname = get_class($this);
        $this->title = get_string('pluginname', $this->blockname);
    }

    /**
     * Are multiple instances of this block allowed?  No.
     *
     * @return bool Returns false
     */
    function instance_allow_multiple() {
        return false;
    }
}
