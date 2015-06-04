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
 * Import upload form.
 *
 * @package    block_importqueue
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2015 Remote Learner.net Inc http://www.remote-learner.net
 */

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/blocks/importqueue/importqueue.class.php');
require_once($CFG->dirroot.'/auth/kronosportal/lib.php');

/**
 * Class to define the upload csv form for virtual machine template.
 *
 * @see moodleform
 */
class importqueue_form extends moodleform {
    /**
     * @var int $context Context id.
     */
    private $context = null;
    /**
     * @var int $userset Userset id.
     */
    private $userset = null;
    /**
     * @var int $error Error message.
     */
    private $error = null;
    /**
     * @var string $mode Form mode.
     */
    private $mode = 'create';
    /**
     * @var array $users Users being processed.
     */
    private $users = array();
    /**
     * @var int $count Number of rows counted.
     */
    private $count = 0;

    /**
     * Constructor, set context id and userset for autocomplete.
     *
     * @param int $context id.
     * @param string $mode Mode for upload, create for creating new users, update for updating users.
     */
    public function __construct($context, $userset = 0, $mode = 'create') {
        $this->context = $context;
        $this->mode = $mode;
        parent::__construct();
    }

    /**
     * Validation of userset.
     */
    public function validation($data, $files) {
        $errors = array();
        if ($this->validate_draft_files() !== true) {
            $errors['csvfile'] = get_string('errorcsvfile', 'block_importqueue');
        }
        if ($this->isselect()) {
            if (empty($data['config_userset'])) {
                $errors['config_userset'] = get_string('errorconfig_userset', 'block_importqueue');
            }
        }
        return $errors;
    }


