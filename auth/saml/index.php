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
 * index.php - landing page for auth/saml based SAML 2.0 login
 *
 * builds basic CFG and DB connection to Moodle, to then get the saml plugin
 * configuration.
 *
 * Does the SimpleSAMLPHP calls to query SAML 2.0 session status,
 *
 * Builds the rest of Moodle session, and then logs the user in.
 *
 * @originalauthor Martin Dougiamas
 * @package    auth_saml
 * @author     Erlend Strømsvik - Ny Media AS
 * @author     Piers Harding - made quite a number of changes
 * @author     Remote-Learner.net Inc
 * @copyright  2014 onwards Remote-Learner {@link http://www.remote-learner.ca/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

session_start();

// Require the authentication config file required for defining the SAML configuration location
require_once(dirname(__FILE__).'/config.php');
if (!file_exists($simplesamllib.'/lib/_autoload.php')) {
    // invalid config and lib directory
    session_write_close();
    error_log('Unable to find file '.$simplesamllib.'/lib/_autoload.php');
    die();
}
// now boot strap SimpleSAMLPHP and get everything that we could
// possibly need data wise
require_once($simplesamllib.'/lib/_autoload.php');

$simplesamlphpconfig = getenv("SIMPLESAMLPHP_CONFIG_DIR");
if (empty($simplesamlphpconfig) || !file_exists($simplesamlphpconfig)) {
    $simplesamlphpconfig = $simplesamlconfig;
}

if (!empty($simplesamlphpconfig) && file_exists($simplesamlphpconfig)) {
    SimpleSAML_Configuration::setConfigDir($simplesamlphpconfig);
}
// grab the ssphp instance information
$as = new SimpleSAML_Auth_Simple($simplesamlsp);
$validsamlsession = !empty($as); // TBD?

/**
 * check that the saml session is OK - if not send to the IdP for authentication
 * if good, then do the Moodle login, and send to the home page, or landing page
 * if otherwise specified
 */
if (isset($_SESSION['retries'])) {
    $_SESSION['retries'] = $_SESSION['retries'] + 1;
}
else {
    $_SESSION['retries'] = 1;
}
auth_saml_err('session : '.var_export($_SESSION, true));
auth_saml_err('checked retries NOW : '.$_SESSION['retries']);

if ($_SESSION['retries'] > SAML_RETRIES) {
    // too many tries at logging in
    unset($_SESSION['retries']);
    session_write_close();
    require_once('../../config.php');
    print_error('retriesexceeded', 'auth_saml', '', SAML_RETRIES);
    die();
}

if (isset($_GET["logout"])) {
    if (empty($_GET['redirect'])) {
        if (preg_match('/^(.*?)auth\/saml.*?$/', auth_saml_qualified_me().auth_saml_me(), $matches)) {
            $simplesamlphplogoutlink = $matches[1];
        } else {
            $simplesamlphplogoutlink = auth_saml_qualified_me();
        }
    } else {
        $simplesamlphplogoutlink = urldecode($_GET['redirect']);
    }
    $logoutpage = $simplesamlphplogoutlink;
    if (!empty($_GET['logoutpage'])) {
        $logoutpage = urldecode($_GET['logoutpage']);
        $_SESSION['logout_redirect'] = urlencode($simplesamlphplogoutlink);
    }
    unset($_SESSION['retries']);
    if ($validsamlsession) {
        $as->logout($logoutpage);
    } else {
        @session_write_close();
        @header($_SERVER['SERVER_PROTOCOL'].' 303 See Other');
        @header('Location: '.$simplesamlphplogoutlink);
        die;
    }
    // should never get here
    exit(0);
} else {
    $errorurl = auth_saml_qualified_me().'/'.dirname($_SERVER['REQUEST_URI']).'/error.php';
    // Set the IdP to be used when authenticatng and retrieving attributes.
    $as->requireAuth(['ErrorURL' => $errorurl]);
    unset($_SESSION['retries']);
}

session_write_close();
// Require Moodle's config.
require_once(dirname(__FILE__).'/../../config.php');
global $CFG, $USER, $SESSION;

$pluginconfig = get_config('auth/saml');

// Check if the authentication plug-in is enabled, has configuration settings and has the minimum required configuration set.
if (!is_enabled_auth('saml')) {
    print_error('notconfigured', 'auth_saml');
} else if (empty($pluginconfig)) {
    print_error('noconfigsettings', 'auth_saml');
}

if (!empty($_GET['wantsurl'])) {
    $SESSION->wantsurl = $_GET['wantsurl'];
}

// get the SAML user attributes
$samlattributes = $as->getAttributes();

// if we get here, then everything is OK - shutdown the ssphp
// side of things, and continue with Moodle
$wantsurl = isset($SESSION->wantsurl) ? $SESSION->wantsurl : false;
unset($_SESSION['retries']);
unset($SESSION->wantsurl);

if (!isset($pluginconfig->userfield) || empty($pluginconfig->userfield)) {
    $pluginconfig->userfield = 'username';
}

// Valid session. Register or update user in Moodle, log him on, and redirect to Moodle front
// we require the plugin to know that we are now doing a saml login in hook puser_login
$GLOBALS['saml_login'] = TRUE;

// make variables accessible to saml->get_userinfo. Information will be requested from authenticate_user_login -> create_user_record / update_user_record
$GLOBALS['saml_login_attributes'] = $samlattributes;

// check user name attribute actually passed
if (!isset($samlattributes[$pluginconfig->username])) {
    error_log('auth_saml: auth failed due to missing username saml attribute: '.$pluginconfig->username);
    session_write_close();
    $USER = new object();
    $USER->id = 0;
    require_once('../../config.php');
    print_error(get_string("auth_saml_username_error", "auth_saml"));
}

// check that there isn't anything nasty in the username
$username = strtolower($samlattributes[$pluginconfig->username][0]);
if ($username != clean_param($username, PARAM_TEXT)) {
    error_log('auth_saml: auth failed due to illegal characters in username: '.$username);
    session_write_close();
    $USER = new object();
    $USER->id = 0;
    require_once('../../config.php');
    print_error('pluginauthfailedusername', 'auth_saml', '', clean_param($samlattributes[$pluginconfig->username][0], PARAM_TEXT));
}

// just passes time as a password. User will never log in directly to moodle with this password anyway or so we hope?
// check if users are allowed to be created and if the user exists
$userid = 0;
$userdata = get_complete_user_data($pluginconfig->userfield, $username);
if (isset($pluginconfig->createusers)) {
    if (!$pluginconfig->createusers && !$userdata) {
        session_write_close();
        $USER = new object();
        $USER->id = 0;
        require_once('../../config.php');
        print_error('pluginauthfailed', 'auth_saml', '', $pluginconfig->userfield.'/'.$samlattributes[$pluginconfig->username][0]);
    }
}
// swap username for Moodle one - if exists
if ($userdata) {
    $username = $userdata->username;
    $userid = isset($userdata->id) ? $userdata->id : 0;
}

//error_log('auth_saml: authenticating username: '.$username);
//error_log('auth_saml: saml attrs: '.var_export($samlattributes, true));

// Set SAML auth property
$authplugin = get_auth_plugin('saml');
$classname = get_class($authplugin);
$classname::$samlattributes = $GLOBALS['saml_login_attributes'];

if (isset($pluginconfig->duallogin) && $pluginconfig->duallogin) {
    $USER = auth_saml_authenticate_user_login($username, time(), $userid);
}
else {
    $USER = authenticate_user_login($username, time());
}

// check that the signin worked
if ($USER == false) {
    session_write_close();
    $USER = new object();
    $USER->id = 0;
    require_once('../../config.php');
    print_error('pluginauthfailed', 'auth_saml', '', $samlattributes[$pluginconfig->username][0]);
}

//error_log('auth_saml: USER logged in: '.var_export($USER, true));

$USER->loggedin = true;
$USER->site     = $CFG->wwwroot;

// complete the user login sequence
$USER = get_complete_user_data('id', $USER->id);

// complete the setup of the user
complete_user_login($USER);  // This call will trigger the user_loggedin event which will be logged.

// just fast copied this from some other module - might not work...
if (isset($wantsurl) and (strpos($wantsurl, $CFG->wwwroot) === 0)) {
    $urltogo = clean_param($wantsurl, PARAM_URL);
} else {
    $urltogo = $CFG->wwwroot.'/';
}

//auth_saml_err($urltogo);
//error_log('auth_saml: jump to: '.$urltogo);

// flag this as a SAML based login
$SESSION->SAMLSessionControlled = true;
redirect($urltogo);


/**
 * Copied from moodlelib:authenticate_user_login()
 *
 * WHY? because I need to hard code the plugins to auth_saml, and this user
 * may be set to any number of other types of login method
 *
 * First of all - make sure that they aren't nologin - we don't mess with that!
 *
 *
 * Given a username and password, this function looks them
 * up using the currently selected authentication mechanism,
 * and if the authentication is successful, it returns a
 * valid $user object from the 'user' table.
 *
 * Uses auth_ functions from the currently active auth module
 *
 * After authenticate_user_login() returns success, you will need to
 * log that the user has logged in, and call complete_user_login() to set
 * the session up.
 *
 * @uses $CFG
 * @param string $username  User's username (with system magic quotes)
 * @param string $password  User's password (with system magic quotes)
 * @param int $userid the User's id, zero (0) if it doesn't exist.
 * @return user|flase A {@link $USER} object or false if error
 */
function auth_saml_authenticate_user_login($username, $password, $userid = 0) {

    global $CFG, $DB;

    // ensure that only saml auth module is chosen
    $authsenabled = get_enabled_auth_plugins();
    $eventparams = ['userid' => $userid, 'other' => ['username' => $username]];
    if ($user = get_complete_user_data('username', $username, $CFG->mnet_localhost_id)) {
        $auth = empty($user->auth) ? 'manual' : $user->auth;  // use manual if auth not set
        if (!empty($user->suspended)) {
            $eventparams['other']['reason'] = 'Suspended Login';
            $event = \core\event\user_login_failed::create($eventparams);
            $event->trigger();
            error_log('[client '.getremoteaddr()."]  $CFG->wwwroot  Suspended Login:  $username  ".$_SERVER['HTTP_USER_AGENT']);
            return false;
        }
        if ($auth=='nologin' or !is_enabled_auth($auth)) {
            $eventparams['other']['reason'] = 'Disabled Login';
            $event = \core\event\user_login_failed::create($eventparams);
            $event->trigger();
            error_log('[client '.getremoteaddr()."]  $CFG->wwwroot  Disabled Login:  $username  ".$_SERVER['HTTP_USER_AGENT']);
            return false;
        }
//        $auths = array($auth);

    } else {
        // check if there's a deleted record (cheaply)
        if ($DB->get_field('user', 'id', array('username' => $username, 'deleted' => 1))) {
            $eventparams['other']['reason'] = 'Deleted Login';
            $event = \core\event\user_login_failed::create($eventparams);
            $event->trigger();
            error_log('[client '.$_SERVER['REMOTE_ADDR']."]  $CFG->wwwroot  Deleted Login:  $username  ".$_SERVER['HTTP_USER_AGENT']);
            return false;
        }

        $auths = $authsenabled;
        $user = new object();
        $user->id = 0;     // User does not exist
    }

    // hard code only saml module
    $auths = array('saml');
    foreach ($auths as $auth) {
        $authplugin = get_auth_plugin($auth);

        // on auth fail fall through to the next plugin
        error_log('auth_saml: '.$auth.' plugin');
        if (!$authplugin->user_login($username, $password)) {
            continue;
        }

        // successful authentication
        if ($user->id) {                          // User already exists in database
            if (empty($user->auth)) {             // For some reason auth isn't set yet
                $DB->set_field('user', 'auth', $auth, array('username' => $username));
                $user->auth = $auth;
            }
            if (empty($user->firstaccess)) { //prevent firstaccess from remaining 0 for manual account that never required confirmation

                $DB->set_field('user','firstaccess', $user->timemodified, array('id' => $user->id));
                $user->firstaccess = $user->timemodified;
            }

            // we don't want to upset the existing authentication schema for the user
//            update_internal_user_password($user, $password); // just in case salt or encoding were changed (magic quotes too one day)

            if ($authplugin->is_synchronised_with_external()) { // update user record from external DB
                $user = update_user_record($username);
            }
        } else {
            // if user not found, create him
            $user = create_user_record($username, $password, $auth);
        }

        $authplugin->sync_roles($user);

        foreach ($authsenabled as $hau) {
            $hauth = get_auth_plugin($hau);
            $hauth->user_authenticated_hook($user, $username, $password);
        }

        if (empty($user->id)) {
            return false;
        }

        if (!empty($user->suspended)) {
            // just in case some auth plugin suspended account
            $eventparams['other']['reason'] = 'Suspended Login';
            $event = \core\event\user_login_failed::create($eventparams);
            $event->trigger();
            error_log('[client '.getremoteaddr()."]  $CFG->wwwroot  Suspended Login:  $username  ".$_SERVER['HTTP_USER_AGENT']);
            return false;
        }

        return $user;
    }

    // failed if all the plugins have failed
    $eventparams['other']['reason'] = 'Failed Login';
    $event = \core\event\user_login_failed::create($eventparams);
    $event->trigger();
    if (debugging('', DEBUG_ALL)) {
        error_log('[client '.getremoteaddr()."]  $CFG->wwwroot  Failed Login:  $username  ".$_SERVER['HTTP_USER_AGENT']);
    }
    return false;
}


// Useful functions copied from Moodle lib/weblib.php - why? need to be able to
// run these without having bootstrapped Moodle

/**
 * Like {@link me()} but returns a full URL
 * @see me()
 * @param array $url (optional) the URL components.
 * @return string
 */
function auth_saml_qualified_me($url = []) {
    if (!empty($url['host'])) {
        $hostname = $url['host'];
    } else if (!empty($_SERVER['SERVER_NAME'])) {
        $hostname = $_SERVER['SERVER_NAME'];
    } else if (!empty($_ENV['SERVER_NAME'])) {
        $hostname = $_ENV['SERVER_NAME'];
    } else if (!empty($_SERVER['HTTP_HOST'])) {
        $hostname = $_SERVER['HTTP_HOST'];
    } else if (!empty($_ENV['HTTP_HOST'])) {
        $hostname = $_ENV['HTTP_HOST'];
    } else {
        notify('Warning: could not find the name of this server!');
        return false;
    }

    if (!empty($url['port'])) {
        $hostname .= ':'.$url['port'];
    } else if (!empty($_SERVER['SERVER_PORT'])) {
        if ($_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443) {
            $hostname .= ':'.$_SERVER['SERVER_PORT'];
        }
    }

    // TODO, this does not work in the situation described in MDL-11061, but
    // I don't know how to fix it. Possibly believe $CFG->wwwroot ahead of what
    // the server reports.
    if (isset($_SERVER['HTTPS'])) {
        $protocol = ($_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
    } else if (isset($_SERVER['SERVER_PORT'])) { # Apache2 does not export $_SERVER['HTTPS']
        $protocol = ($_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';
    } else {
        $protocol = 'http://';
    }

    $url_prefix = $protocol.$hostname;
    return $url_prefix;
}

/**
 * Returns the name of the current script, WITH the querystring portion.
 * this function is necessary because PHP_SELF and REQUEST_URI and SCRIPT_NAME
 * return different things depending on a lot of things like your OS, Web
 * server, and the way PHP is compiled (ie. as a CGI, module, ISAPI, etc.)
 * <b>NOTE:</b> This function returns false if the global variables needed are not set.
 *
 * @return string
 */
 function auth_saml_me() {

    if (!empty($_SERVER['REQUEST_URI'])) {
        return $_SERVER['REQUEST_URI'];

    } else if (!empty($_SERVER['PHP_SELF'])) {
        if (!empty($_SERVER['QUERY_STRING'])) {
            return $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'];
        }
        return $_SERVER['PHP_SELF'];

    } else if (!empty($_SERVER['SCRIPT_NAME'])) {
        if (!empty($_SERVER['QUERY_STRING'])) {
            return $_SERVER['SCRIPT_NAME'] .'?'. $_SERVER['QUERY_STRING'];
        }
        return $_SERVER['SCRIPT_NAME'];

    } else if (!empty($_SERVER['URL'])) {     // May help IIS (not well tested)
        if (!empty($_SERVER['QUERY_STRING'])) {
            return $_SERVER['URL'] .'?'. $_SERVER['QUERY_STRING'];
        }
        return $_SERVER['URL'];

    } else {
        notify('Warning: Could not find any of these web server variables: $REQUEST_URI, $PHP_SELF, $SCRIPT_NAME or $URL');
        return false;
    }
}

/**
 *  error log wrapper
 * @param string $msg
 */
function auth_saml_err($msg) {
    // check if we are debugging
    if (!constant('SAML_DEBUG')) {
        return;
    }
    $logid = '';

    // check if this method is executable
    if (class_exists('SimpleSAML_Logger') && in_array('getTrackId', get_class_methods('SimpleSAML_Logger'))) {
        $logid = '['.SimpleSAML_Logger::getTrackId().']';
    }
    error_log('auth/saml: '.$logid.' '.$msg);
}
