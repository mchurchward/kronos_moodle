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
 * Settings for the HTML block
 *
 * @package    blocks_importqueue
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2008-2015 Remote-Learner.net Inc (http://www.remote-learner.net)
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/local/datahub/importplugins/version1/lib.php');
require_once($CFG->dirroot.'/auth/kronosportal/lib.php');

if ($ADMIN->fulltree) {
    // Create list of fields which can be used in the import process.
    $fieldmap = rlipimport_version1_get_mapping('user');
    // Standard user fields.
    $fields = array(
        'lastnamephonetic' => get_user_field_name('lastnamephonetic'),
        'firstnamephonetic' => get_user_field_name('firstnamephonetic'),
        'middlename' => get_user_field_name('middlename'),
        'alternatename' => get_user_field_name('alternatename'),
        'institution' => get_user_field_name('institution'),
        'department' => get_user_field_name('department'),
        'description' => get_user_field_name('description'),
        'phone1' => get_user_field_name('phone1'),
        'phone2' => get_user_field_name('phone2'),
        'address' => get_user_field_name('address'),
        'lang' => get_string('language'),
        'theme' => get_user_field_name('theme'),
        'timezone' => get_user_field_name('timezone'),
        'url' => get_user_field_name('url'),
        'icq' => get_user_field_name('icq'),
        'skype' => get_user_field_name('skype'),
        'aim' => get_user_field_name('aim'),
        'yahoo' => get_user_field_name('yahoo'),
        'msn' => get_user_field_name('msn'),
    );

    // Custom fields.
    $customfields = $DB->get_records('user_info_field');
    $options = array('context' => context_system::instance());
    foreach ($customfields as $field) {
        $fields['profile_field_'.$field->shortname] = format_string($field->name, true, $options);
    }
    $map = array();
    $excludefields = 'action,auth,username,idnumber,context,user_idnumber,user_username,user_email,inactive,password,';
    $excludefields .= 'firstname,lastname,email,city,country';
    $excludecolumns = preg_split('/,/', $excludefields);
    $auth = get_auth_plugin('kronosportal');
    if ($auth->is_configuration_valid()) {
        $excludecolumns[] = 'profile_field_'.kronosportal_get_solutionfield();
    }
    foreach ($fields as $src => $name) {
        if (!in_array($src, $excludecolumns)) {
            if (!empty($fieldmap[$src])) {
                $map[] = html_writer::tag('li', $fieldmap[$src].' - '.$name);
            } else {
                $map[] = html_writer::tag('li', $src.' - '.$name);
            }
        }
    }

    $settings->add(new admin_setting_heading('block_importqueue/create', get_string('createsettingsheading', 'block_importqueue'), ''));

    $settings->add(new admin_setting_configtext('block_importqueue/importcolumns', get_string('importcolumns', 'block_importqueue'),
                       get_string('importcolumnsdesc', 'block_importqueue'), ''));

    $settings->add(new admin_setting_configtext('block_importqueue/allowedempty', get_string('allowedempty', 'block_importqueue'),
                       get_string('allowedemptydesc', 'block_importqueue'), ''));

    $settings->add(new admin_setting_heading('block_importqueue/updatefields', get_string('updatesettingsheading', 'block_importqueue'), ''));

    $settings->add(new admin_setting_configtext('block_importqueue/updatecolumns', get_string('updatecolumns', 'block_importqueue'),
                       get_string('updatecolumnsdesc', 'block_importqueue'), ''));

    $settings->add(new admin_setting_configtext('block_importqueue/updateallowedempty', get_string('updateallowedempty', 'block_importqueue'),
                       get_string('updateallowedemptydesc', 'block_importqueue'), ''));

    $settings->add(new admin_setting_heading('block_importqueue/deletefields', get_string('deletesettingsheading', 'block_importqueue'), ''));

    $settings->add(new admin_setting_configtext('block_importqueue/deletesolutionid', get_string('deletesolutionid', 'block_importqueue'),
                       get_string('deletesolutioniddesc', 'block_importqueue'), 'deleted'));

    $settings->add(new admin_setting_heading('block_importqueue/importqueuefields', get_string('csvfieldsheading', 'block_importqueue'),
                       get_string('csvfieldsdesc', 'block_importqueue', join('', $map))));

    $settings->add(new admin_setting_heading('block_importqueue/other', get_string('other'), ''));

    $settings->add(new admin_setting_configcheckbox('block_importqueue/learningpath_autocomplete', get_string('learningpath_autocomplete', 'block_importqueue'),
                       get_string('learningpath_autocompletedesc', 'block_importqueue'), 0));

    $settings->add(new admin_setting_configcheckbox('block_importqueue/learningpath_select', get_string('learningpath_select', 'block_importqueue'),
                       get_string('learningpath_selectdesc', 'block_importqueue'), 0));

    $settings->add(new admin_setting_configtextarea('block_importqueue/menu', get_string('menu', 'block_importqueue'),
                       get_string('menudesc', 'block_importqueue'), get_string('newimportqueuecontent', 'block_importqueue', $CFG)));
}
