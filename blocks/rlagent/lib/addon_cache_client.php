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
 * Remote Learner Update Manager - Local addon cache client
 *
 * @package    block_rlagent
 * @copyright  2014 Remote Learner Inc http://www.remote-learner.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_rlagent_addon_cache_client {
    /** @var string The directory where addon reference copies are stored. */
    protected $referencedir = '/mnt/cluster/git/moodle_addons';

    /**
     * Get addon data from the cache
     *
     * Note: Not unit testable because of exec calls
     *
     * @param string $addon The name of the addon to get data from
     */
    public function get_addon_data($addon) {
        $branchnum = block_rlagent_get_branch_number();
        $branch = "MOODLE_{$branchnum}_STABLE";
        $path = "{$this->referencedir}/moodle-{$addon}";

        // Moodle would normallly define these classes before reading the version file.
        $plugin = new stdClass();
        $module = new stdClass();

        // Check if the reference directory exists.
        if (file_exists($path)) {
            $current = getcwd();

            if (chdir($path)) {
                $bare = true;
                exec("git rev-parse --is-bare-repository | grep false", $output, $bare);
                if (!$bare) {
                    // If it's not-bare we need to look at the version file from the remote repository because the local
                    // version could be modified or badly out-of-date.
                    $origin = 'origin/';
                }
                $notexists = true;
                // Check if the reference repository has a version file (tinymce plugins don't).
                exec("git rev-parse --verify {$origin}{$branch}", $output, $notexists);
                if (!$notexists) {
                    // We aren't allowed to modify the reference version so we need to get the file contents
                    // and evaluate them, since the repository might not be on the right branch or commit.
                    $file = array();
                    exec("git cat-file blob {$origin}{$branch}:version.php", $file);
                    $file = str_ireplace('<?php', '', implode("\n", $file));
                    eval($file);
                    chdir($current);
                }
            }
        }

        // Most plugins.
        if (!empty($plugin->version)) {
            return $plugin;
        }
        // Moodle modules.
        if (!empty($module->version)) {
            return $module;
        }
        // Nothing found?
        return new stdClass();
    }
}