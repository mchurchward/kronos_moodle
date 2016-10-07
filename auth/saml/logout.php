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
 * logout.php - Logout landing page for auth/saml based SAML 2.0 logout
 *
 * @package    auth_saml
 * @author     Brent Boghosian <brent.boghosian@remote-learner.net>
 * @author     Remote-Learner.net Inc
 * @copyright  2016 onwards Remote-Learner {@link http://www.remote-learner.ca/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

session_start();

unset($_COOKIE['SimpleSAMLAuthToken']);
unset($_SESSION['SimpleSAMLphp_SESSION']);

$redirect = '/';
if (!empty($_SESSION['logout_redirect'])) {
    $redirect = urldecode($_SESSION['logout_redirect']);
    unset($_SESSION['logout_redirect']);
}

session_write_close();

@header('Location: '.$redirect);

