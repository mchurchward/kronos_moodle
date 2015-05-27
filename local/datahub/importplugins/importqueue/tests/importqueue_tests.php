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

defined('MOODLE_INTERNAL') || die();

/**
 * Tests for Kronos portal authentication.
 *
 * @package    auth_kronosportal
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2015 Remote Learner.net Inc http://www.remote-learner.net
 */

/**
 * Test kronosportal auth plugin and web service
 */
class importqueue_testcase extends advanced_testcase {
    /**
     * @var array $users Array of test users.
     */
    private $users = null;

    /**
     * @var array $users Array of custom field ids.
     */
    private $customfields = null;

    /***
     * Test valid solution id.
     */
    public function test_importqueue_validsolutionid() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/local/datahub/importplugins/importqueue/importqueue.class.php');

        $importqueue = new rlip_importplugin_importqueue();
        $solutionid = $importqueue->validsolutionid('00000006-solutionid');
        $this->assertEquals($solutionid->name, 'solutionextension name 00000006-solutionid');
        // Solution id does not exist.
        $solutionid = $importqueue->validsolutionid('00000006-solutionidabc');
        $this->assertFalse($solutionid);
    }

    /***
     * Test valid learning path.
     */
    public function test_importqueue_validlearningpath() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/local/datahub/importplugins/importqueue/importqueue.class.php');

        $importqueue = new rlip_importplugin_importqueue();
        $learningpath = $importqueue->validlearningpath('00000006-solutionid', 'learning path name 00000006-solutionid');
        $this->assertEquals($learningpath->name, 'learning path name 00000006-solutionid');
        $this->assertEquals($learningpath->displayname, 'learning path name 00000006-solutionid');
        // Solution id does not exist.
        $learningpath = $importqueue->validlearningpath('00000006-solutionidabc', 'learning path name 00000006-solutionidabc');
        $this->assertFalse($learningpath);
        // Learning path does not exist.
        $learningpath = $importqueue->validlearningpath('00000006-solutionid', 'learning path name 00000006-solutionidabc');
        $this->assertFalse($learningpath);
    }

    /***
     * Test enrolling a user into a learning path.
     */
    public function test_importqueue_addtolearningpath() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/local/datahub/importplugins/importqueue/importqueue.class.php');

        $importqueue = new rlip_importplugin_importqueue();
        $record = new stdClass();
        $record->email = 'test@test.com';
        $addtolearningpath = $importqueue->addtolearningpath('filename.csv', $record, '00000006-solutionid', 'learning path name 00000006-solutionid');
        $this->assertTrue($addtolearningpath);
        $learningpath = $importqueue->validlearningpath('00000006-solutionid', 'learning path name 00000006-solutionid');

        // Locate elis user id.
        $elisuser = usermoodle::find(array(new field_filter('muserid', $this->users[0]->id)));
        $elisuser = $elisuser->valid() ? $elisuser->current() : null;

        // Check if user is assigned to user set.
        $usersetassign = $DB->get_record('local_elisprogram_uset_asign', array('clusterid' => $learningpath->id, 'userid' => $elisuser->cuserid));
        $this->assertEquals(1, $usersetassign->autoenrol);
        $this->assertEquals('manual', $usersetassign->plugin);
    }

    /***
     * Test if a user can be updated.
     */
    public function test_importqueue_canupdate() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/local/datahub/importplugins/importqueue/importqueue.class.php');

        $importqueue = new rlip_importplugin_importqueue(new importqueueprovidercsv(array('users'), array('users1.csv')));

        // Test update of existing user.
        $this->assertTrue($importqueue->canupdate($this->users[0]));

        $cannot = new stdClass();
        $solutionidfield = "profile_field_".kronosportal_get_solutionfield();
        $cannot->$solutionidfield = 'doesnotexist123';
        $cannot->email = 'test@test.com';
        $cannot->idnumber = 'test@test.com';
        $cannot->username = 'test@test.com';
        $cannot->firstname = 'update';

        // Test for update or create.
        $this->assertFalse($importqueue->canupdate($cannot));

        // Test for update only.
        $this->assertFalse($importqueue->canupdate($cannot, true));

        // Test for non existing user and non existant solutionid.
        $cannot->email = 'test@test1abc1.com';
        $this->assertFalse($importqueue->canupdate($cannot, true));

        // Test for update or create with non site admin and changing solution id.
        $cannot->$solutionidfield = '00000006-solutionid';
        $this->assertFalse($importqueue->canupdate($cannot));

        // Test for update or create with non site admin and same solution id.
        $cannot->$solutionidfield = 'testsolutionid';
        $this->assertTrue($importqueue->canupdate($cannot));
    }

    /**
     * Enable auth plugin.
     */
    protected function enable_plugin() {
        $auths = get_enabled_auth_plugins(true);
        if (!in_array('kronosportal', $auths)) {
            $auths[] = 'kronosportal';
        }
        set_config('auth', implode(',', $auths));
    }

    /**
     * Setup custom field.
     */
    public function setupcustomfield() {
        global $DB;
        // Add a custom field customerid of text type.
        $this->customfields = array();
        $this->customfields['customerid'] = $DB->insert_record('user_info_field', array(
                'shortname' => 'customerid', 'name' => 'Description of customerid', 'categoryid' => 1,
                'datatype' => 'text'));
        $this->customfields['learningpath'] = $DB->insert_record('user_info_field', array(
                'shortname' => 'learningpath', 'name' => 'Description of learning path', 'categoryid' => 1,
                'datatype' => 'text'));
    }

    /**
     * Set custom field data.
     *
     * @param int $fieldid Id of field to set data.
     * @param int $userid User id to set the field on.
     * @param string $value Value to set field to.
     */
    public function setcustomfielddata($fieldid, $userid, $value) {
        global $DB;
        // Set up data.
        $record = new stdClass;
        $record->fieldid = $fieldid;
        $record->userid = $userid;
        $record->data = $value;
        $DB->insert_record('user_info_data', $record);
    }

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
     * Tests set up.
     */
    public function setUp() {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/auth/kronosportal/lib.php');
        require_once($CFG->dirroot.'/auth/kronosportal/auth.php');
        require_once($CFG->dirroot.'/auth/kronosportal/externallib.php');
        require_once($CFG->dirroot.'/local/elisprogram/lib/setup.php');
        require_once(elispm::lib('data/userset.class.php'));
        require_once(elispm::lib('data/user.class.php'));
        require_once(elispm::lib('data/usermoodle.class.php'));

        $this->resetAfterTest();
        $this->enable_plugin();

        $this->setupcustomfield();

        $this->users = array();

        // Use users class to ensure both elis and moodle user is created.
        $data = new stdClass;
        $data->idnumber = 'test@test.com';
        $data->username = 'test@test.com';
        $data->firstname = 'Test';
        $data->lastname = 'User';
        $data->email = 'test@etest.com';
        $data->country = 'CA';
        $data->birthday = '';
        $data->birthmonth = '';
        $data->birthyear = '';
        $data->language = 'en';
        $data->inactive = '0';

        $user = new User();
        $user->set_from_data($data);
        $user->save();
        $data->id = $user->id;

        $this->users[] = $DB->get_record('user', array('idnumber' => 'test@test.com'));

        $this->setcustomfielddata($this->customfields['customerid'], $this->users[0]->id, 'testsolutionid');
        $this->setcustomfielddata($this->customfields['learningpath'], $this->users[0]->id, 'testlearningdata');
        profile_load_data($this->users[0]);

        // Expired solution id.
        $this->users[] = $this->getDataGenerator()->create_user();
        $this->setcustomfielddata($this->customfields['customerid'], $this->users[1]->id, 'expiredsolution');
        $this->setcustomfielddata($this->customfields['learningpath'], $this->users[1]->id, 'testlearningdata');
        profile_load_data($this->users[1]);

        // Extension solution id.
        $this->users[] = $this->getDataGenerator()->create_user();
        $this->setcustomfielddata($this->customfields['customerid'], $this->users[2]->id, 'extensionsolution');
        $this->setcustomfielddata($this->customfields['learningpath'], $this->users[2]->id, 'testlearningdata');

        // Setup custom userset field.
        // Create custom field.
        $fieldcat = new field_category;
        $fieldcat->name = 'Kronos';
        $fieldcat->save();

        $categorycontext = new field_category_contextlevel();
        $categorycontext->categoryid = $fieldcat->id;
        $categorycontext->contextlevel = CONTEXT_ELIS_USERSET;
        $categorycontext->save();

        $field = new field;
        $field->categoryid = $fieldcat->id;
        $field->shortname = 'extension';
        $field->name = 'Extention';
        $field->datatype = 'int';
        $field->save();

        $fieldctx = new field_contextlevel;
        $fieldctx->fieldid = $field->id;
        $fieldctx->contextlevel = CONTEXT_ELIS_USERSET;
        $fieldctx->save();

        $this->customfields['userset_extension'] = $field->id;

        $field = new field;
        $field->categoryid = $fieldcat->id;
        $field->shortname = 'expiry';
        $field->name = 'Expiry';
        $field->datatype = 'int';
        $field->save();

        $fieldctx = new field_contextlevel;
        $fieldctx->fieldid = $field->id;
        $fieldctx->contextlevel = CONTEXT_ELIS_USERSET;
        $fieldctx->save();

        $this->customfields['userset_expiry'] = $field->id;

        $field = new field;
        $field->categoryid = $fieldcat->id;
        $field->shortname = 'customerid';
        $field->name = 'SolutionID';
        $field->datatype = 'char';
        $field->save();

        $this->customfields['userset_solutionid'] = $field->id;

        $fieldctx = new field_contextlevel;
        $fieldctx->fieldid = $field->id;
        $fieldctx->contextlevel = CONTEXT_ELIS_USERSET;
        $fieldctx->save();

        // Create parent userset.
        $userset = array(
            'name' => 'Customer Audience',
            'display' => 'test userset description',
        );

        $us = new userset($userset);
        $us->save();

        // Create valid solutionid userset.
        $userset = array(
            'name' => 'testuserset',
            'display' => 'test userset description',
            'field_customerid' => 'testsolutionid',
            'field_expiry' => time() + 3600,
            'field_extension' => time() + 3600,
            'parent' => $us->id
        );

        $usvalid = new userset();
        $usvalid->set_from_data((object)$userset);
        $usvalid->save();

        // Create expired solutionid userset.
        $userset = array(
            'name' => 'expiredsolution name',
            'display' => 'test userset description',
            'field_customerid' => 'expiredsolution',
            'field_expiry' => time() - 3600,
            'field_extension' => time() - 3600,
            'parent' => $us->id
        );

        $usinvalid = new userset();
        $usinvalid->set_from_data((object)$userset);
        $usinvalid->save();

        // Create solutionid with extension.
        $userset = array(
            'name' => 'solutionextension name',
            'display' => 'test userset description',
            'field_customerid' => 'extensionsolution',
            'field_expiry' => time() - 3600,
            'field_extension' => time() + 3600,
            'parent' => $us->id
        );

        $usinvalid = new userset();
        $usinvalid->set_from_data((object)$userset);
        $usinvalid->save();

        // Create solutionid with only numeric charcters.
        $userset = array(
            'name' => 'solutionextension name 00000006',
            'display' => 'test userset description',
            'field_customerid' => '00000006',
            'field_expiry' => time() - 3600,
            'field_extension' => time() + 3600,
            'parent' => $us->id
        );

        $usinvalid = new userset();
        $usinvalid->set_from_data((object)$userset);
        $usinvalid->save();

        // Create solutionid with alpha, numeric charcters.
        $userset = array(
            'name' => 'solutionextension name 00000006-solutionid',
            'display' => 'test userset description',
            'field_customerid' => '00000006-solutionid',
            'field_expiry' => time() - 3600,
            'field_extension' => time() + 3600,
            'parent' => $us->id
        );

        $usvalid = new userset();
        $usvalid->set_from_data((object)$userset);
        $usvalid->save();

        // Create learning path with alpha, numeric charcters.
        $userset = array(
            'name' => 'learning path name 00000006-solutionid',
            'display' => 'test userset description',
            'parent' => $usvalid->id
        );

        $usvalid = new userset();
        $usvalid->set_from_data((object)$userset);
        $usvalid->save();

        // Setup configuration.
        set_config('expiry', $this->customfields['userset_expiry'], 'auth_kronosportal');
        set_config('extension', $this->customfields['userset_extension'], 'auth_kronosportal');
        set_config('solutionid', $this->customfields['userset_solutionid'], 'auth_kronosportal');
        set_config('user_field_solutionid', $this->customfields['customerid'], 'auth_kronosportal');

        // Add initial import to queue.
        $record = new stdClass();
        $record->userid = $this->users[0]->id;
        $record->status = 0;
        $record->timemodified = time();
        $record->timecreated = time();
        $DB->insert_record('dhimport_importqueue', $record);
    }
}
