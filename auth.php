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
 * Main auth plug-in class
 *
 * @package    auth_rladmin
 * @copyright  2012 Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->libdir.'/authlib.php');


/**
 * RLAdmin authentication plugin.
 */
class auth_plugin_rladmin extends auth_plugin_base {
    const MAX_TOKEN_ATTEMPTS = 10;
    const EXPIRY             = 600;  // Tokens expire in 10 minutes
    const TOKEN_LENGTH       = 64;

    /**
     * Constructor.
     */
    function auth_plugin_rladmin() {
        global $CFG;
        $this->authtype = 'rladmin';
        $config = get_config('auth/rladmin');
        require($CFG->dirroot.'/auth/rladmin/config.php');
        // what fields can we modify?
        $userinfo = array('firstname', 'lastname', 'email', 'skype', 'phone1', 'city', 'country');
        foreach ($userinfo as $key) {
            $config->{'field_updatelocal_' . $key} = 'onlogin';
            $config->{'field_lock_' . $key} = true;
        }
        $this->config = $config;
    }

    /**
     * Handles a direct login attempt but only for the configured "shared" admin user account.
     *
     * Prevents any kind of normal username / password login for accounts using this method.
     *
     * @param string $username The username (with system magic quotes)
     * @param string $password The password (with system magic quotes)
     *
     * @return bool Authentication success or failure.
     */
    function user_login ($username, $password) {
        global $CFG;
        if ($username == $this->config->username) {
            $token = $this->_create_token();
            $params = array(
                'action' => 'authenticate',
                'token'  => $token,
                'return' => $CFG->wwwroot.'/auth/rladmin/login.php'
            );
            redirect(new moodle_url($this->config->idp, $params), '', 0);
        }
        return false;
    }

    /**
     * Generate an authentication token
     *
     * @uses print_error
     * @param none
     * @return string An authentication token used with the IDP server for auth validation.
     */
    function _create_token() {
        global $DB;

        $pool = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $poollen = strlen($pool);
        mt_srand ((double) microtime() * 1000000);

        $rec = new stdClass;
        $rec->ip = getremoteaddr();
        $rec->expiry = time() + self::EXPIRY;
        $count = 0;
        $saved = false;
        do {
            $token = '';
            for ($i = 0; $i < self::TOKEN_LENGTH; $i++) {
                $token .= substr($pool, mt_rand(0,$poollen), 1);
            }
            $count++;
            try {
                // try saving the record
                $rec->token = $token;
                $DB->insert_record('rladmin', $rec);
                $saved = true;
            }
            catch (dml_write_exception $e) {
                // failed to insert, probably due token already used -- just try again
            }
        } while ($saved == false && $count < self::MAX_TOKEN_ATTEMPTS);

        if (!$saved && $count >= self::MAX_TOKEN_ATTEMPTS) {
            // exceeded the maximum number of attempts -- something bad must
            // have happened
            print_error('cannotgeneratetoken', 'auth_rladmin');
        }
        return $token;
    }

    /**
     * Confirm that the token is valid for the user
     *
     * @param string $token An authentication token used with the IDP server for auth validation.
     * @return bool True if the token is valid for the user, False otherwise.
     */
    function _confirm_token($token) {
        global $DB;

        $DB->delete_records_select('rladmin', 'expiry < ?', array(time())); // remove expired tokens
        $rec = $DB->get_record('rladmin', array('token' => $token));

        if (!$rec || $rec->ip !== getremoteaddr()) {
            print_error('invalidtoken', 'auth_rladmin');
        }

        $DB->delete_records('rladmin', array('id' => $rec->id));

        return true;
    }

    function user_exists($username) {
        if ($username == $this->config->username) {
            return true;
        }
        if ($this->config->separateusers) {
            $len = strlen($this->config->usernameprefix);
            return (strncmp($username, $this->config->usernameprefix, $len) == 0);
        }
        return false;
    }

    function prevent_local_passwords() {
        return true;
    }

    function is_internal() {
        return false;
    }

    /**
     * Make the specified user a system-level admin
     *
     * @uses print_error
     * @param object $user A fully set up user object from the login process
     */
    function _make_admin($user) {
        global $CFG;

        if (!in_array($user->id, explode(',',$CFG->siteadmins))) {
            if (array_key_exists('siteadmin', $CFG->config_php_settings)) {
                // the setting is set in config.php, so cannot set
                print_error('siteadminconfigoverride', 'auth_rladmin', '', $user->id);
            }
            if (empty($CFG->siteadmins)) {
                set_config('siteadmins', $user->id);
            } else {
                set_config('siteadmins', "{$user->id},{$CFG->siteadmins}");
            }
        }
    }

    /**
     * Synchronises role information from the IDP into system-level role assignments on this Moodle instance.
     *
     * Only gets called (indirectly) through /auth/rladmin/login.php, which sets $remoteuser
     *
     * @param object $user A fully set up user object from the login process
     * @return none
     */
    function sync_roles($user) {
        global $CFG, $DB, $remoteuser;

        if ($this->config->separateusers) {
            if (isset($remoteuser->roles)) {
                foreach($remoteuser->roles as $role) {
                    if ($role === '_SITEADMIN_') {
                        $this->_make_admin($user);
                    } else {
                        $roleid = $DB->get_field('role', 'id', array('shortname' => $role));
                        $roleid && role_assign($roleid, $user->id, SYSCONTEXTID);
                    }
                }
                if (!in_array('_SITEADMIN_', $remoteuser->roles)) {
                    // remove the user as an admin
                    $admins = explode(',',$CFG->siteadmins);
                    if (in_array($user->id, $admins)) {
                        set_config('siteadmins', implode(',', array_diff($admins, array($user->id))));
                    }
                }
            }
        } else {
            // Make the rladmin user a site admin
            $this->_make_admin($user);
        }
    }

    /**
     * Sets up an object containing user record values from the remote IDP server (or, by default) returns the information
     * required to create the 'rladmin' user.
     *
     * Only gets called (indirectly) through /auth/rladmin/login.php, which sets $remoteuser
     *
     * @param str $username Not used
     * @return array An array containing user profile information from the remote IDP.
     */
    function get_userinfo($username) {
        // only gets called (indirectly) through /auth/rladmin/login.php, which
        // sets $remoteuser
        global $remoteuser;

        if ($this->config->separateusers && isset($remoteuser)) {
            $user     = array();
            $all_keys = array_keys(get_object_vars($this->config));

            foreach ($all_keys as $key) {
                if (preg_match('/^field_updatelocal_(.+)$/', $key, $match)) {
                    $field        = $match[1];
                    $user[$field] = $remoteuser->$field;
                }
            }

            return $user;
        } else {
            return array(
                'firstname' => 'Remote-Learner',
                'lastname'  => 'Admin',
                'email'     => 'support@remote-learner.net',
                'city'      => 'Fishersville',
                'country'   => 'US',
            );
        }
    }
}
