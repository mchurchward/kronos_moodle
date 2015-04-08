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
 * Kronos sandbox activity.
 *
 * @package    mod_kronossandvm
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2015 Remote Learner.net Inc http://www.remote-learner.net
 */

defined('MOODLE_INTERNAL') || die;

$ADMIN->add('modsettings', new admin_category('modkronossandvmfolder',
        get_string('pluginname', 'mod_kronossandvm'), $module->is_enabled() === false));

$settings = new admin_settingpage($section, get_string('settings', 'mod_kronossandvm'),
        'moodle/site:config', $module->is_enabled() === false);

if ($ADMIN->fulltree) {
    $name = get_string('requestsuserperday', 'mod_kronossandvm');
    $desc = get_string('requestsuserperdaydesc', 'mod_kronossandvm');
    $settingname = 'mod_kronossandvm_requestsuserperday';
    $settings->add(new admin_setting_configtext($settingname, $name, $desc, 1, PARAM_INT));

    $name = get_string('requestssolutionperday', 'mod_kronossandvm');
    $desc = get_string('requestssolutionperdaydesc', 'mod_kronossandvm');
    $settingname = 'mod_kronossandvm_requestssolutionperday';
    $settings->add(new admin_setting_configtext($settingname, $name, $desc, 50, PARAM_INT));

    $name = get_string('requestsconcurrentpercustomer', 'mod_kronossandvm');
    $desc = get_string('requestsconcurrentpercustomerdesc', 'mod_kronossandvm');
    $settingname = 'mod_kronossandvm_requestsconcurrentpercustomer';
    $settings->add(new admin_setting_configtext($settingname, $name, $desc, 12, PARAM_INT));

    $name = get_string('requesturl', 'mod_kronossandvm');
    $desc = get_string('requesturldesc', 'mod_kronossandvm');
    $settingname = 'mod_kronossandvm_requesturl';
    $default = 'http://edweb2.kronos.com/onsite/connectvm.aspx?sIP={instanceip}';
    $settings->add(new admin_setting_configtext($settingname, $name, $desc, $default, PARAM_TEXT));
    $url = new moodle_url($CFG->wwwroot.'/mod/kronossandvm/vmcourses.php');
    $link = html_writer::link($url, get_string('managetemplates', 'mod_kronossandvm'));
    $settings->add(new admin_setting_heading('mod_templatesconfig', get_string('managetemplatestitle', 'mod_kronossandvm'), $link));
}

$ADMIN->add('modkronossandvmfolder', $settings);
$ADMIN->add('modkronossandvmfolder', new admin_externalpage('virtualtemplates', get_string('managetemplates', 'mod_kronossandvm'),
        $CFG->wwwroot.'/mod/kronossandvm/vmcourses.php'));
$ADMIN->add('modkronossandvmfolder', new admin_externalpage('virtualtemplatescsv', get_string('managetemplatescsv', 'mod_kronossandvm'),
        $CFG->wwwroot.'/mod/kronossandvm/vmcourses_csv.php'));
$settings = null;
