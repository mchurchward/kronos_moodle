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
 * This class contains the form elements used to display the configuration options for this plugin
 *
 * @package    auth_saml
 * @author     Remote-Learner.net Inc
 * @copyright  2014 onwards Remote-Learner {@link http://www.remote-learner.ca/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once(dirname(__FILE__).'/lib.php');

$settings->add(new admin_setting_heading('saml_heading', '', get_string('auth_samldescription', 'auth_saml', $CFG->dirroot)));

$setting = new admin_setting_configtext('username', get_string('auth_saml_username', 'auth_saml'),
        get_string('auth_saml_username_description', 'auth_saml'), '', PARAM_TEXT);
$setting->plugin = AUTH_SAML;
$settings->add($setting);

$setting = new admin_setting_configtext('userfield', get_string('auth_saml_userfield', 'auth_saml'),
        get_string('auth_saml_userfield_description', 'auth_saml'), '', PARAM_TEXT);
$setting->plugin = AUTH_SAML;
$settings->add($setting);

$setting = new admin_setting_configcheckbox('dologout', get_string('auth_saml_dologout', 'auth_saml'),
        get_string('auth_saml_dologout_description', 'auth_saml'), '', 'on', '');
$setting->plugin = AUTH_SAML;
$settings->add($setting);

$setting = new admin_setting_configcheckbox('createusers', get_string('auth_saml_createusers', 'auth_saml'),
        get_string('auth_saml_createusers_description', 'auth_saml'), '', 'on', '');
$setting->plugin = AUTH_SAML;
$settings->add($setting);

$setting = new admin_setting_configcheckbox('duallogin', get_string('auth_saml_duallogin', 'auth_saml'),
        get_string('auth_saml_duallogin_description', 'auth_saml'), '', 'on', '');
$setting->plugin = AUTH_SAML;
$settings->add($setting);

$setting = new admin_setting_configtext('logout_redirect', get_string('auth_saml_logout_redirect', 'auth_saml'),
        get_string('auth_saml_logout_redirect_description', 'auth_saml'), '', PARAM_URL);
$setting->plugin = AUTH_SAML;
$settings->add($setting);

// Note: ReturnTo/RelayState & ErrorURL should _not_ be configurable since this is for Moodle!
/*
$setting = new admin_setting_configtext('relaystate', get_string('auth_saml_relaystate', 'auth_saml'),
        get_string('auth_saml_relaystate_description', 'auth_saml'), '', PARAM_URL);
$setting->plugin = AUTH_SAML;
$settings->add($setting);

$setting = new admin_setting_configtext('errorurl', get_string('auth_saml_errorurl', 'auth_saml'),
        get_string('auth_saml_errorurl_description', 'auth_saml'), '', PARAM_URL);
$setting->plugin = AUTH_SAML;
$settings->add($setting);
*/

$settings->add(new admin_setting_heading('coursecreators_heading', get_string('coursecreators'), ''));

$setting = new admin_setting_configtext('memberattribute', get_string('auth_saml_memberattribute', 'auth_saml'),
        get_string('auth_saml_memberattribute_description', 'auth_saml'), '', PARAM_URL);
$setting->plugin = AUTH_SAML;
$settings->add($setting);

$setting = new admin_setting_configtext('attrcreators', get_string('auth_saml_attrcreators', 'auth_saml'),
        get_string('auth_saml_attrcreators_description', 'auth_saml'), '', PARAM_URL);
$setting->plugin = AUTH_SAML;
$settings->add($setting);

$setting = new admin_setting_configcheckbox('unassigncreators', get_string('auth_saml_unassigncreators', 'auth_saml'),
        get_string('auth_saml_unassigncreators_description', 'auth_saml'), '', 'on', '');
$setting->plugin = AUTH_SAML;
$settings->add($setting);

$profilefields = new profile_fields_lock_options(AUTH_SAML, $settings, true, true);
$profilefields->add_profile_to_settings();
