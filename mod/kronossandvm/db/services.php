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
 * Kronos virtual machine request web service.
 *
 * @package    mod_kronossandvm
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2015 Remote Learner.net Inc http://www.remote-learner.net
 */

$functions = array (
    'mod_kronossandvm_external_vm_requests' => array (
        'classname' => 'mod_kronossandvm_external',
        'methodname' => 'vm_requests',
        'classpath' => 'mod/kronossandvm/externallib.php',
        'description' => 'Return list of virtual machine requests.',
        'type' => 'read'
    ),
    'mod_kronossandvm_external_update_vm_request' => array (
        'classname' => 'mod_kronossandvm_external',
        'methodname' => 'update_vm_request',
        'classpath' => 'mod/kronossandvm/externallib.php',
        'description' => 'Update a virtual machine request.',
        'type' => 'read'
    ),
    'mod_kronossandvm_external_delete_vm_request' => array (
        'classname' => 'mod_kronossandvm_external',
        'methodname' => 'delete_vm_request',
        'classpath' => 'mod/kronossandvm/externallib.php',
        'description' => 'Delete virtual machine request.',
        'type' => 'read'
    ),
    'mod_kronossandvm_external_get_vm_request' => array (
        'classname' => 'mod_kronossandvm_external',
        'methodname' => 'get_vm_request',
        'classpath' => 'mod/kronossandvm/externallib.php',
        'description' => 'Get virtual machine request.',
        'type' => 'read'
    ),
);

$services = array (
    'Kronos virtual machine requests' => array (
        'functions' => array (
            'mod_kronossandvm_external_vm_requests',
            'mod_kronossandvm_external_update_vm_request',
            'mod_kronossandvm_external_delete_vm_request',
            'mod_kronossandvm_external_get_vm_request'
            ),
        'restrictedusers' => 0,
        'enabled' => 1
    )
);
