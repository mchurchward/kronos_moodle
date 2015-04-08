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
     * Constructor, set context id and userset for autocomplete.
     *
     * @param int $context id.
     */
    public function __construct($context, $userset = 0) {
        $this->context = $context;
        parent::__construct();
    }

    /**
     * Validation of userset.
     */
    public function validation($data, $files) {
        $errors = array();
        if (empty($data['config_userset'])) {
            $errors['config_userset'] = 'error';
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
            $items[$item->id] = $item->name;
        }
        $mform->addElement('select', 'config_userset', get_string('selecteduserset', 'block_importqueue'), $items);
    }

    /**
     * Method that defines all of the elements of the form.
     */
    public function definition() {
        global $USER, $DB;
        $mform =& $this->_form;

        $context = context_system::instance();
        if (is_siteadmin() || has_capability('block/importqueue:sitewide', $context, $USER->id)) {
            $this->addautocomplete();
        } else {
            $this->addselect();
        }

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
        global $USER, $DB;
        $redoptions = array('style' => 'color: red');
        $auth = get_auth_plugin('kronosportal');
        if (!$auth->is_configuration_valid()) {
            $this->error = html_writer::tag('h3', get_string('configauthkronos', 'block_importqueue'), $redoptions);
            return 0;
        }
        $formdata = $this->get_data();
        if (empty($formdata)) {
            $this->error = $this->render();
            return 0;
        } else {
            // Process upload.
            $content = $this->get_file_content('csvfile');
            $tempdir = make_temp_directory('/importqueue');
            $tempfile = tempnam($tempdir, 'tmp');
            if (!$fp = fopen($tempfile, 'w+b')) {
                $this->_error = get_string('csvcannotsavedata', 'error');
                @unlink($tempfile);
                return 0;
            }
            fwrite($fp, $content);
            fseek($fp, 0);

            $columns = array('email', 'password', 'firstname', 'lastname', 'city', 'country');
            $firstrow = fgetcsv($fp);
            $total = count($columns);
            $tempdestfile = tempnam($tempdir, 'dest');
            if (!$fpdest = fopen($tempdestfile, 'w+b')) {
                $this->_error = get_string('csvcannotsavedata', 'error');
                @unlink($tempfile);
                return 0;
            }
            // Save column headers with fields for datahub.
            $usersolutionid = $auth->get_user_solution_id($USER->id);
            $solutionfield = kronosportal_get_solutionfield();

            for ($i = 0; $i < $total; $i++) {
                if ($firstrow[$i] != $columns[$i]) {
                    $this->error = html_writer::tag('h3', get_string('csvinvalidcolumnformat', 'block_importqueue', $columns[$i]), $redoptions);
                    return 0;
                }
            }
            array_unshift($firstrow, 'username', 'action', 'auth', 'idnumber', $solutionfield);
            fputcsv($fpdest, $firstrow);

            // Save users to create enrol file if role is selected.
            $users = array();
            $count = 0;
            while ($row = fgetcsv($fp)) {
                for ($i = 0; $i < $total; $i++) {
                    if ($columns[$i] == 'password') {
                        // If password is empty generate one.
                        if (empty($row[$i])) {
                            $row[$i] = generate_password();
                        }
                    }
                    if (empty($row[$i])) {
                        $columnname = $columns[$i];
                        $error = new stdClass();
                        $error->linenumber = $count;
                        $error->columname = $columnname;
                        $this->error = html_writer::tag('h3', get_string('csvinvalidrow', 'block_importqueue', $error), $redoptions);
                        return 0;
                    }
                }
                $count++;
                // Add datahub fields.
                $users[] = $row[0];
                array_unshift($row, $row[0], 'create', 'kronosportal', $row[0], $usersolutionid);
                fputcsv($fpdest, $row);
            }
            fclose($fpdest);
            $importqueue = new importqueue();
            $userset = $DB->get_record('local_elisprogram_uset', array('id' => $formdata->config_userset));
            // If assigning to a role, than create enrol file.
            if ($userset->depth != 2) {
                $enrolfile = $importqueue->enrol_users_userset($userset->name, $users);
            } else {
                $enrolfile = null;
            }
            $queueid = $importqueue->addtoqueue($tempdestfile, null, $enrolfile);
            if ($queueid) {
                $status = new stdClass();
                $status->total = $count;
                $this->error = html_writer::tag('p', get_string('csvadded', 'block_importqueue', $status));
                return $queueid;
            }
        }
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
