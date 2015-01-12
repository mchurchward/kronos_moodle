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

require_once(__DIR__.'/../../../../eliscore/test_config.php');
global $CFG;

require_once($CFG->dirroot.'/local/elisprogram/lib/setup.php');

/**
 * Test \eliswidget_featuredcourses\datatable\programcourse.
 * @group eliswidget_featuredcourses
 */
class featuredcourses_datatable_programcourse_testcase extends \elis_database_test {
    /**
     * Get ELIS data generator.
     *
     * @return \elis_program_datagenerator An ELIS data generator instance.
     */
    protected function getelisdatagenerator() {
        global $DB, $CFG;
        require_once(\elispm::file('tests/other/datagenerator.php'));
        return new \elis_program_datagenerator($DB);
    }

    /**
     * Create a custom field category.
     * @param int $context The context level constant to create the category for (ex. CONTEXT_ELIS_USER)
     * @return field_category The created field category.
     */
    protected function create_field_category($context) {
        $data = new stdClass;
        $data->name = \local_eliscore\context\helper::get_class_for_level($context).' Test';

        $category = new field_category($data);
        $category->save();

        $categorycontext = new field_category_contextlevel();
        $categorycontext->categoryid = $category->id;
        $categorycontext->contextlevel = $context;
        $categorycontext->save();

        return $category;
    }

    /**
     * Create an ELIS custom field.
     * @param field_category &$cat The category to create the field in.
     * @param int $context The context level constant to create the category for (ex. CONTEXT_ELIS_USER)
     * @return field The created field.
     */
    protected function create_field(field_category &$cat, $context) {
        $data = new stdClass;
        $data->shortname = 'phpu_featured_course';
        $data->name = 'Test Field featured course';
        $data->categoryid = $cat->id;
        $data->description = 'Test Field';
        $data->datatype = 'bool';
        $data->forceunique = '0';
        $data->mform_showadvanced_last = 0;
        $data->multivalued = '0';
        $data->defaultdata = '';
        $data->manual_field_enabled = '1';
        $data->manual_field_edit_capability = '';
        $data->manual_field_view_capability = '';
        $data->manual_field_control = 'text';
        $data->manual_field_options_source = '';
        $data->manual_field_options = '';
        $data->manual_field_columns = 30;
        $data->manual_field_rows = 10;
        $data->manual_field_maxlength = 2048;

        $field = new field($data);
        $field->save();

        $fieldcontext = new field_contextlevel();
        $fieldcontext->fieldid      = $field->id;
        $fieldcontext->contextlevel = $context;
        $fieldcontext->save();

        $owner = new field_owner();
        $owner->fieldid = $field->id;
        $owner->plugin = 'manual';
        $owner->params = serialize(array(
            'required' => false,
            'edit_capability' => '',
            'view_capability' => '',
            'control' => 'text',
            'columns' => 30,
            'rows' => 10,
            'maxlength' => 2048,
            'startyear' => '1970',
            'stopyear' => '2038',
            'inctime' => '0'
        ));
        $owner->save();
        return $field;
    }

    /**
     * Test get_search_results function.
     */
    public function test_get_search_results() {
        global $DB;

        $datagen = $this->getelisdatagenerator();

        // Create entities.
        $mockuser = $datagen->create_user();
        $pgm = $datagen->create_program();
        $pmcourse1 = $datagen->create_course();
        $pmclass1 = $datagen->create_pmclass(['courseid' => $pmcourse1->id]);
        $pmcourse2 = $datagen->create_course();
        $pmclass2 = $datagen->create_pmclass(['courseid' => $pmcourse2->id]);

        // Create category and a field.
        $fldcat = $this->create_field_category(CONTEXT_ELIS_COURSE);
        $fld = $this->create_field($fldcat, CONTEXT_ELIS_COURSE);
        // Set featured course setting.
        set_config('featuredcoursefield', $fld->shortname, 'eliswidget_featuredcourses');
        // Set the featured courses custom field for the course.
        $ctxclass = \local_eliscore\context\helper::get_class_for_level(CONTEXT_ELIS_COURSE);
        $context = $ctxclass::instance($pmcourse1->id);
        $property = "field_{$fld->shortname}";
        $course = new course(array('id' => $pmcourse1->id));
        $course->$property = 1;
        $course->save();

        // Perform assignments.
        $curcourse = $datagen->assign_course_to_program($pmcourse1->id, $pgm->id);
        $curstu = $datagen->assign_user_to_program($mockuser->id, $pgm->id);
        $stu = $datagen->assign_user_to_class($mockuser->id, $pmclass1->id);

        // Run method.
        $datatable = new \eliswidget_featuredcourses\datatable\programcourse($DB, 'http://example.com');
        $datatable->set_programid($pgm->id);
        list($courses, $totalresults) = $datatable->get_search_results();

        // Convert recordset to array.
        $coursesar = [];
        foreach ($courses as $course) {
            $coursesar[$course->id] = $course;
        }

        // Validate.
        $this->assertTrue((isset($coursesar[$pmcourse1->id])));
        $this->assertEquals(1, count($coursesar));
    }
}