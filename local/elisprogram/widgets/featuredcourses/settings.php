<?php
/**
 * ELIS(TM): Enterprise Learning Intelligence Suite
 * Copyright (C) 2008-2014 Remote-Learner.net Inc (http://www.remote-learner.net)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    eliswidget_featuredcourses
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2015 Remote-Learner.net Inc (http://www.remote-learner.net)
 * @author     James McQuillan <james.mcquillan@remote-learner.net>
 * @author     Akinsaya Delamarre <adelamarre@remote-learner.net>
 */

defined('MOODLE_INTERNAL') || die;

global $USER;

if ($ADMIN->fulltree) {
    // General settings
    $generalheader = get_string('setting_general_hdr', 'eliswidget_featuredcourses');
    $settings->add(new \admin_setting_heading('generalhdr', $generalheader, ''));

    // Add a single drop down for selecting the Course Description custom field that denotes a featured course.
    $fields = field::get_for_context_level('course');
    $customfields = array();

    if ($fields->valid() === true) {
        foreach ($fields as $field) {
            if ('bool' == $field->datatype) {
                $customfields[$field->shortname] = $field->name;
            }
        }
    }

    // If the custom fields array is empty add a 'none' value.
    if (empty($customfields)) {
        $customfields['cf_no_field_selected'] = get_string('no_field_selected', 'eliswidget_featuredcourses');
    }

    $name = 'eliswidget_featuredcourses/featuredcoursefield';
    $visiblename = get_string('setting_featuredcoursefield', 'eliswidget_featuredcourses');
    $description = get_string('setting_featuredcoursefield_desc', 'eliswidget_featuredcourses');
    $settings->add(new \admin_setting_configselect($name, $visiblename, $description, null, $customfields));

    // Progress bar.
    $progressbarheader = get_string('setting_progressbar_heading', 'eliswidget_featuredcourses');
    $progressbarheaderdesc = get_string('setting_progressbar_heading_description', 'eliswidget_featuredcourses');
    $settings->add(new \admin_setting_heading('progressbar', $progressbarheader, $progressbarheaderdesc));

    $progressbarenabled = [
        'name' => 'eliswidget_featuredcourses/progressbarenabled',
        'visiblename' => get_string('setting_progressbar_enabled', 'eliswidget_featuredcourses'),
        'description' => get_string('setting_progressbar_enableddescription', 'eliswidget_featuredcourses'),
        'defaultsetting' => 1
    ];
    $settings->add(new \admin_setting_configcheckbox($progressbarenabled['name'], $progressbarenabled['visiblename'],
            $progressbarenabled['description'], $progressbarenabled['defaultsetting']));

    $defaultcolors = ['#A70505', '#D5D100', '#009029'];
    for ($i = 1; $i <= 3; $i++) {
        $progressbarcolor = [
            'name' => 'eliswidget_featuredcourses/progressbarcolor'.$i,
            'visiblename' => get_string('setting_progressbar_color'.$i, 'eliswidget_featuredcourses'),
            'description' => get_string('setting_progressbar_color'.$i.'description', 'eliswidget_featuredcourses'),
            'defaultsetting' => $defaultcolors[$i-1]
        ];
        $settings->add(new \admin_setting_configcolourpicker($progressbarcolor['name'], $progressbarcolor['visiblename'],
                $progressbarcolor['description'], $progressbarcolor['defaultsetting']));
    }

    // Enabled fields for each level.
    $enabledfieldsheader = get_string('setting_enabledfields_heading', 'eliswidget_featuredcourses');
    $enabledfieldsheaderdesc = get_string('setting_enabledfields_heading_description', 'eliswidget_featuredcourses');
    $settings->add(new \admin_setting_heading('enabledfields', $enabledfieldsheader, $enabledfieldsheaderdesc));

    $fieldlevels = [
        'courseset' => [
            'displayname' => get_string('courseset', 'local_elisprogram'),
            'fields' => [
                'idnumber' => get_string('courseset_idnumber', 'local_elisprogram'),
                'name' => get_string('courseset_name', 'local_elisprogram'),
                'description' => get_string('description', 'local_elisprogram'),
            ],
            'defaultfields' => ['idnumber', 'name', 'description'],
        ],
        'course' => [
            'displayname' => get_string('course', 'local_elisprogram'),
            'fields' => [
                'name' => get_string('course_name', 'local_elisprogram'),
                'code' => get_string('course_code', 'local_elisprogram'),
                'idnumber' => get_string('course_idnumber', 'local_elisprogram'),
                'description' => get_string('course_syllabus', 'local_elisprogram'),
                'credits' => get_string('credits', 'local_elisprogram'),
                'cost' => get_string('cost', 'local_elisprogram'),
                'version' => get_string('course_version', 'local_elisprogram'),
            ],
            'defaultfields' => ['name', 'code', 'idnumber', 'description', 'credits'],
        ],
        'class' => [
            'displayname' => get_string('class', 'local_elisprogram'),
            'fields' => [
                'idnumber' => get_string('class_idnumber', 'local_elisprogram'),
                'startdate' => get_string('class_startdate', 'local_elisprogram'),
                'enddate' => get_string('class_enddate', 'local_elisprogram'),
                'starttime' => get_string('class_starttime', 'local_elisprogram'),
                'endtime' => get_string('class_endtime', 'local_elisprogram'),
            ],
            'defaultfields' => ['idnumber', 'startdate', 'enddate', 'starttime', 'endtime'],
        ],
    ];
    foreach ($fieldlevels as $ctxlvl => $info) {
        // Get custom fields and merge with base fields.
        $fields = field::get_for_context_level($ctxlvl);
        if ($fields->valid() === true) {
            foreach ($fields as $field) {
                $info['fields']['cf_'.$field->shortname] = $field->name;
            }
        }

        $enabledfields = [
            'name' => 'eliswidget_featuredcourses/'.$ctxlvl.'enabledfields',
            'visiblename' => get_string('setting_enabledfields', 'eliswidget_featuredcourses', $info['displayname']),
            'description' => get_string('setting_enabledfields_description', 'eliswidget_featuredcourses', $info['displayname']),
            'defaultsetting' => $info['defaultfields'],
            'choices' => $info['fields'],
        ];
        $settings->add(new \admin_setting_configmultiselect($enabledfields['name'], $enabledfields['visiblename'],
                $enabledfields['description'], $enabledfields['defaultsetting'], $enabledfields['choices']));
    }
}