<?php
/**
 * ELIS(TM): Enterprise Learning Intelligence Suite
 * Copyright (C) 2008-2013 Remote-Learner.net Inc (http://www.remote-learner.net)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    local_elisprogram
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2013 Remote Learner.net Inc http://www.remote-learner.net
 * @author     James McQuillan <james.mcquillan@remote-learner.net>
 *
 */

require_once(elis::plugin_file('usetenrol_manual', 'lib.php'));

/**
 * Trait containing shared methods.
 */
trait deepsight_action_usersetuser {
    /**
     * Determine whether the current user can manage an association.
     *
     * @param int $usersetid The ID of the main element. The is the ID of the 'one', in a 'many-to-one' association.
     * @param int $userid The ID of the incoming element. The is the ID of the 'many', in a 'many-to-one' association.
     * @return bool Whether the current can manage (true) or not (false)
     */
    protected function can_manage_assoc($usersetid, $userid) {
        return clusteruserpage::can_manage_assoc($userid, $usersetid);
    }
}

/**
 * An action to assign users to a userset.
 */
class deepsight_action_usersetuser_assign extends deepsight_action_confirm {
    use deepsight_action_usersetuser;

    public $label = 'Assign';
    public $icon = 'elisicon-assoc';

    /**
     * Constructor.
     * @param moodle_database $DB The active database connection.
     * @param string $name The unique name of the action to use.
     * @param string $descsingle The description when the confirmation is for a single element.
     * @param string $descmultiple The description when the confirmation is for the bulk list.
     */
    public function __construct(moodle_database &$DB, $name, $descsingle='', $descmultiple='') {
        parent::__construct($DB, $name);

        $langelements = new stdClass;
        $langelements->baseelement = strtolower(get_string('cluster', 'local_elisprogram'));
        $langelements->actionelement = strtolower(get_string('user', 'local_elisprogram'));
        $this->descsingle = (!empty($descsingle))
                ? $descsingle : get_string('ds_action_assign_confirm', 'local_elisprogram', $langelements);

        $langelements = new stdClass;
        $langelements->baseelement = strtolower(get_string('cluster', 'local_elisprogram'));
        $langelements->actionelement = strtolower(get_string('users', 'local_elisprogram'));
        $this->descmultiple = (!empty($descmultiple))
                ? $descmultiple : get_string('ds_action_assign_confirm_multi', 'local_elisprogram', $langelements);
    }

    /**
     * Assign the users to the userset.
     *
     * @param array $elements An array of user information to assign to the userset.
     * @param bool $bulkaction Whether this is a bulk-action or not.
     * @return array An array to format as JSON and return to the Javascript.
     */
    protected function _respond_to_js(array $elements, $bulkaction) {
        global $DB;
        $usersetid = required_param('id', PARAM_INT);
        $userset = new userset($usersetid);

        // Permissions.
        if (usersetpage::can_enrol_into_cluster($userset->id) !== true) {
            return array('result' => 'fail', 'msg' => get_string('not_permitted', 'local_elisprogram'));
        }

        $failedops = [];
        foreach ($elements as $userid => $label) {
            if ($this->can_manage_assoc($userset->id, $userid) === true) {
                try {
                    cluster_manual_assign_user($userset->id, $userid);
                } catch (\Exception $e) {
                    if ($bulkaction === true) {
                        $failedops[] = $userid;
                    } else {
                        throw $e;
                    }
                }
            } else {
                $failedops[] = $userid;
            }
        }

        if ($bulkaction === true && !empty($failedops)) {
             return [
                'result' => 'partialsuccess',
                'msg' => get_string('ds_action_generic_bulkfail', 'local_elisprogram'),
                'failedops' => $failedops,
            ];
        } else {
            return array('result' => 'success', 'msg' => 'Success');
        }
    }
}

/**
 * An action to unassign a user from a userset.
 */
class deepsight_action_usersetuser_unassign extends deepsight_action_confirm {
    use deepsight_action_usersetuser;
    const TYPE = 'usersetuser_unassign';

    public $label = 'Unassign';
    public $icon = 'elisicon-unassoc';
    protected $cascademsg = 0;
    protected $cascademsgtoken = 'element_id';

    /**
     * Constructor.
     * @param moodle_database $DB The active database connection.
     * @param string $name The unique name of the action to use.
     * @param string $descsingle The description when the confirmation is for a single element.
     * @param string $descmultiple The description when the confirmation is for the bulk list.
     */
    public function __construct(moodle_database &$DB, $name, $descsingle='', $descmultiple='') {
        parent::__construct($DB, $name);
        $this->label = ucwords(get_string('unassign', 'local_elisprogram'));

        $langelements = new stdClass;
        $langelements->baseelement = strtolower(get_string('cluster', 'local_elisprogram'));
        $langelements->actionelement = strtolower(get_string('user', 'local_elisprogram'));
        $this->descsingle = (!empty($descsingle))
                ? $descsingle : get_string('ds_action_unassign_confirm', 'local_elisprogram', $langelements);

        if ($this->get_cascade_single_warning_msg($langelements)) {
            $this->descsingle = $this->get_cascade_single_warning_msg($langelements);
            $this->cascademsg = 1;
        }

        $langelements = new stdClass;
        $langelements->baseelement = strtolower(get_string('cluster', 'local_elisprogram'));
        $langelements->actionelement = strtolower(get_string('users', 'local_elisprogram'));
        $this->descmultiple = (!empty($descmultiple))
                ? $descmultiple : get_string('ds_action_unassign_confirm_multi', 'local_elisprogram', $langelements);

        if ($this->get_cascade_bulk_warning_msg($langelements)) {
            $this->descmultiple = $this->get_cascade_bulk_warning_msg($langelements);
            $this->cascademsg = 1;
        }
    }

