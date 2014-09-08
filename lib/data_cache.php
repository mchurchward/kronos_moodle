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
$dir = dirname(__FILE__);
require_once($dir.'/addon_cache_client.php');
require_once($dir.'/xmlrpc_dashboard_client.php');

/**
 * Remote Learner Update Manager - Data Cache class
 *
 * @package    block_rlagent
 * @copyright  2014 Remote Learner Inc http://www.remote-learner.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_rlagent_data_cache {
    /** @var object The Moodle cache object */
    protected $cache = null;

    /** @var object The addons data */
    protected $data = array();

    /** @var object The RL addon cache client object */
    protected $rlcache = null;

    /** @var array An array of type => function mappings for data retrieval */
    protected $types = array('addonlist' => 'get_addon_data', 'grouplist' => 'get_group_data');

    /** @var object The XMLRPC client object */
    protected $xmlrpc = null;

    /**
     * Constructor
     */
    public function __construct() {
        $this->cache = cache::make('block_rlagent', 'addondata');
        $this->rlcache = new block_rlagent_addon_cache_client();
        $this->xmlrpc = new block_rlagent_xmlrpc_dashboard_client();
    }

    /**
     * Get data from the cache (fetching it if it's missing)
     *
     * The returned array is an array with timestamp and data entries.
     *
     * @param string $type The type of data to fetch
     * @return array|bool The data from the cache.  False on failure to get data.
     */
    public function get_data($type) {

        if (!array_key_exists($type, $this->types)) {
            return false;
        }

        $this->data = $this->cache->get($type);

        // Fetch new data if we don't have it or it's more than 23 hours old.
        if (($this->data === false) || ($this->data['timestamp'] < (time() - 82800))) {
            $this->data = array('result' => 'fail');
            $this->get_xmlrpc_data($type);
            $this->get_moodle_data($type);
            $this->get_rlcache_data($type);

            if ($this->data['result'] == 'OK') {
                $this->cache->set($type, $this->data);
            }
        }

        return $this->data;
    }

    /**
     * Get data from Moodle
     *
     * We need $this->data to pre-populated with target addons.
     *
     * @param string $type The type of data to fetch
     */
    protected function get_moodle_data($type) {
        global $DB, $USER;

        switch ($type) {
            case 'addonlist':
                $list = core_plugin_manager::instance()->get_plugins();
                foreach ($this->data['data'] as $key => $addon) {
                    $type = $addon['type'];
                    $name = $addon['name'];
                    $addon['installed'] = false;
                    $addon['versiondisk'] = 0;
                    $addon['versiondb'] = 0;
                    $addon['dependencies'] = array();
                    $addon['release'] = '';
                    $addon['myrating'] = 0;
                    if (array_key_exists($type, $list) && array_key_exists($name, $list[$type])) {
                        $moodle = $list[$type][$name];
                        $addon['installed'] = true;
                        $addon['versiondisk'] = $moodle->versiondisk;
                        $addon['versiondb'] = $moodle->versiondb;
                        if (!empty($moodle->dependencies)) {
                            $addon['dependencies'] = $moodle->dependencies;
                        }
                        if (!empty($moodle->release)) {
                            $addon['release'] = $moodle->release;
                        }
                    }
                    $this->data['data'][$key] = $addon;
                }
                $ratings = $DB->get_records('block_rlagent_rating', array ('userid' => $USER->id));
                foreach ($ratings as $rating) {
                    if (array_key_exists($rating->plugin, $this->data['data'])) {
                        $this->data['data'][$rating->plugin]['myrating'] = $rating->rating;
                    }
                }
                break;
            default:
                break;
        }
    }

    /**
     * Get data from the rlcache client
     *
     * We need $this->data to pre-populated with target addons.
     *
     * @param string $type The type of data to fetch
     */
    protected function get_rlcache_data($type) {
        switch ($type) {
            case 'addonlist':
                $software = block_rlagent_get_ini_value('deliverable_software', 'deliverables');
                $elis = false;
                if (strtolower($software) == 'elis') {
                    $elis = true;
                }

                $pay = array();
                // Turtles all the way down.
                $cache = new block_rlagent_data_cache();
                $groups = $cache->get_data('grouplist');
                unset($cache);
                if (is_array($groups) && is_array($groups['data']) && is_array($groups['data']['elis'])) {
                    $pay = array_fill_keys($groups['data']['elis']['plugins'], 1);
                }

                foreach ($this->data['data'] as $name => $addon) {
                    $cached = $this->rlcache->get_addon_data($name);
                    $addon['cached'] = false;
                    $addon['upgradeable'] = false;
                    $addon['cache'] = array();
                    $addon['paid'] = false;
                    if (!empty($cached->version)) {
                        $addon['cached'] = true;
                        $addon['cache']['dependencies'] = array();
                        if (!empty($cached->dependencies)) {
                            $addon['cache']['dependencies'] = $cached->dependencies;
                        }
                        $addon['cache']['version'] = $cached->version;
                        if (($addon['installed'] == true) && ($addon['versiondisk'] < $cached->version)) {
                            $addon['upgradeable'] = true;
                        }
                    }
                    if (array_key_exists($name, $pay)) {
                        $addon['paid'] = true;
                    }
                    $this->data['data'][$name] = $addon;
                }
                break;
            default:
                break;
        }
    }

    /**
     * Get data from the xmlrpc client
     *
     * We will overwrite $this->data with data from the Dashboard
     *
     * @param string $type The type of data to fetch
     */
    protected function get_xmlrpc_data($type) {
        $method = $this->types[$type];
        $response = $this->xmlrpc->$method();
        if (is_array($response)) {
            if (array_key_exists(0, $response) && array_key_exists('result', $response[0])) {
                // Future improved format
                $this->data = ksort($response[0]);

            } else {
                // Current broken format
                $this->data = array('result' => 'OK', 'data' => $response);
            }
        }
        $this->data['timestamp'] = time();
    }

    /**
     * Update the cache data
     *
     * @param string $type The type of data to set
     * @param array $data The data to set in the cache
     */
    public function update_data($type, $data) {
        $this->cache->set($type, $data);
    }
}
