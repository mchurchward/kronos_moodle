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
 * Version information
 *
 * @package    auth_rladmin
 * @copyright  2012 Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../config.php');
require($CFG->dirroot.'/auth/rladmin/config.php');

$PAGE->set_context(get_context_instance(CONTEXT_SYSTEM));

$token = optional_param('token', null, PARAM_ALPHANUM);
$id    = optional_param('id', null, PARAM_ALPHANUM);

// force enable the rladmin plugin
if (!in_array('rladmin', explode(',',$CFG->auth))) {
    if (array_key_exists('auth', $CFG->config_php_settings)) {
        // the setting is set in config.php, so cannot set
        print_error('authconfigoverride', 'auth_rladmin');
    }
    if (empty($CFG->auth)) {
        set_config('auth', 'rladmin');
    } else {
        set_config('auth', "rladmin,{$CFG->auth}");
    }
}

$authplugin = get_auth_plugin('rladmin');

if (empty($token) || empty($id)) {
    $authplugin->user_login($config->username, false);
    print_error('internalerror', 'auth_rladmin');
}

$authplugin->_confirm_token($token);

$params = array(
    'action' => 'whois',
    'token'  => $token,
    'id'     => $id,
    'return' => $CFG->wwwroot.'/auth/rladmin/login.php'
);
$confirmurl = new moodle_url($config->idp, $params);

// Start a new cURL connextion to valdidate the auth credentials
$ch = curl_init($confirmurl->out(false));
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Moodle');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

if (isset($config->proxy)) {
    curl_setopt($ch, CURLOPT_PROXY, $config->proxy);
    if (isset($config->proxyuserpwd)) {
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $config->proxyuserpwd);
    }
}

if (isset($config->cainfo)) {
    curl_setopt($ch, CURLOPT_CAINFO, $config->cainfo);
}

$result    = trim(curl_exec($ch));
$curlerrno = curl_errno($ch);
$info      = curl_getinfo($ch);
curl_close($ch);

if ($curlerrno != 0) {
    print_error('curl_error', 'auth_rladmin', '', s(curl_error($ch)));
}

if (!empty($info['http_code']) and ($info['http_code'] != 200)) {
    $a = new stdClass;
    $a->http_code = $info['http_code'];
    $a->error     = $result;
    print_error('http_error', 'auth_rladmin', '', $a);
}

// make sure the returned value is valid (should just be a JSON-encoded user object)
$remoteuser = json_decode($result);
if (!$remoteuser || !is_object($remoteuser) || empty($remoteuser->username) || preg_match('/[^a-zA-Z.0-9]/', $remoteuser->username)) {
    $a = new stdClass;
    $a->error = s($result);
    print_error('invalidresult', 'auth_rladmin', '', $a);
}

if ($config->separateusers) {
    $username = (isset($config->usernameprefix) ? $config->usernameprefix : '').$remoteuser->username;
} else {
    $username = $config->username;
}

// make sure the user exists
$user = $DB->get_record('user', array('username' => $username, 'mnethostid' => $CFG->mnet_localhost_id));
if ($user) {
    // make sure the rladmin user uses the rladmin plugin
    if ($user->auth !== 'rladmin') {
        $DB->set_field('user','auth', 'rladmin', array('id' => $user->id));
    }
    $user = update_user_record($username);
} else {
    $user = create_user_record($username, '', 'rladmin');
}

if (empty($user->firstaccess)) {
    $DB->set_field('user','firstaccess', $user->timemodified, array('id' => $user->id));
    $user->firstaccess = $user->timemodified;
}

// log in as rladmin
add_to_log(SITEID, 'user', 'login', 'auth/rladmin/login.php', $remoteuser->username, 0, $user->id);
complete_user_login($user);
$authplugin->sync_roles($user);

// go somewhere
if (isset($SESSION->wantsurl)) {
    $urltogo = $SESSION->wantsurl;
} else {
    $urltogo = $CFG->wwwroot.'/';
}
unset($SESSION->wantsurl);
redirect($urltogo);
