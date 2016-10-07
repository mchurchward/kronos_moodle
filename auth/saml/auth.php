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
 * @package    auth_saml
 * @author     Erlend Strømsvik - Ny Media AS
 * @author     Remote-Learner.net Inc
 * @copyright  2014 onwards Remote-Learner {@link http://www.remote-learner.ca/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * Authentication Plugin: SAML based SSO Authentication
 *
 * Authentication using SAML2 with SimpleSAMLphp.
 *
 * Based on plugins made by Sergio Gómez (moodle_ssp) and Martin Dougiamas (Shibboleth).
 *
 * 2008-10  Created
 * 2009-07  added new configuration options.  Tightened up the session handling
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir.'/authlib.php');
require_once('lib.php');
require_once($CFG->dirroot.'/auth/kronosportal/auth.php');

/**
 * SimpleSAML authentication plugin.
**/
class auth_plugin_saml extends auth_plugin_base {
    /** @var array $samlattributes an array of nested arrays containing saml user attributes */
    public static $samlattributes = array();

    /**
    * Constructor.
    */
    function auth_plugin_saml() {
        $this->authtype = 'saml';
        $this->config = get_config('auth/saml');
    }

    /**
    * Returns true if the username and password work and false if they are
    * wrong or don't exist.
    *
    * @param string $username The username (with system magic quotes)
    * @param string $password The password (with system magic quotes)
    * @return bool Authentication success or failure.
    */
    function user_login($username, $password) {
        global $CFG, $DB, $USER, $SESSION;

        // If saml_login is not set than user is not logged in.
        if (empty($GLOBALS['saml_login'])) {
            return false;
        }

        $kronosportal = get_auth_plugin('kronosportal');
        // Check if the plug-in is configured correctly.
        if (!$kronosportal->is_configuration_valid()) {
            $event = \auth_kronosportal\event\kronosportal_invalid_configuration::create(array());
            $event->trigger();
            return false;
        }

        // if true, user_login was initiated by saml/index.php
        if (!empty($GLOBALS['saml_login'])) {
            unset($GLOBALS['saml_login']);
            return TRUE;
        }

        return FALSE;
    }

    /**
    * Returns the user information for 'external' users. In this case the
    * attributes provided by Identity Provider
    *
    * @return array $result Associative array of user data
    */
    function get_userinfo($username) {
        if ($login_attributes = $GLOBALS['saml_login_attributes']) {
            $attributemap = $this->get_attributes();
            $country_codes = $this->country_codes();
            $attributemap['memberof'] = $this->config->memberattribute;
            $result = array();

            foreach ($attributemap as $key => $value) {
                if (isset($login_attributes[$value]) && $attribute = $login_attributes[$value][0]) {
                    if ($key == 'country') {
                        if (isset($country_codes['bycode'][$attribute])) {
                            $result[$key] = clean_param($attribute, PARAM_TEXT);
                        }
                        else {
                            if (isset($country_codes['bynames'][$attribute])) {
                                $result[$key] = clean_param($country_codes['bynames'][$attribute], PARAM_TEXT);
                            }
                            // else we don't know what this country is so ignore it
                        }
                    }
                    else {
                        $result[$key] = clean_param($attribute, PARAM_TEXT);
                    }
                } else {
                    $result[$key] = clean_param($value, PARAM_TEXT); // allows user to set a hardcode default
                }
            }
            return $result;
        }

        return FALSE;
    }

    /**
    * Returns the list of country codes
    *
    * @return array $names of country codes indexed both ways
    */
    function country_codes() {
        global $CFG, $SESSION;

        $string = array();

        $lang = (isset($SESSION->lang) ? $SESSION->lang : $CFG->lang);
        include($CFG->dirroot.'/lang/'.$lang.'/countries.php');

        $names = array('bynames' => array(), 'bycode' => array());
        foreach ($string as $k => $v) {
            $names['bynames'][$v] = $k;
        }
        $names['bycode'] = $string;

        return $names;
    }

    /*
    * Returns array containg attribute mappings between Moodle and Identity Provider.
    */
    function get_attributes() {
        $configarray = (array) $this->config;

        $fields = array("firstname", "lastname", "email", "phone1", "phone2",
            "department", "address", "city", "country", "description",
            "idnumber", "lang", "url", "institution");

        $moodleattributes = array();
        foreach ($fields as $field) {
            if (isset($configarray["field_map_$field"])) {
                $moodleattributes[$field] = $configarray["field_map_$field"];
            }
        }
        return $moodleattributes;
    }

    /**
    * Returns true if this authentication plugin is 'internal'.
    *
    * @return bool
    */
    function is_internal() {
        return false;
    }

    /**
    * Returns true if this authentication plugin can change the user's
    * password.
    *
    * @return bool
    */
    function can_change_password() {
        return false;
    }

    function loginpage_hook() {
        // Prevent username from being shown on login page after logout
        $GLOBALS['CFG']->nolastloggedin = true;

        return;
    }

    function logoutpage_hook() {
        global $SESSION, $USER;
        unset($SESSION->SAMLSessionControlled);
        if (!empty($this->config->dologout)) {
            set_moodle_cookie('nobody');
            require_logout();
            require_once('config.php');
            $args = ['logout' => '1', 'logoutpage' => urlencode(new moodle_url('/auth/saml/logout.php'))];
            if (!empty($this->config->logout_redirect)) {
                $args['redirect'] = urlencode($this->config->logout_redirect);
            }
            if (!empty($simplesamlphplogouthook)) {
                redirect($simplesamlphplogouthook);
            } else {
                redirect(new moodle_url('/auth/saml/index.php', $args));
            }
        }

        // If enabled, perform a logout and redirect for SAML users only.
        if (!empty($this->config->logout_redirect) && $USER->auth == 'saml') {
            require_logout();
            redirect($this->config->logout_redirect);
        }
    }

