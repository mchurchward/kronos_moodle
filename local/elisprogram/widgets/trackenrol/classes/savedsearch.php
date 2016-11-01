<?php
/**
 * ELIS(TM): Enterprise Learning Intelligence Suite
 * Copyright (C) 2008-2016 Remote-Learner.net Inc (http://www.remote-learner.net)
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
 * @package    eliswidget_enrolment
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2016 Onwards Remote-Learner.net Inc (http://www.remote-learner.net)
 * @author     Remote-Learner.net Inc
 *
 */

namespace eliswidget_trackenrol;

require_once($CFG->dirroot.'/local/elisprogram/lib/deepsight/lib/lib.php');

/**
 * Saved search AJAX response class.
 *
 * This receives AJAX requests to save, update and delete saved searches for the Track enrol widget.
 */
class savedsearch extends \deepsight_savedsearch {

    /**
     * Constructor.
     */
    public function __construct($context, $pagename) {
        parent::__construct($context, $pagename);
        // Override permissions TBD.
        $this->cansavesearches = true;
        $this->canloadsearches = true;
        $this->set_context($context);
    }

    /**
     * Set context and update permissions.
     *
     * @param object $context Context object.
     */
    public function set_context($context) {
        global $USER;
        // Allows for passing of phpunit test deepsight_datatable_testcase.
        if (!empty($context)) {
            // Load user saved searches if they have permission at solution userset id context level or system level.
            $this->canloadsearches = false;
            if (has_capability('eliswidget/trackenrol:studenttrackwidgetsearchload', $this->context, $USER->id) ||
                    has_capability('eliswidget/trackenrol:studenttrackwidgetsearchload', $syscontext, $USER->id) ||
                    has_capability('eliswidget/trackenrol:trackwidgetsearchload', $syscontext, $USER->id)) {
                $this->canloadsearches = true;
            }
            // Allow saved user saved searches if they have permission at solution userset id context level or system level.
            $this->cansavesearches = false;
            if ((has_capability('eliswidget/trackenrol:studenttrackwidgetsearchsave', $this->context, $USER->id) ||
                    has_capability('eliswidget/trackenrol:studenttrackwidgetsearchsave', $syscontext, $USER->id) ||
                    has_capability('eliswidget/trackenrol:trackwidgetsearchsave', $syscontext, $USER->id))) {
                $this->cansavesearches = true;
            }
        }
    }

    /**
     * Save or update search.
     *
     * @param object $search Search object containing name and filters.
     * @return int Id of saved search or false on failure.
     */
    public function save($search) {
        global $DB, $USER;
        if (!$this->cansavesearches()) {
            return false;
        }
        // Validate required data for search.
        // Do not check for feild sort as track enrol widget does not use it.
        if (empty($search) || empty($search->name) || empty($search->data)) {
            return false;
        }
        if (!empty($search->contextid) && $search->contextid != $this->context->id) {
            // Set context to context we want to save to.
            $this->set_context(context::instance_by_id($search->contextid));
            if (!$this->cansavesearches()) {
                return false;
            }
        }
        $search->contextid = $this->context->id;
        $search->pagename = $this->pagename;
        $search->data = json_encode($search->data);
        if (!empty($search->fieldsort)) {
            $search->fieldsort = json_encode($search->fieldsort);
        }
        $systemcontext = \context_system::instance();
        // Allow more than one admin to overwrite each others saved searches.
        if ($search->contextid != $systemcontext->id && $search->userid != $USER->id) {
            unset($search->id);
        }
        $search->userid = $USER->id;
        if (empty($search->id)) {
            // Adding.
            $id = $DB->insert_record('local_elisprogram_deepsight', $search);
        } else {
            // Saving.
            $record = $DB->get_record('local_elisprogram_deepsight', array('id' => $search->id, 'contextid' => $this->context->id));
            if (!empty($record)) {
                $DB->update_record('local_elisprogram_deepsight', $search);
                $id = $search->id;
            } else {
                unset($search->id);
                $search->contextid = $this->context->id;
                $id = $DB->insert_record('local_elisprogram_deepsight', $search);
            }
        }
        if (!empty($id) && !empty($search->isdefault)) {
            if ($search->contextid != $systemcontext->id) {
                // For user and training manager ensure only update their defaults.
                $sql = 'UPDATE {local_elisprogram_deepsight} SET isdefault = 0 WHERE id != ? AND contextid = ? AND pagename = ? AND userid = ?';
                $DB->execute($sql, array($id, $this->context->id, $this->pagename, $USER->id));
            } else {
                // For system level context allow admins to override each others defaults.
                $sql = 'UPDATE {local_elisprogram_deepsight} SET isdefault = 0 WHERE id != ? AND contextid = ? AND pagename = ?';
                $DB->execute($sql, array($id, $this->context->id, $this->pagename));
            }
        }
        return $id;
    }

