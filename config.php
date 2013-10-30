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
 * Plugin configuration file
 *
 * @package    auth_rladmin
 * @copyright  2012 Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$config           = new stdClass;
$config->idp      = new moodle_url('http://localhost/rlmoodle.plain.22/local/rladmin_idp/index.php');
$config->username = 'rladmin';

$config->separateusers = true;
// if using separate users, set the prefix for the username
// $config->usernameprefix = 'rl_';

// Use a proxy:
// $config->proxy = 'http://proxy.example.com/';
// $config->proxyuserpwd = '[username]:[password]'; // don't blame me; this is how curl wants it formatted

// Verify the SSL using this CA file (may contain multipl CA certs)
// $config->cainfo = '/path/to/ca/file';