    /**
    * Cleans and returns first of potential many values (multi-valued attributes)
    *
    * @param string $str Possibly multi-valued attribute from Identity Provider
    * @return string Initial part of the passed string upto first semi-colon ';'
    */
    public static function get_first_string($str) {
        $lst = explode(';', $str);
        return trim($lst[0]);
    }

    /**
    * Sync roles
    *
    * @param object $user Moodle user record
    */
    function sync_roles($user) {
        $login_attributes = $GLOBALS['saml_login_attributes'];
        $iscreator = $this->iscreator($login_attributes);
        if ($iscreator === null) {
        	return; //nothing to sync
        }
        if ($roles = get_roles_with_capability('moodle/legacy:coursecreator', CAP_ALLOW)) {
            $creatorrole = array_shift($roles);
            $systemcontext = get_context_instance(CONTEXT_SYSTEM);
            if ($iscreator) {
            	role_assign($creatorrole->id, $user->id, 0, $systemcontext->id, 0, 0, 0, 'saml');
            }
            else {
                if ($this->config->unassigncreators){
                    role_unassign($creatorrole->id, $user->id, 0, $systemcontext->id, 'saml');
                }
            }
        }
    }

    /**
    * isCreator test
    *
    * @param array $login_attributes login attributes mapping
    */
    function iscreator($login_attributes) {
        if (isset($this->config->memberattribute) && isset($login_attributes[$this->config->memberattribute])) {
            $memberof = $login_attributes[$this->config->memberattribute];
            $attrs = explode(";", $this->config->attrcreators);
            foreach ($attrs as $attr) {
                foreach ($memberof as $m) {
                    if ($m === $attr) {
                        return true;
                    }
                }
            }
        }
        return false;
    }


    /**
     * Authentication hook that is fired once the user has been authenticated by Moodle
     * @param object $user the user object.
     * @param string $username the user's username.
     * @param string $password the user's password.
     * @return boolean True on success.
     */
    public function user_authenticated_hook(&$user, $username, $password) {
        global $DB, $CFG;

       // Process the user login attributes and update custom fields in required. (The loop above only runs once!)
       $tablecolumns = 'shortname,id,name,datatype,defaultdata,param1,param2,param3';
       $customprofilefields = $DB->get_records('user_info_field', array(), null, $tablecolumns);
       $configarray = (array) $this->config;

       if (!empty($configarray) && !empty($customprofilefields) && !empty(self::$samlattributes)) {
            // Check if the timecreaed and timemodifed are equal to each other
            $newuser = ($user->timecreated == $user->timemodified) ? true : false;
            auth_saml_sync_custom_profile_fields($user, $configarray, self::$samlattributes, $customprofilefields, $newuser);
       }

        if ($user->auth != 'saml') {
            return false;
        }

        $kronosportal = get_auth_plugin('kronosportal');
        // Get kronos configuration.
        $kronosconfig = get_config('auth_kronosportal');
        $errorurl = '';
        if (!empty($kronosconfig->kronosportal_errorurl)) {
            $errorurl = $kronosconfig->kronosportal_errorurl;
        }

        if (!$user = $DB->get_record('user', array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id))) {
            if (defined('PHPUNIT_COMPOSER_INSTALL')) {
                return false;
            }
            $kronosportal->handleerror($errorurl, 'usermessagemissingdata');
        }

        $kronosportal->username = $user->username;

        // Load custom fields.
        profile_load_data($user);
        // Do validation checks based on Kronos business rules.
        $result = kronosportal_validate_user($user);
        if ($result == "success") {
            if (!empty($kronosconfig->kronosportal_successurl)) {
                $url = $kronosconfig->kronosportal_successurl;
                if (preg_match("/^\//", $url)) {
                    $url = $CFG->wwwroot.$url;
                }
                $SESSION->wantsurl = $url;
            }
        } else {
            if (defined('PHPUNIT_COMPOSER_INSTALL')) {
                return false;
            }
            $kronosportal->handleerror($errorurl, 'usermessage'.$result);
        }

        // Used by php unit tests.
        return true;
    }
}

function auth_saml_get_metadata($metadatain = NULL) {
    if (!empty($metadatain) && is_array($metadatain)) {
        $metadataout = $metadatain;
    } else {
        $metadataout = array();
    }

    $config = get_config('auth/saml');
    if (isset($config->metadata_url) && !empty($config->metadata_url)) {
        $metadataout[$config->metadata_url] = array(
          'name' => array('en' => $config->metadata_name),
          'SingleSignOnService' => $config->metadata_SingleSignOnService,
          'SingleLogoutService' => $config->metadata_SingleLogoutService);
        if (!empty($config->metadata_certData)) {
            $metadataout[$config->metadata_url]['certData'] = $config->metadata_certData;
        } else if (!empty($config->metadata_certFingerprint)) {
            $metadataout[$config->metadata_url]['certFingerprint'] = $config->metadata_certFingerprint;
        }
    }

    return $metadataout;
}