    /**
     * Sort comparsion function based on object name.
     *
     * @param array $a First object.
     * @param array $b Second object.
     * @return int 0 for equal, -1 for $a < $b and 1 for $a > $b.
     */
    public function cmp($a, $b) {
        if ($a['name'] == $b['name']) {
            return 0;
        }
        return $a['name'] < $b['name'] ? -1 : 1;
    }

    /**
     * Get list of saved searches. With the first search being the default search.
     *
     * @return array Array contianing most recently saved starting searches.
     */
    public function starting_searches() {
        global $DB;
        if (!$this->canloadsearches()) {
            return array();
        }
        return $this->search(null);
    }

    /**
     * Search for saved search.
     *
     * @param string $query Search query string.
     * @return array Array contianing searches.
     */
    public function search($query) {
        global $DB, $USER;
        if (!$this->canloadsearches()) {
            return array();
        }
        // Get parent userset searches.
        $globalsearches = $this->get_global_searches($query);
        // Training manager saved default take prority over global.
        $results = $this->get_trainingmanager_searches($query);
        $results = self::merge_results($results, $globalsearches);
        $usersearches = $this->get_user_searches($query);
        $results = self::merge_results($usersearches, $results);
        usort($results, array($this, 'cmp'));
        $results = self::merge_results($results, []);
        return $results;
    }

    /**
     * Get training manager saved searches if solution id is set.
     *
     * @param string $query Search query string. If null do not preform sub string search.
     * @return array Array containing searches.
     */
    public function get_trainingmanager_searches($query) {
        global $DB, $USER;
        // Get searches for Solution Id userset.
        $trainingmanager = $this->get_solution_usersets_training_manager($USER->id);
        if (empty($trainingmanager)) {
            return [];
        }
        $tmids = [];
        foreach ($trainingmanager as $manager) {
            $tmids[] = $manager->userid;
        }
        list ($tmsql, $tmparams) = $DB->get_in_or_equal($tmids);
        $usersetrole = get_config('block_kronostmrequest', 'usersetrole');
        $like = $DB->sql_like('name', '?', false);
        $sql = 'SELECT * FROM {local_elisprogram_deepsight} WHERE contextid = ? AND pagename = ? AND '.$like.' AND userid '.$tmsql.'  LIMIT 10';
        $params = array_merge(array($this->context->id, $this->pagename, "%$query%"), $tmparams);
        $records = $DB->get_records_sql($sql, $params);
        $results = array();
        if (empty($records)) {
            return [];
        }
        foreach ($records as $key => $value) {
            $item = $this->process_search($value);
            // Non training managers cannot update tm saved searches.
            if ($item['userid'] != $USER->id) {
                $item['cansave'] = false;
                $item['type'] = 'tm';
                $results[] = $item;
            }
        }
        return $results;
    }

    /**
     * Merge list of saved searches putting default search result first in the list.
     *
     * @param array $firstresults First results to merge.
     * @param array $secondresults Second results to merge.
     * @return array Array containing searches.
     */
    public static function merge_results($firstresults, $secondresults) {
        $results = [];
        $founddefault = false;
        foreach ($firstresults as $item) {
            // First default found is the only one is set as default.
            if (!empty($founddefault)) {
                $item['isdefault'] = false;
            } else if ($item['isdefault']) {
                $founddefault = $item;
                continue;
            }
            $results[] = $item;
        }

        foreach ($secondresults as $item) {
            if (!empty($founddefault)) {
                $item['isdefault'] = false;
            } else if ($item['isdefault']) {
                $founddefault = $item;
                continue;
            }
            $results[] = $item;
        }
        if (!empty($founddefault)) {
            array_unshift($results, $founddefault);
        }
        return $results;
    }

    /**
     * Search for global saved searches saved at system context level.
     *
     * @param string $query Search query string. If null do not preform sub string search.
     * @return array Array containing searches.
     */
    public function get_global_searches($query = null) {
        global $DB, $USER;
        $systemcontext = \context_system::instance();
        $results = array();
        // Only show global searches if user has load permissions at system context.
        if (!(has_capability('eliswidget/trackenrol:trackwidgetsearchload', $systemcontext, $USER->id) ||
                has_capability('eliswidget/trackenrol:studenttrackwidgetsearchload', $systemcontext, $USER->id))) {
            return array();
        }
        $founddefault = false;
        if (!empty($orderby)) {
            $orderby = "ORDER BY $orderby";
        }
        // Only allow saving global searches if they have capability at system level.
        $cansavesearches = has_capability('eliswidget/trackenrol:trackwidgetsearchsave', $systemcontext, $USER->id);
        // Search for parent saved parent userset searches;
        if (!empty($query)) {
            $like = $DB->sql_like('name', '?', false);
            $sql = 'SELECT * FROM {local_elisprogram_deepsight} WHERE contextid = ? AND pagename = ? AND '.$like.' LIMIT 10';
            $records = $DB->get_records_sql($sql, array($systemcontext->id, $this->pagename, "%$query%"));
        } else {
            $sql = 'SELECT * FROM {local_elisprogram_deepsight} WHERE contextid = ? AND pagename = ? LIMIT 10';
            $records = $DB->get_records_sql($sql, array($systemcontext->id, $this->pagename));
        }
        foreach ($records as $item) {
            $newitem = $this->process_search($item);
            $newitem['cansave'] = $cansavesearches;
            $newitem['type'] = 'global';
            // First default found is the only one is set as default.
            if (!empty($founddefault)) {
                $newitem['isdefault'] = false;
            } else if ($newitem['isdefault']) {
                $founddefault = $newitem;
            }
            $results[] = $newitem;
        }
        if (!empty($founddefault)) {
            array_unshift($results, $founddefault);
        }
        return $results;
    }

