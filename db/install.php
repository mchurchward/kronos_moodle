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
 * Post install function for RL Update Manager block
 *
 * @package    blocks
 * @subpackage block_rladmin
 * @author     Remoter-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (c) 2012 Remote Learner.net Inc http://www.remote-learner.net
 */

function xmldb_block_rlagent_install() {
    global $DB;

    $context = context_course::instance(SITEID);

    // Set up the new instance
    $block_instance_record = new stdclass;
    $block_instance_record->blockname = 'rlagent';
    $block_instance_record->pagetypepattern = 'site-index';
    $block_instance_record->parentcontextid = $context->id;
    $block_instance_record->showinsubcontexts = 0;
    // Force location
    $block_instance_record->defaultregion = 'side-post';
    $block_instance_record->defaultweight = 1;
    $DB->insert_record('block_instances', $block_instance_record);

}
