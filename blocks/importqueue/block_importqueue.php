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

require_once($CFG->dirroot.'/blocks/importqueue/lib.php');

/**
 * Import queue block.
 *
 * @package    block_importqueue
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2015 Remote Learner.net Inc http://www.remote-learner.net
 */
class block_importqueue extends block_base {

    /** @var string Holds the block language string name. */
    public $blockname = null;

    /**
     * Set the initial properties for the block
     */
    public function init() {
        $this->blockname = get_class($this);
        $this->title = get_string('pluginname', $this->blockname);
    }

    /**
     * Overridden from parent class.  This block has a global settings page.
     */
    public function has_config() {
        return true;
    }

    /**
     * Overridden from parent class. Sets the formats.
     */
    public function applicable_formats() {
        return array('all' => true);
    }

    /**
     * Overridden from parent class. This game sets the formats.
     */
    public function specialization() {
        $this->title = isset($this->config->title) ? format_string($this->config->title) : format_string(get_string('newimportqueue', $this->blockname));
    }

    /**
     * Overridden from parent class. Set multiple instances.
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Overridden from parent class.
     */
    public function get_content() {
        global $CFG, $DB, $USER;
        $this->content = new stdClass();
        $allowed = importqueue_isallowed();
        if ($allowed) {
            $this->content->text = !empty($this->config->text['text']) ? $this->config->text['text'] : get_string('newimportqueuecontent', $this->blockname, $CFG);
        } else {
            $this->content->text = '';
        }
        $this->content->footer = '';
        return $this->content;
    }
}
