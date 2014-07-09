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
 * Accessory functions for RL Agent block.
 *
 * @package   block_rlagent
 * @copyright 2014 Amy Groshek for Remote-Learner.net
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Get data from the cache (fetching it if it's missing)
 *
 * @param string $type The type of data to fetch
 * @return mixed The data from the cache
 */
function block_rlagent_get_data($type) {
    static $client = null;
    static $cache = null;
    $types = array('addonlist' => 'get_addon_data', 'grouplist' => 'get_group_data');

    if (!array_key_exists($type, $types)) {
        return null;
    }

    if ($cache == null) {
        $cache = cache::make('block_rlagent', 'addondata');
    }
    $data = $cache->get($type);

    if ($data === false) {
        $method = $types[$type];

        if ($client == null) {
            $client = new block_rlagent_xmlrpc_dashboard_client();
        }
        $data = $client->$method();
        if ($data['result'] == 'OK') {
            $cache->set($type, $data);
        }
    }

    return $data;
}
