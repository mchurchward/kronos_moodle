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
$string['auth_samltitle']         = 'SAML Authentication';
$string['auth_samldescription']   = 'SSO Authentication using SimpleSAML.';
$string['auth_saml_dologout'] = 'Log out from Identity Provider';
$string['auth_saml_dologout_description'] = 'Check to have the module log out from Identity Provider when user log out from Moodle';
$string['auth_saml_createusers'] = 'Automatically create users';
$string['auth_saml_createusers_description'] = 'Check to have the module log automatically create users accounts if none exists';
$string['auth_saml_duallogin'] = 'Enable Dual login for users';
$string['auth_saml_duallogin_description'] = 'Enable use of users assigned login auth module and SAML';
$string['auth_saml_notshowusername'] = 'Do not show username';
$string['auth_saml_notshowusername_description'] = 'Check to have Moodle not show the username for users logging in by Identity Provider';
$string['errorbadlib'] = 'SimpleSAMLPHP lib directory $a is not correct.  Please edit the auth/saml/config.php file correctly.';
$string['errorbadconfig'] = 'SimpleSAMLPHP config directory $a is in correct.  Please edit the auth/saml/config.php file correctly.';
$string['auth_saml_username'] = 'SAML username mapping';
$string['auth_saml_username_description'] = 'SAML attribute that is mapped to Moodle username - this defaults to mail';
$string['auth_saml_memberattribute'] = 'Member attribute';
$string['auth_saml_memberattribute_description'] = 'Optional: Overrides user member attribute, when user belongs to a group. Usually \'member\'';
$string['auth_saml_attrcreators'] = 'Attribute creators';
$string['auth_saml_attrcreators_description'] = 'List of groups or contexts whose members are allowed to create attributes. Separate multiple groups with \';\'. Usually something like \'cn=teachers,ou=staff,o=myorg\'';
$string['auth_saml_unassigncreators'] = 'Unassign creators';
$string['auth_saml_unassigncreators_description'] = 'Unassign creators role if unmatch specified condition.';
$string['retriesexceeded'] = 'Maximum number of retries exceeded ($a) - there must be a problem with the Identity Service';
$string['pluginauthfailed'] = 'The SAML authentication plugin failed - user $a disallowed (no user auto creation?) or dual login disabled';
$string['pluginauthfailedusername'] = 'The SAML authentication plugin failed - user $a disallowed due to invalid username format';
$string['auth_saml_username_error'] = 'IdP returned a set of data that does not contain the SAML username mapping field. This field is required to login';
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
