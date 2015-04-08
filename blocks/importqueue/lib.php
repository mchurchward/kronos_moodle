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
 * Import queue block.
 *
 * @package    block_importqueue
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2015 Remote Learner.net Inc http://www.remote-learner.net
 */

/**
 * Detimine if current user can access import queue.
 *
 * @return boolean true for is allowed.
 */
function importqueue_isallowed() {
    global $USER;
    $allowed = false;
    // Allow access to system adminstrators, training managers or manager with sitewide access.
    $context = context_system::instance();
    if (is_siteadmin() || has_capability('block/importqueue:sitewide', $context, $USER->id)) {
        $allowed = true;
    }
    if (has_capability('block/importqueue:upload', $context, $USER->id)) {
        $allowed = true;
    }
    return $allowed;
}
