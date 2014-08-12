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
 * Remote Learner Update Manager - Moodle Addon Self Service action page
 *
 * @package   block_rlagent
 * @copyright 2014 Remote Learner Inc http://www.remote-learner.net
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$dir = dirname(__FILE__);
require_once($dir.'/../../../config.php');
require_once($dir.'/../lib.php');
require_once($dir.'/../lib/xmlrpc_dashboard_client.php');
require_once($dir.'/../lib/data_cache.php');

require_login(SITEID);
global $USER;

if (!has_capability('moodle/site:config', context_system::instance())) {
    print_error('siteadminonly');
}

// The order in the actions array determines the order of operations in the request file.
// Don't change it unless you know what you are dong.
$actions = array('remove', 'add', 'update');

$types = core_component::get_plugin_types();

$cache = new block_rlagent_data_cache();
$list = $cache->get_data('addonlist');

$messages = array();
$skipped = array();

// Check actions for bad requests.
$addons = array();
foreach ($actions as $action) {
    $skipped[$action] = array();
    $addons[$action] = array();
    $items = optional_param_array($action, array(), PARAM_ALPHANUMEXT);

    foreach ($items as $item) {
        list($type, $name) = explode('_', $item, 2);
        $skip = false;
        if (empty($name)) {
            $action = new stdClass();
            $action->action = $action;
            $action->subject = $item;
            $messages[] = get_string('error_unparseable_name', 'block_rlagent', $action);
            $skip = true;
        } else if (!array_key_exists($type, $types)) {
            $addon = new stdClass();
            $addon->name = $item;
            $addon->type = $type;
            $messages[] = get_string('error_unknown_addon_type', 'block_rlagent', $addon);
            $skip = true;
        } else if (!array_key_exists($item, $list['data'])) {
            $messages[] = get_string('error_unknown_addon', 'block_rlagent', $item);
            $skip = true;
        }
        if ($skip) {
            $skipped[$action][$item] = $item;
            continue;
        }
        $addons[$action][$item] = array('type' => $type, 'name' => $name);
    }
}

// Check for useless removals.
foreach ($addons['remove'] as $name => $remove) {
    $list = core_component::get_plugin_list($remove['type']);
    if (!array_key_exists($remove['name'], $list)) {
        unset($addons['remove'][$name]);
        $messages[] = get_string('error_remove_notinstalled', 'block_rlagent', $name);
        $skipped['remove'][$name] = $name;
    }
}

// Check for useless adds.
foreach ($addons['add'] as $name => $add) {
    $list = core_component::get_plugin_list($add['type']);
    $skip = false;
    if (array_key_exists($add['name'], $list) && !array_key_exists($name, $addons['remove'])) {
        unset($addons['add'][$name]);
        $messages[] = get_string('error_add_installed', 'block_rlagent', $name);
        $skipped['add'][$name] = $name;
    }
}

// Check for useless updates.
foreach ($addons['update'] as $name => $update) {
    $list = core_component::get_plugin_list($update['type']);
    $skip = false;
    if (array_key_exists($name, $addons['add'])) {
        $messages[] = get_string('error_update_added', 'block_rlagent', $name);
        $skip = true;
    } else if (array_key_exists($name, $addons['remove'])) {
        $messages[] = get_string('error_update_removed'. 'block_rlagent', $name);
        $skip = true;
    } else if (!array_key_exists($update['name'], $list)) {
        $messages[] = get_string('error_update_not_installed', 'block_rlagent', $name);
        $skip = true;
    }
    if ($skip) {
        unset($addons['update'][$name]);
        $skipped['update'][$name] = $name;
    }
}

$contents = array($CFG->dirroot);
foreach ($addons as $action => $items) {
    foreach ($items as $name => $addon) {
        $contents[] = "$action $name";
    }
    if (count($skipped[$action]) > 0) {
        $messages[] = "Skipping $action for the following addons: ".implode(', ', $skipped[$action]);
    }
}

if (count($contents) > 1) {
    $errors = block_rlagent_write_incron_commands($contents, 'addon_', $CFG->dataroot.'/manager/addons/commands');
    $messages = array_merge($messages, $errors);
}

$return = json_encode($messages);
print($return);