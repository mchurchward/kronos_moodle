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

/*
 * If more than 24 hours have elapsed since last sandbox update, return true.
 *
 * @return Boolean True if > 7 days since last sandbox update.
 */
function needs_update() {
    global $CFG;
    $currenttime = time();

    $timename = 'refreshtime';
    $timepath = $CFG->dataroot.'/manager/';
    $timefile = $timepath.$timename;

    $lastrefresh = file_get_contents($timefile);

    $date_diff = $currenttime - $lastrefresh;
    $day = 7* 24 * 60 * 60;

    if ($date_diff <= $day) {
        return false;
    } else {
        return true;
    }
}
