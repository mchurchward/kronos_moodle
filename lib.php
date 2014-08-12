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
 * Figure out the proper branch number
 *
 * @return int The branch number
 */
function block_rlagent_get_branch_number() {
    global $CFG;

    // Figure out the branch number.
    $matches = array();
    preg_match('/(\d+)\.(\d+)./', $CFG->release, $matches);
    $branch = $matches[1].$matches[2];

    return $branch;
}

/**
 * Get an ini value from the global.ini file
 *
 * Needs vfsStream for unit testing.
 *
 * @param string $field The field to return
 * @param string $segment The segment to return the field from
 * @return string The field value
 */
function block_rlagent_get_ini_value($field, $segment) {
    $inifile = '/mnt/data/config/global.ini';
    if (file_exists($inifile) && is_readible($inifile)) {
        $ini = parse_ini_file($inifile, true);
        if ((false !== $ini) && isset($ini[$segment][$field])) {
            return $ini[$segment][$field];
        }
    }
    return false;
}

/*
 * If more than 24 hours have elapsed since last sandbox update, return true.
 *
 * @return Boolean True if > 7 days since last sandbox update.
 */
function block_rlagent_needs_update() {
    global $CFG;

    // Check if this is a sandbox site
    $sandbox = false;
    $sitename = basename($CFG->dirroot);
    if (preg_match('/^moodle_sand([0-9]*|)$/', $sitename)) {
        $sandbox = true;
    } else if (block_rlagent_get_ini_value('refresh_source', $sitename) !== false) {
        $sandbox = true;
    }

    // If it's a sandbox check how long since the last refresh.
    if ($sandbox) {
        $lastrefresh = 0;
        $path = $CFG->dataroot.'/manager/refreshtime';
        if (file_exists($path) && is_readable($path)) {
            $lastrefresh = intval(file_get_contents($path));
        }

        // Check if the last refresh was more than 7 days ago (7 x 24 x 60 x 60 = 604800).
        if ((time() - $lastrefresh) > 604800) {
            return true;
        }
    }
    return false;
}

/**
 * Write commands to an incron file.
 *
 * Needs vfsStream for unit testing.
 *
 * @param array $commands The commands to write to the command file
 * @param string $prefix The filename prefix to use for the command file
 * @param string $path The directory to write the command file to.
 */
function block_rlagent_write_incron_commands($commands, $prefix, $path) {
    $messages = array();

    if (!file_exists($path) && !mkdir($path, 0770, true)) {
        $messages[] = get_string('error_unable_to_create_dispatch_dir', 'block_rlagent', $path);
    } else {
        // Write to a tempfile to make requests atomic.
        $tmpfile = tempnam(sys_get_temp_dir(), $prefix);
        $file = $path.'/'.basename($tmpfile);
        if (file_put_contents($tmpfile, implode("\n", $commands))) {
            if (copy($tmpfile, $file)) {
                if (!unlink($tmpfile)) {
                    $messages[] = get_string('error_unable_to_delete_temp_command_file', 'block_rlagent');
                }
            } else {
                $messages[] = get_string('error_unable_to_copy_command', 'block_rlagent');
            }
        } else {
            $messages[] = get_string('error_unable_to_write_temp_command_file', 'block_rlagent');
        }
    }

    return $messages;
}