    /**
     * Unassign the user from the userset.
     *
     * @param array $elements An array of elements to perform the action on.
     * @param bool $bulkaction Whether this is a bulk-action or not.
     * @return array An array to format as JSON and return to the Javascript.
     */
    protected function _respond_to_js(array $elements, $bulkaction) {
        global $DB;
        $usersetid = required_param('id', PARAM_INT);
        $userset = new userset($usersetid);

        // Permissions.
        if (usersetpage::can_enrol_into_cluster($userset->id) !== true) {
            return array('result' => 'fail', 'msg' => get_string('not_permitted', 'local_elisprogram'));
        }

        $failedops = [];
        foreach ($elements as $userid => $label) {
            if ($this->can_manage_assoc($usersetid, $userid)) {
                $assignrec = $DB->get_record(clusterassignment::TABLE, array('userid' => $userid, 'clusterid' => $usersetid));
                if (!empty($assignrec) && $assignrec->plugin === 'manual') {
                    try {
                        $curstu = new clusterassignment($assignrec);
                        $curstu->delete();
                    } catch (\Exception $e) {
                        if ($bulkaction === true) {
                            $failedops[] = $userid;
                        } else {
                            throw $e;
                        }
                    }
                } else {
                    $failedops[] = $userid;
                }
            } else {
                $failedops[] = $userid;
            }
        }

        if ($bulkaction === true && !empty($failedops)) {
             return [
                'result' => 'partialsuccess',
                'msg' => get_string('ds_action_generic_bulkfail', 'local_elisprogram'),
                'failedops' => $failedops,
            ];
        } else {
            return array('result' => 'success', 'msg' => 'Success');
        }
    }

    /**
     * Provide options to the javascript.
     * @return array An array of options.
     */
    public function get_js_opts() {
        global $CFG;
        $opts = parent::get_js_opts();
        $opts['condition'] = $this->condition;
        $opts['opts']['actionurl'] = $this->endpoint;
        $opts['opts']['desc_single'] = $this->descsingle;
        $opts['opts']['desc_multiple'] = $this->descmultiple;
        $opts['opts']['mode'] = 'unassign'; // TBD
        $opts['opts']['lang_bulk_confirm'] = get_string('ds_bulk_confirm', 'local_elisprogram');
        $opts['opts']['lang_working'] = get_string('ds_working', 'local_elisprogram');
        $opts['opts']['langrecursive'] = get_string('usersettrack_recursive_unassign', 'local_elisprogram');
        $opts['opts']['langyes'] = get_string('yes', 'moodle');
        $opts['opts']['langno'] = get_string('no', 'moodle');
        $opts['opts']['cascademsg'] = $this->cascademsg;
        $opts['opts']['cascademsgtoken'] = $this->cascademsgtoken;
        return $opts;
    }

    /**
     * Returns a warning message for single removal. Shown when cascade unenrol is enabled.  Otherwise it returns nothing.
     * @param object An object containing 'baseelement' and 'actionelement' as properties that is used for languagestring
     * substitution.  See language string 'ds_action_unassign_confirm' and 'ds_action_unassign_confirm_cascade'.
     * @return string A warning message.
     */
    protected function get_cascade_single_warning_msg($langelements) {
        if (class_exists('individual_course_progress_report') && !empty(elis::$config->local_elisprogram->remove_trk_cls_pgr_assoc)) {
            $url = new \moodle_url('/local/elisreports/render_report_page.php', array('report' => 'individual_course_progress', 'filterautoc_id' => $this->cascademsgtoken));
            $stub = new \individual_course_progress_report('temp');
            $attr = array('class' => 'deepsight_anchor_on_dark', 'target' => '_blank');
            $langelements->reportlink = \html_writer::link($url, get_string('displayname', $stub->lang_file), $attr);
            return get_string('ds_action_unassign_confirm_cascade', 'local_elisprogram', $langelements);
        }
        return '';
    }

    /**
     * Returns a warning message for bulk removal. Shown when cascade unenrol is enabled.  Otherwise it returns nothing.
     * @param object An object containing 'baseelement' and 'actionelement' as properties that is used for languagestring
     * substitution.  See language string 'ds_action_unassign_confirm' and 'ds_action_unassign_confirm_cascade'.
     * @return string A warning message.
     */
    protected function get_cascade_bulk_warning_msg($langelements) {
        if (class_exists('course_completion_by_cluster_report') && !empty(elis::$config->local_elisprogram->remove_trk_cls_pgr_assoc)) {
            $url = new \moodle_url('/local/elisreports/render_report_page.php', array('report' => 'course_completion_by_cluster'));
            $attr = array('class' => 'deepsight_anchor_on_dark', 'target' => '_blank');
            $langelements->reportlink = \html_writer::link($url, get_string('displayname', 'rlreport_course_completion_by_cluster'), $attr);
            return get_string('ds_action_unassign_confirm_multi_cascade', 'local_elisprogram', $langelements);
        }
        return '';

    }
}