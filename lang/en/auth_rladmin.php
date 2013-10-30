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
 * Language strings
 *
 * @package    auth_rladmin
 * @copyright  2012 Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['authconfigoverride'] = 'The auth setting is overriden in the config.php file, and does not contain the rladmin authentication plugin.  Please add "rladmin" to auth in config.php (it is a comma-separated list).';
$string['auth_rladmindescription'] = 'Allows Remote-Learner staff to securely log into client sites';
$string['cannotgeneratetoken']= 'Could not generate token.  Either something bad has happened, or someone is running an improbability drive.';
$string['curl_error'] = 'CURL error: {$a}';
$string['http_error'] = 'HTTP error (code: {$a->http_code}): {$a->error}';
$string['internalerror'] = 'Internal error.  The universe is in an inconsistent state.  Please reboot the universe and try again.';
$string['invalidtoken'] = 'Unknown token.  The token is either invalid or expired.';
$string['invalidresult'] = 'Invalid result from ID provider: {$a->error}';
$string['pluginname'] = 'Remote-Learner Admin';
$string['siteadminconfigoverride'] = 'The siteadmin setting is overridden in the config.php file, and does not contain the rladmin user.  Please add id {$a} to siteadmin in config.php (it is a comma-separated list).';
