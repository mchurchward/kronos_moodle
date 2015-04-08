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
 * Import queue block.
 *
 * @package    block_importqueue
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2015 Remote Learner.net Inc http://www.remote-learner.net
 */

/**
 * Form for editing import queue block instances.
 */
class block_importqueue_edit_form extends block_edit_form {

    /**
     * Add form elements for configuration.
     */
    public function specific_definition($mform) {
        global $CFG;
        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_importqueue'));
        $mform->setType('config_title', PARAM_TEXT);
        $mform->setDefault('config_title', get_string('newimportqueue', 'block_importqueue', $CFG));

        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' => $this->block->context);
        $element = $mform->addElement('editor', 'config_text', get_string('configcontent', 'block_importqueue'), null, $editoroptions);
        $mform->addRule('config_text', null, 'required', null, 'client');
        $mform->setType('config_text', PARAM_RAW);
        // Set default text for content.
        $element->setValue(array('text' => get_string('newimportqueuecontent', 'block_importqueue', $CFG)));
    }
}