    /**
     * Add autocomplete to form.
     */
    private function addautocomplete() {
        global $PAGE;

        $mform =& $this->_form;

        // Add autocomplete parameters.
        $url = new moodle_url('/blocks/importqueue/ajax.php');
        $acdivid = 'ac_input';
        $params = array(
            'datasource' => $url->out(),
            'divid' => $acdivid,
            'blockinstanceid' => $this->context
        );
        $PAGE->requires->css('/blocks/importqueue/styles.css');
        $PAGE->requires->yui_module('moodle-block_importqueue-usrsetautocmp', 'M.block_importqueue.init', array($params), null, true);
        $mform->addElement('hidden', 'config_userset', '0', array('id' => 'id_config_userset'));
        $mform->setType('config_userset', PARAM_INT);
        // Add an auto-complete field.
        $usersetinit = false;
        if (!empty($this->userset)) {
            $userset = new userset($this->userset);
            $usersetinit = true;
        }
        $label = html_writer::tag('label', get_string('selecteduserset', 'block_importqueue'), array('for' => $acdivid));
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
            'placeholder' => get_string('placeholder', 'block_importqueue'),
            'value' => $text,
            'name' => 'usersetname'
        ));
        $configuserset = optional_param('config_userset', 0, PARAM_INT);
        $issubmitted = optional_param('_qf__importqueue_form', 0, PARAM_INT);
        $inputdescription = '';
        if ($issubmitted && empty($configuserset)) {
            $inputdescription = html_writer::start_tag('p');
            $inputdescription .= html_writer::tag('span', get_string('missinguserset', 'block_importqueue'), array('class' => 'error'));
            $inputdescription .= html_writer::end_tag('p');
        }
        $inputdescription .= html_writer::tag('p', get_string('autocompletedesc', 'block_importqueue'));

        $inputdiv = html_writer::tag('div', $input.$inputdescription, array('id' => 'ac-div', 'class' => 'felement ftext yui3-skin-sam', 'name' => $acdivid));

        $maindiv = html_writer::tag('div', $labeldiv.$inputdiv, array('id' => "fitem_{$acdivid}",  'class' => 'fitem fitem_ftext'));
        $mform->addElement('html', $maindiv);
    }

    /**
     * Get userset associated with solutionid.
     *
     * @param string $solutionid Solution id to locate users set for.
     * @return object User set record object.
     */
    public function get_userset($solutionid) {
        global $DB;
        // Get solution id for user.
        $auth = get_auth_plugin('kronosportal');
        $solutionidfield = $auth->config->solutionid;
        $sql = "SELECT uset.*
                  FROM {local_elisprogram_uset} uset
                  JOIN {local_eliscore_field_clevels} fldctx on fldctx.fieldid = ?
                  JOIN {context} ctx ON ctx.instanceid = uset.id AND ctx.contextlevel = fldctx.contextlevel
                  JOIN {local_eliscore_fld_data_char} fldchar ON fldchar.contextid = ctx.id AND fldchar.fieldid = fldctx.fieldid
                 WHERE uset.depth = 2
                       AND fldchar.data = ? LIMIT 1";
        $userset = $DB->get_record_sql($sql, array($solutionidfield, $solutionid));
        return $userset;
    }

    /**
     * Add select to form.
     */
    private function addselect() {
        global $USER, $DB, $OUTPUT;
        $mform =& $this->_form;
        $auth = get_auth_plugin('kronosportal');
        $solutionid = $auth->get_user_solution_id($USER->id);
        if (empty($solutionid)) {
            print_error('solutionidnotset', 'block_importqueue');
        }
        $userset = $this->get_userset($solutionid);
        // Show roles and solution id userset.
        $sql = "SELECT *
                  FROM {local_elisprogram_uset}
                 WHERE depth in (2, 3)
                   AND (parent = ? OR id = ?)
              ORDER BY depth, name ASC
                 LIMIT 50";
        if (empty($userset->id)) {
            $message = new stdClass();
            $message->solutionid = $solutionid;
            echo $OUTPUT->header();
            echo get_string('usersetnotfound', 'block_importqueue', $message);
            echo $OUTPUT->footer();
            exit;
        }
        $param = array($userset->id, $userset->id);
        $records = $DB->get_records_sql($sql, $param);
        // Don't show dropdown if there is no roles.
        if (count($records) < 2) {
            $mform->addElement('hidden', 'config_userset', $userset->id);
            $mform->setType('config_userset', PARAM_INT);
            return;
        }
        $items = array();

        foreach ($records as $item) {
            if (!empty($item->displayname)) {
                $items[$item->id] = $item->displayname;
            } else {
                $items[$item->id] = $item->name;
            }
        }
        $mform->addElement('select', 'config_userset', get_string('selecteduserset', 'block_importqueue'), $items);
    }

    /**
     * Check if select or auto complete should be used.
     * @return True if drop down or auto complete can be used.
     */
    public function isselect() {
        global $USER;
        // If deleting than then the user set has not reliveance.
        if ($this->mode == 'delete') {
            return false;
        }
        $context = context_system::instance();
        if (is_siteadmin() || has_capability('block/importqueue:sitewide', $context, $USER->id)) {
            if (!empty(get_config('block_importqueue', 'learningpath_autocomplete'))) {
                return true;
            }
        }
        if (!empty(get_config('block_importqueue', 'learningpath_select'))) {
            return true;
        }
        return false;
    }

    /**
     * Method that defines all of the elements of the form.
     */
    public function definition() {
        global $USER, $DB;
        $mform =& $this->_form;
        $context = context_system::instance();
        // If deleting do not show drop down or auto complete.
        if ($this->mode != 'delete' && (is_siteadmin() || has_capability('block/importqueue:sitewide', $context, $USER->id))) {
            if (!empty(get_config('block_importqueue', 'learningpath_autocomplete'))) {
                $this->addautocomplete();
            }
        } else {
            if ($this->mode != 'delete' && (!empty(get_config('block_importqueue', 'learningpath_select')))) {
                $this->addselect();
            }
        }

        // Add mode option.
        $mform->addElement('hidden', 'mode', $this->mode);
        $mform->setType('mode', PARAM_TEXT);

        $mform->addElement('filepicker', 'csvfile', get_string('file'), null,
                array('maxbytes' => 1048576, 'accepted_types' => '*'));
        $mform->addRule('csvfile', get_string('uploadrequired', 'block_importqueue'), 'required', null, 'client');
        $mform->addElement('submit', 'submitbutton', get_string('upload'));
    }

    /**
     * Show form or process upload if submitted.
     *
     * @return int Id of import queue if successful.
     */
    public function process() {
        global $USER, $DB, $SESSION;
        $redoptions = array('style' => 'color: red');
        $auth = get_auth_plugin('kronosportal');
        if (!$auth->is_configuration_valid()) {
            $this->error = html_writer::tag('h3', get_string('configauthkronos', 'block_importqueue'), $redoptions);
            return 0;
        }

        if (!$this->is_submitted() && !$this->is_validated()) {
            $this->error = $this->render();
            return 0;
        } else {
            // Process upload.
            $tempdir = make_temp_directory('/importqueue');
            $tempfile = tempnam($tempdir, 'tmp');
            $this->save_file('csvfile', $tempfile, true);

            $error = $this->validatefile($tempfile);
            @unlink($tempfile);

            // An error has occured.
            if (empty($error)) {
                return 0;
            }

            // If deleting than show confirmation page.
            $formdata = $this->get_data();
            $mode = $formdata->mode;
            if ($mode == 'delete') {
                $this->error = $this->showconfirm();
                $SESSION->block_importqueue_csvfile = $this->csvfile;
                return 0;
            }

            $importqueue = new importqueue();
            $queueid = $importqueue->addtoqueue($this->csvfile, null, null);
            @unlink($this->csvfile);
            if ($queueid) {
                $status = new stdClass();
                $status->total = $this->count;
                $this->error = html_writer::tag('p', get_string('csvadded', 'block_importqueue', $status));
                return $queueid;
            }
        }
    }

    /**
     * Return confirmation message to delete users.
     * @return string Show sample of first 10 users which are to be deleted.
     */
    public function showconfirm() {
        $html = html_writer::tag('h3', get_string('confirmdelete', 'block_importqueue'));
        $html .= html_writer::start_tag('ul');
        $total = count($this->users);
        for ($i = 0; $i < $total && $i < 10; $i++) {
            $html .= html_writer::tag('li', $this->users[$i]);
        }
        if ($total >= 10) {
            $html .= html_writer::tag('li', get_string('more', 'block_importqueue', $total - 10));
        }
        $html .= html_writer::end_tag('ul');
        $link = new moodle_url('/blocks/importqueue/deleteusers.php', array('confirm' => 1));
        $options = array('onclick' => "window.location='".$link->out()."'", 'class' => 'uploadstatusbutton');
        $html .= html_writer::tag('button', get_string('confirmdeletebutton', 'block_importqueue'), $options);
        return $html;
    }

    /**
     * Validate csv file uploaded.
     * @param string $csvfile Csv file uploaded.
     * @return string Empty if file is valid, if it is not an error message is returned.
     */
    public function validatefile($csvfile) {
        global $USER, $DB, $SESSION;
        $context = context_system::instance();
        // Retrieve the mode of the upload.
        $formdata = $this->get_data();
        $mode = $formdata->mode;
        $auth = get_auth_plugin('kronosportal');
        $redoptions = array('style' => 'color: red');

        // Default columns that are required by datahub.
        $columns = array('email', 'password', 'firstname', 'lastname', 'city', 'country');

        // For updating, the Id number is used as the unique identifier.
        if ($mode == 'update') {
            array_unshift($columns, 'idnumber');
        }

        // Only add learning path to columns if auto complete or drop down is not configured.
        if (is_siteadmin() || has_capability('block/importqueue:sitewide', $context, $USER->id)) {
            if (empty(get_config('block_importqueue', 'learningpath_autocomplete'))) {
                $columns[] = 'learningpath';
            }
        } else if (empty(get_config('block_importqueue', 'learningpath_select'))) {
            // Must be training manager with learning path drop down enabled.
            $columns[] = 'learningpath';
        }
        // Add columns set in configuration.
        if ($mode == 'update') {
            $customcolumns = preg_split('/,/', get_config('block_importqueue', 'updatecolumns'));
        } else {
            $customcolumns = preg_split('/,/', get_config('block_importqueue', 'importcolumns'));
        }
        foreach ($customcolumns as $column) {
            if (!empty($column)) {
                $columns[] = $column;
            }
        }

        $allowedempty = array('learningpath');
        // Add allowed empty columns set in configuration.
        if ($mode == 'update') {
            $customcolumns = preg_split('/,/', get_config('block_importqueue', 'updateallowedempty'));
        } else {
            $customcolumns = preg_split('/,/', get_config('block_importqueue', 'allowedempty'));
        }
        foreach ($customcolumns as $column) {
            if (!empty($column)) {
                $allowedempty[] = $column;
            }
        }

        // Deletion only requires the idnumber.
        if ($mode == 'delete') {
            $columns = array('idnumber');
        }

        $fp = fopen($csvfile, 'r');
        $firstrow = fgetcsv($fp);
        $total = count($columns);
        $totalfirstrow = count($firstrow);
        if ($total != $totalfirstrow) {
            $this->error = html_writer::tag('h3', get_string('csvinvalidcolumnformatheader', 'block_importqueue', join(',', $columns)), $redoptions);
            return false;
        }
        $tempdir = make_temp_directory('/importqueue');
        $tempdestfile = tempnam($tempdir, 'dest');
        if (!$fpdest = fopen($tempdestfile, 'w+b')) {
            @unlink($tempfile);
            $this->error = get_string('csvcannotsavedata', 'error');
            return false;
        }
        // Save column headers with fields for datahub.
        $usersolutionid = $auth->get_user_solution_id($USER->id);
        $solutionfield = kronosportal_get_solutionfield();

        $learningpath = '';

        // Load solution id and learning path from user set if user set is selected in form.
        if ($this->isselect() && !empty($formdata->config_userset)) {
            $userset = $DB->get_record('local_elisprogram_uset', array('id' => $formdata->config_userset));
            $result = $DB->get_record('local_eliscore_field', array('id' => $auth->config->solutionid));
            $usersetsolutionidfield = 'field_'.$result->shortname;
            if (!empty($userset) && $userset->depth == 2) {
                $uset = new Userset($userset->id);
                $uset->load();
                // Solution id user set.
                if (!empty($uset->$usersetsolutionidfield)) {
                    // Only allow to over ride solution user set if admin or have site wide capability.
                    if (is_siteadmin() || has_capability('block/importqueue:sitewide', $context, $USER->id)) {
                        $usersolutionid = $uset->$usersetsolutionidfield;
                    }
                }
            } else if ($userset->depth == 3 && !empty($userset->parent)) {
                // Learning path user set.
                $parentuserset = new Userset($userset->parent);
                $parentuserset->load();
                $learningpath = empty($userset->displayname) ? $userset->name : $userset->displayname;
                if (!empty($parentuserset->$usersetsolutionidfield)) {
                    // Only allow to over ride solution user set if admin or have site wide capability.
                    if (is_siteadmin() || has_capability('block/importqueue:sitewide', $context, $USER->id)) {
                        $usersolutionid = $parentuserset->$usersetsolutionidfield;
                    }
                }
            }
        }

        // Validate the solution id is valid.
        if (!kronosportal_is_user_userset_valid($auth, $usersolutionid)) {
            $this->error = html_writer::tag('h3', get_string('invalidsolutionid', 'block_importqueue', $usersolutionid), $redoptions);
            return false;
        }

        if (kronosportal_is_user_userset_expired($auth, $usersolutionid)) {
            $this->error = html_writer::tag('h3', get_string('expiredsolutionid', 'block_importqueue'), $redoptions);
            return false;
        }

        for ($i = 0; $i < $total; $i++) {
            if (empty($firstrow[$i]) || $firstrow[$i] != $columns[$i]) {
                $a = new stdClass();
                $a->column = $columns[$i];
                $a->index = $i;
                $this->error = html_writer::tag('h3', get_string('csvinvalidcolumnformat', 'block_importqueue', $a), $redoptions);
                return false;
            }
        }

        $excludecolumns = array('action', 'auth', 'username', 'context');
        // Allow idnumber to be used during update.
        if ($mode == 'create') {
            $excludecolumns[] = 'idnumber';
        }
        $excludetotal = count($excludecolumns);
        for ($i = 0; $i < $excludetotal; $i++) {
            if (in_array($excludecolumns[$i], $firstrow)) {
                $this->error = html_writer::tag('h3', get_string('csvinvalidcolumnformatexclude', 'block_importqueue', $excludecolumns[$i]), $redoptions);
                return false;
            }
        }

        // Remove idnumber column.
        if ($mode == 'update') {
            array_unshift($firstrow, 'username', 'action', 'auth', 'profile_field_'.$solutionfield);
        } else if ($mode == 'create') {
            array_unshift($firstrow, 'username', 'action', 'auth', 'idnumber', 'profile_field_'.$solutionfield);
        } else if ($mode == 'delete') {
            // Delete only updates solution id field.
            array_push($firstrow, 'action', 'profile_field_'.$solutionfield);
            // Solution id to move deleted users to.
            $deletesolutionid = get_config('block_importqueue', 'deletesolutionid');
        }

        // If we have a learning path add the learning path column if needed.
        if (!in_array('learningpath', $columns) && $learningpath) {
            $columns[] = 'learningpath';
        }
        if (!in_array('learningpath', $firstrow) && $learningpath) {
            $firstrow[] = 'learningpath';
        }
        fputcsv($fpdest, $firstrow);

        // Add password and learningpath to csv file.
        $this->users = array();
        $this->count = 1;
        $total = count($columns);
        $tmperror = '';
        while ($row = fgetcsv($fp)) {
            $this->count++;
            for ($i = 0; $i < $total; $i++) {
                if ($columns[$i] == 'password') {
                    // If password is empty generate one.
                    if (empty($row[$i])) {
                        $row[$i] = generate_password();
                    }
                } else if ($columns[$i] == 'learningpath' && !empty($learningpath)) {
                    $row[$i] = $learningpath;
                } else if ($columns[$i] == 'idnumber') {
                    $idnumber = $row[$i];
                }
                // If empty and not allowed to be empty report an error.
                if (!in_array($columns[$i], $allowedempty) && empty($row[$i])) {
                    $columnname = $columns[$i];
                    $error = new stdClass();
                    $error->linenumber = $this->count;
                    $error->columnname = $columnname;
                    $error->email = $row[0];
                    $tmperror .= html_writer::tag('h3', get_string('csvinvalidrow', 'block_importqueue', $error), $redoptions);
                }
            }
            // Build list of users in cvs file.
            $this->users[] = $row[0];
            // Add datahub fields.
            if ($mode == 'create') {
                array_unshift($row, $row[0], 'create', 'kronosportal', $row[0], $usersolutionid);
            } else if ($mode == 'update') {
                // Id number is first column and second column is the email address which is used as username.
                array_unshift($row, $row[1], 'update', 'kronosportal', $usersolutionid);
            } else if ($mode == 'delete') {
                // Deletion is done by moving user to another solution id user set.
                // Add action and solution id column.
                array_push($row, 'update', $deletesolutionid);
            }
            fputcsv($fpdest, $row);
        }
        fclose($fpdest);
        if (!empty($tmperror)) {
            @unlink($tempdestfile);
            $this->csvfile = '';
            $this->error = $tmperror;
            return false;
        } else {
            // User file successfully generated.
            $this->csvfile = $tempdestfile;
        }
        return true;
    }

    /**
     * Return error message generated.
     *
     * @return string Error message.
     */
    public function geterror() {
        return $this->error;
    }
}
