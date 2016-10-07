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

$string['pluginname']         = 'SAML Authentication';
$string['auth_samltitle']         = 'SAML Authentication';
$string['auth_samldescription']   = 'SSO Authentication using SimpleSAML.';
$string['auth_saml_certdir'] = 'Certificate/Key Directory';
$string['auth_saml_certdir_description'] = 'Enter the directory, relative to moodledata, where the certificate/key files can be found.';
$string['auth_saml_dologout'] = 'Log out from Identity Provider';
$string['auth_saml_dologout_description'] = 'Check to have the module log out from Identity Provider when user log out from Moodle';
$string['auth_saml_createusers'] = 'Automatically create users';
$string['auth_saml_createusers_description'] = 'Check to have the module log automatically create users accounts if none exists';
$string['auth_saml_duallogin'] = 'Enable Dual login for users';
$string['auth_saml_duallogin_description'] = 'Enable use of users assigned login auth module and SAML';
$string['auth_saml_notshowusername'] = 'Do not show username';
$string['auth_saml_notshowusername_description'] = 'Check to have Moodle not show the username for users logging in by Identity Provider';
$string['notconfigured'] = 'auth/saml is not configured for use';
$string['errorbadlib'] = 'SimpleSAMLPHP lib directory {$a} is not correct.  Please edit the auth/saml/config.php file correctly.';
$string['errorbadconfig'] = 'SimpleSAMLPHP config directory {$a} is in correct.  Please edit the auth/saml/config.php file correctly.';
$string['auth_saml_username'] = 'SAML username mapping';
$string['auth_saml_username_description'] = 'SAML attribute that is mapped to Moodle username - this defaults to mail';
$string['auth_saml_userfield'] = 'Moodle username mapping';
$string['auth_saml_userfield_description'] = 'Moodle user field that is mapped to SAML username attribute - this defaults to username, but could be idnumber, or email';
$string['auth_saml_memberattribute'] = 'Member attribute';
$string['auth_saml_memberattribute_description'] = 'Optional: Overrides user member attribute, when user belongs to a group. Usually \'member\'';
$string['auth_saml_attrcreators'] = 'Attribute creators';
$string['auth_saml_attrcreators_description'] = 'List of groups or contexts whose members are allowed to create attributes. Separate multiple groups with \';\'. Usually something like \'cn=teachers,ou=staff,o=myorg\'';
$string['auth_saml_logout_redirect'] = 'Logout redirect URL';
$string['auth_saml_logout_redirect_description'] = 'If set, users will be redirect to this URL after successfully logging out of Moodle. Be sure to enter a valid URL otherwise users will be redirected either to the incorrect place or see an error message after logout.';
$string['auth_saml_unassigncreators'] = 'Unassign creators';
$string['auth_saml_unassigncreators_description'] = 'Unassign creators role if unmatch specified condition.';
$string['auth_saml_metadata_url'] = 'Remote IdP URL';
$string['auth_saml_metadata_url_description'] = 'URL of remote SAML identity provider for metadata.';
$string['auth_saml_metadata_name'] = 'IdP name';
$string['auth_saml_metadata_name_description'] = 'Textual name to call IdP.';
$string['auth_saml_metadata_SingleSignOnService'] = 'SSO URL';
$string['auth_saml_metadata_SingleSignOnService_description'] = 'URL for IdP single sign on.';
$string['auth_saml_metadata_SingleLogoutService'] = 'SLO URL';
$string['auth_saml_metadata_SingleLogoutService_description'] = 'URL for IdP single logout.';
$string['auth_saml_metadata_certData'] = 'Certificate data';
$string['auth_saml_metadata_certData_description'] = 'Base64 encoded certificate data (note - overrides fingerprint if specified).';
$string['auth_saml_metadata_certFingerprint'] = 'Certificate fingerprint';
$string['auth_saml_metadata_certFingerprint_description'] = 'Certificate fingerprint (note - only used if certificate data is not specified).';
$string['retriesexceeded'] = 'Maximum number of retries exceeded ({$a}) - there must be a problem with the Identity Service';
$string['invalidconfig'] = 'Invalid configuration config.php for auth/saml';
$string['pluginauthfailed'] = 'The SAML authentication plugin failed - user {$a} disallowed (no user auto creation?) or dual login disabled';
$string['pluginauthfailedusername'] = 'The SAML authentication plugin failed - user {$a} disallowed due to invalid username format';
$string['auth_saml_username_error'] = 'IdP returned a set of data that does not contain the SAML username mapping field. This field is required to login';
$string['loginfailed'] = 'SAML 2.0 login failed when negotiating with the IdP';
$string['custom_field_sync_desc'] = 'Input the SAML field that is to sync with this Moodle custom profile field.';
$string['custom_field_header'] = 'Moodle custom profile field';
$string['update_local_desc'] = 'On creation: sync SAML field with Moodle custom profile field only when the user is first created.  On every login: sync SAML field with Moodle profile field every time the user logs into Moodle';
$string['noconfigsettings'] = 'No configuration settings have been set.';
$string['sourcenotexist'] = 'No source directory path specified.';
$string['confignotexist'] = 'No configuration directory path specified.';
$string['error_code'] = 'Error code: {$a}';
$string['emptyidplist'] = 'Empty IdP list in configuration settings';
$string['emptyreferer'] = 'Unknown referer';
$string['noidpmatch'] = 'No matches found for identity provider';
$string['auth_saml_relaystate'] = 'Login RelayState URL';
$string['auth_saml_relaystate_description'] = 'Override the RelayState URL.  The URL the user should be returned to after authentication.';
$string['auth_saml_errorurl'] = 'Login Error URL';
$string['auth_saml_errorurl_description'] = 'Override the ErrorURL, The URL the user will be redirected to if there was an error with the authentication.';