    /**
     * Search for user saved searches.
     *
     * @param string $query Search query string. If null do not preform sub string search.
     * @return array Array containing searches.
     */
    public function get_user_searches($query = null) {
        global $DB, $USER;
        $parentcontext = $this->context;
        $results = array();
        $syscontext = \context_system::instance();

        if (!$this->canloadsearches()) {
            return [];
        }

        $founddefault = false;
        if (!empty($orderby)) {
            $orderby = "ORDER BY $orderby";
        }

        // Search for parent saved parent userset searches;
        if (!empty($query)) {
            $like = $DB->sql_like('name', '?', false);
            $sql = 'SELECT * FROM {local_elisprogram_deepsight} WHERE contextid = ? AND pagename = ? AND userid = ? AND '.$like.' LIMIT 10';
            $records = $DB->get_records_sql($sql, array($parentcontext->id, $this->pagename, $USER->id, "%$query%"));
        } else {
            $sql = 'SELECT * FROM {local_elisprogram_deepsight} WHERE contextid = ? AND pagename = ? AND userid = ? LIMIT 10';
            $records = $DB->get_records_sql($sql, array($parentcontext->id, $this->pagename, $USER->id));
        }
        foreach ($records as $item) {
            $newitem = $this->process_search($item);
            $newitem['type'] = 'user';
            $newitem['cansave'] = $this->cansavesearches();
            // First default found is the only one is set as default.
            if (!empty($founddefault)) {
                $newitem['isdefault'] = false;
            } else if ($newitem['isdefault']) {
                $founddefault = $newitem;
            }
            $results[] = $newitem;
        }
        if (!empty($founddefault)) {
            array_unshift($results, $founddefault);
        }
        return $results;
    }

    /**
     * This function retrieves the user's Solution ID from a custom profile field.
     * @todo write phpunit tests at some point in the future.
     * @param int $userid A user id.
     * @return int The user's solution id.  Zero is returned if the Solution id is empty.
     */
    public static function get_user_solution_id($userid) {
        global $DB;
        $solutionfieldid = get_config('auth_kronosportal', 'user_field_solutionid');
        $usersolutionid = $DB->get_field('user_info_data', 'data', array('userid' => $userid, 'fieldid' => $solutionfieldid));

        if (empty($usersolutionid)) {
            return 0;
        }

        return clean_param(trim($usersolutionid), PARAM_ALPHANUMEXT);
    }

    /**
     * This function searches for training manager roles assigned a User Sets Solution.
     * @param int $userid The user ID.
     * @return array Array of objects ('id' -> Context id, 'name' -> User Set name, 'usersetid' -> User Set id).  Otherwise false.
     */
    public function get_solution_usersets_training_manager($userid) {
        global $DB;
        // Get training managers for Solution Id userset.
        $solutionid = self::get_user_solution_id($userid);
        $auth = get_auth_plugin('kronosportal');
        if (empty($solutionid)) {
            return false;
        }
        $usersetcontext = $auth->userset_solutionid_exists($solutionid);
        if (empty($usersetcontext)) {
            return false;
        }
        $sql = "SELECT a.id, c.id contextid, c.instanceid usersetid, a.roleid, a.userid
                  FROM {context} c,
                       {role_assignments} a
                 WHERE a.roleid = ?
                       AND a.contextid = c.id
                       AND c.contextlevel = ?
                       AND c.id = ?";
        $usersetroleid = get_config('block_kronostmrequest', 'usersetrole');
        return $DB->get_records_sql($sql, array($usersetroleid, CONTEXT_ELIS_USERSET, $usersetcontext->id));
    }

    /**
     * Return if user can load seraches for current context.
     *
     * @return boolean True if can load searches.
     */
    public function canloadsearches() {
        return $this->canloadsearches;
    }

    /**
     * Return if user can save seraches for current context.
     *
     * @return boolean True if can save searches.
     */
    public function cansavesearches() {
        return $this->cansavesearches;
    }
}
