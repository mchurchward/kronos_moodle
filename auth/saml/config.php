<?php 
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
 * config.php - config file for auth/saml based SAML 2.0 login
 * 
 * make sure that you define both the SimpleSAMLphp lib directory and config
 * directory for the associated SP and also specify the IdP that it will talk to
 * 
 * 
 * @package        auth_saml
 * @originalauthor Martin Dougiamas
 * @author         Erlend Strømsvik - Ny Media AS
 * @author         Piers Harding - made quite a number of changes
 * @author         Remote-Learner.net Inc
 * @copyright      2014 onwards Remote-Learner {@link http://www.remote-learner.ca/}
 * @license        http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('SAML_RETRIES', 3);
define('SAML_DEBUG', 1);

$simplesamllib = '/usr/share/simplesamlphp';
// Set the location of the config SimpleSAMLphp config file.
$basename = basename(dirname(dirname(dirname(__FILE__))));
$simplesamlconfig = "/mnt/data/conf/simplesamlphp_{$basename}/config";
$simplesamlsp = 'default-sp';

// The alternative to the normal redirect for logout from the logout hook.  Set 'logout_redirect' in the settings instead of using this.
// $simplesamlphplogouthook = 'https://some.moodle.x/auth/saml/index.php?logout=1';
$simplesamlphplogouthook = '';