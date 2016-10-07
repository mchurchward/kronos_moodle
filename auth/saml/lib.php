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
 * Library of methods and constants
 *
 * @package    auth_saml
 * @author     Remote-Learner.net Inc
 * @copyright  2014 onwards Remote-Learner {@link http://www.remote-learner.ca/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once($CFG->dirroot.'/admin/tool/uploaduser/locallib.php');
require_once($CFG->dirroot.'/user/lib.php');

/* SAML configuration name */
define('AUTH_SAML', 'auth/saml');
/* Custom profile field prefix naming convention */
define('CUSTOM_PROFILE_PREFIX', 'field_map_custom_');
define('CUSTOM_PROFILE_UPDATE_LOCAL_PREFIX', 'field_updatelocal_custom_');

/**
 * This class setup and prints the admin settings lock options for standard profile fields.
 * It is moddled after @see print_auth_lock_options()
 */
class profile_fields_lock_options {

    /** @var array $fields Any array of fields to be printed */
    protected $fields = array(
        'firstname' => '',
        'lastname' => '',
        'email' => '',
        'city' => '',
        'country' => '',
        'lang' => '',
        'description' => '',
        'url' => '',
        'idnumber' => '',
        'institution' => '',
        'department' => '',
        'phone1' => '',
        'phone2' => '',
        'address' => ''
    );

    /** @var bool $retrieveopts A flag to display additional options for updating profile fields */
    protected $retrieveopts;

    /** @var admin_settingpage $settings an instance of the admin_settingpage class */
    protected $settings;

    /** @var array $customfields an array of record objects from mdl_user_info_field table */
    protected $customfields = array();

    /**
     * Constructor to initialize options for printing
     * @throws coding_exception if invalid parameters are passed
     * @param string $pluginname The name of the authentication plug-in
     * @param admin_settingpage $settings an instance of the admin_settingpage class
     * @param bool $retrieveopt If set to true will additional options will be displayed for updating profile fields
     * @param bool $customfields If set to true Moodle custom profile fields will be displayed on the page
     */
    public function __construct($pluginname = 'auth_saml', $settings, $retrieveopts = false, $customfields = false) {
        global $DB;

        if (empty($pluginname)) {
            throw new coding_exception('Plugin name is an empty string', 'First parameter in constructor cannot be an empty string');
        }

        if (!$settings instanceof admin_settingpage) {
            throw new coding_exception('Settings must be an instance of admin_settingpage', 'Second parameter in constructor must be an instance of admin_settingpage');
        }

        if (!is_bool($retrieveopts)) {
            throw new coding_exception('Retrieve options must be a boolean', 'Third parameter in constructor must be a boolean');
        }

        if ($customfields) {
            $this->customfields = $DB->get_records('user_info_field', array());
        }

        $this->pluginname = $pluginname;
        $this->retrieveopts = $retrieveopts;
        $this->settings = $settings;
    }

    /**
     * Set retrieve options to true or false
     * @throws coding_exception if invalid parameters are passed
     * @param bool $value Set flag to true to display additional options
     */
    public function set_retrieve_options($value) {
        if (!is_bool($value)) {
            throw new coding_exception('Value must be a boolean', 'Parameter for set_retrieve_options() must be a boolean');
        }

        $this->retrieveopts = $value;
    }

    /**
     * Return retrieve options flag
     * @return bool Return retrieve options flag value
     */
    public function get_retrieve_options() {
        return $this->retrieveopts;
    }

    /**
     * Checks if the profile name is valid
     * @param string $profile The provile name
     * @return bool True if the profile name exists.
     */
    public function profile_name_is_valid($profile) {
        if (array_key_exists($profile, $this->fields)) {
            return true;
        }

        return false;
    }

    /**
     * Change default language string used for a user profile field
     * @param string $profile The profile to be changed
     * @param string $languagefile The language file to use for (ex. 'auth_yourpluginname')
     * @return bool True if successful.
     */
    public function change_profile_language_string($profile, $languagefile = '') {
        if (!$this->profile_name_is_valid($profile)) {
            return false;
        }

        $this->fields[$profile] = $languagefile;
        return true;
    }

    /**
     * Adds the profile fields to the settings page
     */
    public function add_profile_to_settings() {
        $languagestring = '';
        $updatelocal = array(
            'oncreate' => get_string('update_oncreate', 'auth'),
            'onlogin' => get_string('update_onlogin', 'auth')
        );
        $lockoptions = array(
            'unlocked' => get_string('unlocked', 'auth'),
            'unlockedifempty' => get_string('unlockedifempty', 'auth'),
            'locked' => get_string('locked', 'auth')
        );

        foreach ($this->fields as $profile => $language) {
            // Lang and phone1 are special cases
            if ('lang' === $profile && empty($language)) {
                $languagestring = get_string('language');
            } else if ('phone1' === $profile && empty($language)) {
                $languagestring = get_string('phone');
            } else if (empty($language)) {
                $languagestring = get_string($profile);
            } else {
                $languagestring = get_string($profile, $language);
            }

            // Print heading
            $this->settings->add(new admin_setting_heading($profile.'_heading', $languagestring, ''));

            if (!empty($this->retrieveopts)) {
                $setting = new admin_setting_configtext('field_map_'.$profile, $languagestring, '', '', PARAM_TEXT);
                $setting->plugin = $this->pluginname;
                $this->settings->add($setting);

                $setting = new admin_setting_configselect('field_updatelocal_'.$profile, get_string('auth_updatelocal', 'auth'), '', '', $updatelocal);
                $setting->plugin = $this->pluginname;
                $this->settings->add($setting);
            }

            $setting = new admin_setting_configselect('field_lock_'.$profile, get_string('auth_fieldlock', 'auth'), '', '', $lockoptions);
            $setting->plugin = $this->pluginname;
            $this->settings->add($setting);
        }

        // Print custom fields options
        foreach ($this->customfields as $customfield) {
            $append = ' ('.get_string('custom_field_header', 'auth_saml').')';
            $this->settings->add(new admin_setting_heading($customfield->shortname.'_heading', $customfield->name.$append, ''));

            if (!empty($this->retrieveopts)) {
                $description = get_string('custom_field_sync_desc', 'auth_saml');
                $setting = new admin_setting_configtext(CUSTOM_PROFILE_PREFIX.$customfield->shortname, $customfield->name, $description, '', PARAM_TEXT);
                $setting->plugin = $this->pluginname;
                $this->settings->add($setting);

                $description = get_string('update_local_desc', 'auth_saml');
                $setting = new admin_setting_configselect(CUSTOM_PROFILE_UPDATE_LOCAL_PREFIX.$customfield->shortname, get_string('auth_updatelocal', 'auth'),
                        $description, '', $updatelocal);
                $setting->plugin = $this->pluginname;
                $this->settings->add($setting);
            }
        }
    }
}

/**
 * This function processes the synchronizing of the SAML fields with Moodle custom profile fields
 * @param object $user the user object
 * @param array $authconfig the authentication plug-in config settings
 * @param array $samlattributes the login attributes passed from the SAML server
 * @param array $customfields an array of database records for from mdl_user_info_field
 * @param bool $newuser true if the user is new
 */
function auth_saml_sync_custom_profile_fields($user, $authconfig, $samlattributes, $customfields, $newuser) {
    // Clone the user object because I don't want to add properties that might conflict later on in the process
    $userclone = clone($user);
    // Load the profile data into the user object
    profile_load_data($userclone);
    // Needs updating flag
    $needsupdate = false;

    /*
     * Iterate through each auth config setting for custom profile fields and ensure the following:
     * 1. The custom profile field name exists in the config values
     * 2. The custom field shortname exists in the the user_info_field table
     * 3. The auth setting for the custom profile field is mapped to a SAML field
     * 4. If the field needs to be updated only upon creation of on every login
     * 5. The SAML value is valid for the custom field is it synchronized with
     * If one of the conditions fails, then the profile property, in the user object, is unset to save
     * on database insert/updates as well for the edge case where a menu profile field doesn't have a default value.
     *
     * Note: Each condition is a separate function in order to make phpunit testing easier
     */
    foreach ($authconfig as $name => $value) {
        $valid = true;

        // Only look for fields that are marked as a custom field in the auth config setting
        if (!valid_custom_field_prefix($name)) {
            continue;
        }

        if (!valid_matching_config_profile_shortname($name, $customfields)) {
            continue;
        }

        // Extract the custom field shortname name from the auth settings
        $length = strlen(CUSTOM_PROFILE_PREFIX);
        $customfieldname = substr($name, $length);

        // Check if the custom field is mapped to any SAML field AND if the mapped field exists in the SAML login attributes
        if (!valid_matching_field_map_to_saml_attribute($value, $samlattributes)) {
            unset($userclone->{"profile_field_$customfieldname"});
            continue;
        }

        // Check if the field needs to be updated on creation or on every login
        if (!needs_updating($authconfig, $customfieldname, $newuser)) {
            unset($userclone->{"profile_field_$customfieldname"});
            continue;
        }

        if ('menu' === $customfields[$customfieldname]->datatype) {
            // Validate the SAML data with the acceptable values for menu of choices
            $valid = validate_saml_data_for_menu($customfields[$customfieldname], $samlattributes[$value][0]);
        } else if ('datetime' === $customfields[$customfieldname]->datatype) {
            // Validate the SAML data with the acceptable date type minimum and maximum values
            $valid = validate_saml_data_for_datetime($customfields[$customfieldname], $samlattributes[$value][0]);
        }

        // If not valid then skip sync of this field
        if (!$valid) {
            unset($userclone->{"profile_field_$customfieldname"});
            continue;
        }

        // Clean and set user object property to saml attribute value
        if ('checkbox' === $customfields[$customfieldname]->datatype) {
            // If the field is a checkbox then use 1 or 0 for checkec and unchecked
            $userclone->{"profile_field_$customfieldname"} = (empty($samlattributes[$value][0])) ? 0 : 1;
            $needsupdate = true;
        } else if (!is_array($userclone->{"profile_field_$customfieldname"})) {
            // If user object property is not an array then it is not a textarea profile field
            $userclone->{"profile_field_$customfieldname"} = clean_saml_data_for_custom_profile($samlattributes[$value][0]);
            $needsupdate = true;
        } else {
            // Onther areas of Moodle use PARAM_RAW when inserting into text fields
            $userclone->{"profile_field_$customfieldname"}['text'] = clean_saml_data_for_custom_profile($samlattributes[$value][0], PARAM_TEXT);
            $needsupdate = true;
        }

    }

    // Save the profile data
    if ($needsupdate) {
        // Pre process the custom profile field data
        $userclone = uu_pre_process_custom_profile_data($userclone);
        // Save profile data
        profile_save_data($userclone);
        // Trigger user_updated event
        user_update_user($userclone, false);
    }
}

/**
 * Validate if the field is marked with the custom field prefix
 * @param string $name the name of the auth config value
 * @return bool true if valid
 */
function valid_custom_field_prefix($name) {
    $pos = strpos($name, CUSTOM_PROFILE_PREFIX);
    if (0 != $pos || false === $pos) {
        return false;
    }

    return true;
}

/**
 * Validate whether the profile field name in the auth config matches a profile shortname from the user_info_field table
 * @param $string $name the name of the auth config value
 * @param array $customfields an array of database records for from mdl_user_info_field
 * @return bool true if valid
 */
function valid_matching_config_profile_shortname($name, $customfields) {
    // Extract the custom field shortname name from the auth settings
    $length = strlen(CUSTOM_PROFILE_PREFIX);
    $customfieldname = substr($name, $length);

    // Check if the custom field shortname exists in the user_info_field records
    if (!isset($customfields[$customfieldname])) {
        return false;
    }

    return true;
}

/**
 * Validate whether the auth config field mapping exists in the SAML attributes
 * @param string $value a SAML field name
 * @param array $samlattributes an array of arrays with SAML fields as keys 
 * @param bool true if valid
 */
function valid_matching_field_map_to_saml_attribute($value, $samlattributes) {
    if (empty($value)) {
        return false;
    }

    if (!isset($samlattributes[$value])) {
        return false;
    }

    return true;
}

/**
 * Determine if the field needs updating based on the auth config 'updatelocal' value and whether the user is new
 * @param array $authconfig an array of SAML configuration values
 * @param string $name name of the field configuration (only the field shortname)
 * @param bool $newuser true if this is a new user, else false
 * @return bool true if the field needs updating
 */
function needs_updating($authconfig, $name, $newuser) {
    if ('oncreate' === $authconfig[CUSTOM_PROFILE_UPDATE_LOCAL_PREFIX.$name] && !$newuser) {
        return false;
    }

    return true;
}
/**
 * This function validates the SAML data against a custom field menu of choices.
 * @param object $customfield the custom field definition object, a record from mdl_user_info_field
 * @param string $samlvalue a SAML user attribute
 * @param bool true if SAML attribute is valid for the profile field.
 */
function validate_saml_data_for_menu($customfield, $samlvalue) {
    if (empty($samlvalue)) {
        return false;
    }

    $choices = explode("\n", $customfield->param1);
    $valid = false;

    foreach ($choices as $choice) {
        if (trim($samlvalue) === $choice) {
            $valid = true;
            break;
        }
    }

    return $valid;
}

/**
 * This function validates the SAML data against a custom field datetime.  Currenly assumes the data and time will be an integer.
 * @param object $customfield the custom field definition object, a record from mdl_user_info_field
 * @param string $samlvalue a SAML user attribute
 * @param bool true if SAML attribute is valid for the profile field.
 */
function validate_saml_data_for_datetime($customfield, $samlvalue) {
    $timestamp = (int) $samlvalue;

    // Check if the converted integer is a zero
    if (0 == $timestamp) {
        return false;
    }

    // Check if the the timestamp is after the start year and and before the end year
    $datearray = getdate($timestamp);

    if ((int) $datearray['year'] < (int) $customfield->param1 || (int) $datearray['year'] > $customfield->param2) {
        return false;
    }

    return true;
}

/**
 * Cleans the parameter and returns the result.
 * @param string $value the value to be cleaned
 * @param string $cleantype the type of cleaning that needs to be done.  @see lib/moodlelib.php constants for acceptable values
 * @return string the cleaned value
 */
function clean_saml_data_for_custom_profile($value, $cleantype = PARAM_NOTAGS) {
    if (empty($value)) {
        return '';
    }

    $value = trim(clean_param($value, $cleantype));
    return $value;
}
