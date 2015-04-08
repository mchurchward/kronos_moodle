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
 * Kronos HTML block.
 *
 * @package    block_kronoshtml
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2014 Remote Learner.net Inc http://www.remote-learner.net
 */

/**
 * Form for editing Kronos HTML block instances.
 *
 * @copyright 2009 Tim Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_kronoshtml_edit_form extends block_edit_form {
    /**
     * Overridden from base class.  Add additional form elements to the block instance editing page.
     */
    protected function specific_definition($mform) {
        global $CFG, $PAGE;

        require_once($CFG->dirroot.'/local/elisprogram/lib/data/userset.class.php');
        $usersetinit = false;

        // Fields for editing HTML block title and contents.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        // Add autocomplete parameters
        $url = new moodle_url('/blocks/kronoshtml/ajax.php');
        $acdivid = 'ac_input';
        $params = array(
            'datasource' => $url->out(),
            'divid' => $acdivid,
            'blockinstanceid' => $this->block->instance->id
        );

        $PAGE->requires->css('/blocks/kronoshtml/styles.css');
        $PAGE->requires->yui_module('moodle-block_kronoshtml-usrsetautocmp', 'M.block_kronoshtml.init', array($params), null, true);

        $mform->addElement('hidden', 'config_userset', '0', array('id' => 'id_config_userset'));
        $mform->setType('config_userset', PARAM_INT);

        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_kronoshtml'));
        $mform->setType('config_title', PARAM_TEXT);

        $editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' => $this->block->context);
        $mform->addElement('editor', 'config_text', get_string('configcontent', 'block_kronoshtml'), null, $editoroptions);
        $mform->addRule('config_text', null, 'required', null, 'client');
        // XSS is prevented when printing the block contents and serving files.
        $mform->setType('config_text', PARAM_RAW);

        if (!empty($CFG->block_kronoshtml_allowcssclasses)) {
            $mform->addElement('text', 'config_classes', get_string('configclasses', 'block_kronoshtml'));
            $mform->setType('config_classes', PARAM_TEXT);
            $mform->addHelpButton('config_classes', 'configclasses', 'block_kronoshtml');
        }

        // Add an auto-complete field.
        if (isset($this->block->config) && isset($this->block->config->userset)) {
            $userset = new userset($this->block->config->userset);
            $usersetinit = true;
        }

        $label = html_writer::tag('label', get_string('selecteduserset', 'block_kronoshtml'), array('for' => $acdivid));
        $labeldiv = html_writer::tag('div', $label, array('class' => 'fitemtitle'));

        // Retrieve the previously selected User Set.
        $text = '';
        if ($usersetinit && $userset->name) {
            $text = format_string($userset->name);
        }

        $input = html_writer::empty_tag('input', array(
            'id' => $acdivid,
            'type' => 'text',
            'size' => 51,
            'maxlength' => '50',
            'placeholder' => get_string('placeholder', 'block_kronoshtml'),
            'value' => $text
        ));

        $inputdescription = '';
        $configuserset = optional_param('config_userset', 0, PARAM_INT);
        $issubmitted = optional_param('_qf__block_kronoshtml_edit_form', 0, PARAM_INT);
        if ($issubmitted && empty($configuserset)) {
            $inputdescription = html_writer::start_tag('p');
            $inputdescription .= html_writer::tag('span', get_string('missinguserset',  'block_kronoshtml'), array('class' => 'error'));
            $inputdescription .= html_writer::end_tag('p');
        }
        $inputdescription .= html_writer::tag('p', get_string('autocompletedesc', 'block_kronoshtml'));

        $inputdiv = html_writer::tag('div', $input.$inputdescription, array('id' => 'ac-div', 'class' => 'felement ftext yui3-skin-sam', 'name' => $acdivid));

        $maindiv = html_writer::tag('div', $labeldiv.$inputdiv, array('id' => "fitem_{$acdivid}", 'class' => 'fitem fitem_ftext'));
        $mform->addElement('html', $maindiv);
    }

    /**
     * Validation of userset.
     *
     * @param array $data Array of data for fields.
     * @files array Array of files uploaded.
     * @return array Array of errors.
     */
    public function validation($data, $files) {
        $errors = array();
        if (empty($data['config_userset'])) {
            $errors['config_userset'] = 'error';
        }
        return $errors;
    }

    /**
     * Overridden from base class. Set deafult data.
     */
    public function set_data($defaults) {
        if (!empty($this->block->config) && is_object($this->block->config)) {
            $text = $this->block->config->text;
            $draftideditor = file_get_submitted_draft_itemid('config_text');

            if (empty($text)) {
                $currenttext = '';
            } else {
                $currenttext = $text;
            }

            $text = file_prepare_draft_area($draftideditor, $this->block->context->id, 'block_kronoshtml', 'content', 0, array('subdirs' => true), $currenttext);
            $defaults->config_text['text'] = $text;
            $defaults->config_text['itemid'] = $draftideditor;
            $defaults->config_text['format'] = $this->block->config->format;
        } else {
            $text = '';
        }

        if (!$this->block->user_can_edit() && !empty($this->block->config->title)) {
            // If a title has been set but the user cannot edit it format it nicely.
            $title = $this->block->config->title;
            $defaults->config_title = format_string($title, true, $this->page->context);
            // Remove the title from the config so that parent::set_data doesn't set it.
            unset($this->block->config->title);
        }

        // Have to delete text here, otherwise parent::set_data will empty content of editor.
        unset($this->block->config->text);
        parent::set_data($defaults);
        // Restore text.
        if (!isset($this->block->config)) {
            $this->block->config = new stdClass();
        }
        $this->block->config->text = $text;
        if (isset($title)) {
            // Reset the preserved title.
            $this->block->config->title = $title;
        }
    }
}
