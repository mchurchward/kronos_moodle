<?php
/**
 * ELIS(TM): Enterprise Learning Intelligence Suite
 * Copyright (C) 2008-2013 Remote-Learner.net Inc (http://www.remote-learner.net)
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
 * @package    local_elisprogram
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2008-2013 Remote Learner.net Inc http://www.remote-learner.net
 *
 */

require_once(dirname(__FILE__).'/../../eliscore/test_config.php');
global $CFG;
require_once($CFG->dirroot.'/local/elisprogram/lib/setup.php');

// Libs.
require_once(elispm::lib('data/pmclass.class.php'));
require_once(elispm::lib('data/student.class.php'));
require_once(elispm::lib('data/waitlist.class.php'));
require_once(elispm::lib('data/course.class.php'));
require_once(elispm::lib('data/user.class.php'));
require_once(elispm::lib('data/usermoodle.class.php'));
require_once(elispm::lib('data/coursetemplate.class.php'));
require_once(elispm::file('studentpage.class.php'));

/**
 * Test waitlist functions.
 * @group local_elisprogram
 */
class waitlist_testcase extends elis_database_test {

    /**
     * Load initial data from CSVs.
     */
    protected function load_csv_data() {
        $dataset = $this->createCsvDataSet(array(
            pmclass::TABLE => elispm::file('tests/fixtures/pmclass.csv'),
            waitlist::TABLE => elispm::file('tests/fixtures/waitlist.csv'),
        ));
        $this->loadDataSet($dataset);
    }

    /**
     * Load in some data for a course and a class at the bare minimum
     */
    protected function load_csv_data_course_class() {
        $dataset = $this->createCsvDataSet(array(
            course::TABLE => elispm::file('tests/fixtures/pmcourse.csv'),
            pmclass::TABLE => elispm::file('tests/fixtures/pmclass.csv'),
        ));
        $this->loadDataSet($dataset);
    }

    /**
     * Validate that a waitlist record has a particular user associated to a
     * particular class at the top of the wait list, and created and modified times
     * are equal and within the provided range
     *
     * @param int $classid The database record id of the PM class
     * @param int $userid The database record id of the PM user
     * @param int $mintime The minimum time allowed for time created and modified
     * @param int $maxtime The maximum time allowed for time created and modified
     */
    protected function assert_waitlist_record_valid($classid, $userid, $mintime, $maxtime) {
        global $DB;

        // Validate that a related database record exists.
        $exists = $DB->record_exists(waitlist::TABLE, array('classid' => $classid, 'userid' => $userid, 'position' => 1));
        $this->assertTrue($exists);

        // Obtain the record.
        $record = $DB->get_record(waitlist::TABLE, array('classid' => $classid, 'userid' => $userid, 'position' => 1));

        // Timestamp validation.
        $this->assertGreaterThanOrEqual($mintime, $record->timecreated);
        $this->assertLessThanOrEqual($maxtime, $record->timecreated);
        $this->assertEquals($record->timecreated, $record->timemodified);
    }

    /**
     * Test validation of duplicates
     *
     * Note: no exception thrown from waitlist.class.php for dup.
     */
    public function test_waitlistvalidationpreventsduplicates() {
        global $DB;
        $this->load_csv_data();

        $waitlist = new waitlist(array('classid' => 100, 'userid' => 1, 'position' => 1));

        $waitlist->save();
        $waitlistentries = $DB->get_records(waitlist::TABLE, array('classid' => 100, 'userid' => 1));
        $this->assertEquals(count($waitlistentries), 1);
    }

    /**
     * Data provider for test method test_check_autoenrol_after_course_completion()
     * @return array Parameters for test method
     *               format: array(
     *                  completion status,
     *                  student enrollment id
     *                  expected outcome
     *               )
     */
    public function dataprovider_check_autoenrol_after_course_completion() {
        return array(
            // Completion status is not complete.
            array(
                STUSTATUS_NOTCOMPLETE,
                704,
                1,
                false
            ),
            // Course complete, class has a waitlist, meets prereq, auto_enroll off.
            array(
                STUSTATUS_PASSED,
                704,
                0,
                false
            ),
            // Course complete, class has a waitlist, meets prereq, auto_enroll on.
            array(
                STUSTATUS_PASSED,
                704,
                1,
                true
            ),
            // Course complete, class has a waitlist, fails prereq, auto_enroll off.
            array(
                STUSTATUS_PASSED,
                706,
                0,
                false
            ),
            // Course complete, class has a waitlist, fails prereq, auto_enroll on.
            array(
                STUSTATUS_PASSED,
                706,
                1,
                false
            ),
            // Course complete, class has no waitlist, meets prereq, auto_enroll off.
            array(
                STUSTATUS_PASSED,
                705,
                0,
                false
            ),
            // Course complete, class has no waitlist, meets prereq, auto_enroll on.
            array(
                STUSTATUS_PASSED,
                705,
                1,
                false
            ),
            // Course complete, class has no waitlist, fails prereq, auto_enroll off.
            array(
                STUSTATUS_PASSED,
                707,
                0,
                false
            ),
            // Course complete, class has no waitlist, fails prereq, auto_enroll on.
            array(
                STUSTATUS_PASSED,
                707,
                1,
                false
            ),
        );
    }

    /**
     * Test the check_user_prerequisite_status function.
     * @dataProvider dataprovider_check_autoenrol_after_course_completion
     * @param int $classid The class instance to check against.
     * @param int $userid The number enrolled to return from the mocked count_enroled function.
     * @param boolean $expected The expected result.
     */
    public function test_check_autoenrol_after_course_completion($completionstatus, $enrollid, $enableautoenroll, $expected) {
        // Load the data sets.
        $dataset = $this->createCsvDataSet(array(
            curriculum::TABLE => elispm::file('tests/fixtures/elisprogram_pgm.csv'),
            course::TABLE => elispm::file('tests/fixtures/elisprogram_crs.csv'),
            pmclass::TABLE => elispm::file('tests/fixtures/elisprogram_cls.csv'),
            curriculumcourse::TABLE =>elispm::file('tests/fixtures/elisprogram_pgm_crs.csv'),
            user::TABLE => elispm::file('tests/fixtures/elisprogram_usr.csv'),
            student::TABLE=>elispm::file('tests/fixtures/elisprogram_cls_enrol.csv'),
            waitlist::TABLE=>elispm::file('tests/fixtures/elisprogram_waitlist.csv'),
            courseprerequisite::TABLE=>elispm::file('tests/fixtures/elisprogram_prereq.csv'),
        ));
        $this->loadDataSet($dataset);

        // Load the enrollment object.
        $enrollment = new student($enrollid);
        $enrollment->load();
        $enrollment->completestatusid = $completionstatus;
        $enrollment->save();

        // Load the Class Object so that we can set the auto_enrol_waitlist.
        $pmclass = new pmclass($enrollment->classid);
        $pmclass->load();
        $pmclass->enrol_from_waitlist = $enableautoenroll;
        $pmclass->save();

        // Run the test.
        $return = waitlist::check_autoenrol_after_course_completion($enrollment);
        $this->assertEquals($expected, $return);
    }
}