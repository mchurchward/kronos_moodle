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
class auth_saml_kronos_testcase extends advanced_testcase {
    /**
     * @var array $users Array of test users.
     */
    private $users = null;

    /**
     * @var array $users Array of custom field ids.
     */
    private $customfields = null;

    /***
     * Test token is created successfully and inserted into database
     */
    public function test_auth_saml_user_login() {
        global $CFG, $DB;
        $auth = get_auth_plugin('saml');
        $this->assertFalse($auth->user_login('test', 'test'));
        $GLOBALS['saml_login'] = 1;
        // Check that configuration is valid.
        $kronosportal = get_auth_plugin('kronosportal');
        $this->assertTrue($kronosportal->is_configuration_valid());
        $this->assertTrue($auth->user_login($this->users[0]->username, ''));

        // With valid solution id and expiry.
        $this->assertTrue($auth->user_authenticated_hook($this->users[0], $this->users[0]->username, ''));
        // Expired.
        $this->assertFalse($auth->user_authenticated_hook($this->users[1], $this->users[1]->username, ''));
        // With extension.
        $this->assertTrue($auth->user_authenticated_hook($this->users[2], $this->users[2]->username, ''));
        // No solution id.
        $this->assertFalse($auth->user_authenticated_hook($this->users[3], $this->users[3]->username, ''));
        // Valid solution, kronosportal auth.
        $this->assertFalse($auth->user_authenticated_hook($this->users[4], $this->users[4]->username, ''));
    }

    /**
     * Enable auth plugin.
     */
    protected function enable_plugin() {
        $auths = get_enabled_auth_plugins(true);
        if (! in_array('saml', $auths)) {
            $auths [] = 'saml';
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
        // Valid solution id.
        $this->users[] = $this->getDataGenerator()->create_user(['auth' => 'saml']);
        $this->setcustomfielddata($this->customfields['customerid'], $this->users[0]->id, 'testsolutionid');
        $this->setcustomfielddata($this->customfields['learningpath'], $this->users[0]->id, 'testlearningdata');

        // Expired solution id.
        $this->users[] = $this->getDataGenerator()->create_user(['auth' => 'saml']);
        $this->setcustomfielddata($this->customfields['customerid'], $this->users[1]->id, 'expiredsolution');
        $this->setcustomfielddata($this->customfields['learningpath'], $this->users[1]->id, 'testlearningdata');

        // Extension solution id.
        $this->users[] = $this->getDataGenerator()->create_user(['auth' => 'saml']);
        $this->setcustomfielddata($this->customfields['customerid'], $this->users[2]->id, 'extensionsolution');
        $this->setcustomfielddata($this->customfields['learningpath'], $this->users[2]->id, 'testlearningdata');

        // No solution id.
        $this->users[] = $this->getDataGenerator()->create_user(['auth' => 'saml']);

        // Valid solution id, not saml.
        $this->users[] = $this->getDataGenerator()->create_user(['auth' => 'kronosportal']);
        $this->setcustomfielddata($this->customfields['customerid'], $this->users[4]->id, 'testsolutionid');
        $this->setcustomfielddata($this->customfields['learningpath'], $this->users[4]->id, 'testlearningdata');

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

        $usinvalid = new userset();
        $usinvalid->set_from_data((object)$userset);
        $usinvalid->save();

        // Setup configuration.
        set_config('expiry', $this->customfields['userset_expiry'], 'auth_kronosportal');
        set_config('extension', $this->customfields['userset_extension'], 'auth_kronosportal');
        set_config('solutionid', $this->customfields['userset_solutionid'], 'auth_kronosportal');
        set_config('user_field_solutionid', $this->customfields['customerid'], 'auth_kronosportal');
    }
}
