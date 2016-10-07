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
 * lib PHPUnit tests
 *
 * @package    auth_saml
 * @category   phpunit
 * @copyright  2013 onwards Remote-Learner {@link http://www.remote-learner.ca/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

global $CFG;
require_once($CFG->dirroot.'/auth/saml/lib.php');
require_once($CFG->dirroot.'/lib/adminlib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');

/**
 * @group auth_saml
 */
class auth_saml_lib_testcase extends advanced_testcase {
    /** @var array $samldata test SAML user attributes data */
    protected $samldata = array();
    /** @var object $user test user */
    protected $user = null;
    /** @var array $authconfig array of saml configuration options */
    protected $authconfig = array();

    /**
     * Generate a test user
     */
    protected function setup_test_user() {
        $this->user = $this->getDataGenerator()->create_user();
    }

    /**
     * Setup test SAML configuration options
     */
    protected function setup_test_authconfig() {
        $this->authconfig['field_lock_custom_testdropdownwithdefault'] = 'unlocked';
        $this->authconfig['field_map_custom_testdropdownwithdefault'] = 'saml_testdropdownwithdefault';
        $this->authconfig['field_updatelocal_custom_testdropdownwithdefault'] = 'oncreate';

        $this->authconfig['field_lock_custom_testdropdownwithnodefault'] = 'unlocked';
        $this->authconfig['field_map_custom_testdropdownwithnodefault'] = 'saml_testdropdownwithnodefault';
        $this->authconfig['field_updatelocal_custom_testdropdownwithnodefault'] = 'oncreate';

        $this->authconfig['field_lock_custom_testcheckboxdefaultunchecked'] = 'unlocked';
        $this->authconfig['field_map_custom_testcheckboxdefaultunchecked'] = 'saml_testcheckboxdefaultunchecked';
        $this->authconfig['field_updatelocal_custom_testcheckboxdefaultunchecked'] = 'oncreate';

        $this->authconfig['field_lock_custom_testcheckboxdefaultchecked'] = 'unlocked';
        $this->authconfig['field_map_custom_testcheckboxdefaultchecked'] = 'saml_testcheckboxdefaultchecked';
        $this->authconfig['field_updatelocal_custom_testcheckboxdefaultchecked'] = 'oncreate';

        $this->authconfig['field_lock_custom_testdate'] = 'unlocked';
        $this->authconfig['field_map_custom_testdate'] = 'saml_testdate';
        $this->authconfig['field_updatelocal_custom_testdate'] = 'oncreate';

        $this->authconfig['field_lock_custom_testdatetime'] = 'unlocked';
        $this->authconfig['field_map_custom_testdatetime'] = 'saml_testdatetime';
        $this->authconfig['field_updatelocal_custom_testdatetime'] = 'oncreate';

        $this->authconfig['field_lock_custom_testtext'] = 'unlocked';
        $this->authconfig['field_map_custom_testtext'] = 'saml_testtext';
        $this->authconfig['field_updatelocal_custom_testtext'] = 'oncreate';

        $this->authconfig['field_lock_custom_testtextarea'] = 'unlocked';
        $this->authconfig['field_map_custom_testtextarea'] = 'saml_testtextarea';
        $this->authconfig['field_updatelocal_custom_testtextarea'] = 'oncreate';
    }

    /**
     * Setup SAML attributes with default data
     */
    protected function setup_saml_test_data() {
        $this->samldata['saml_testdropdownwithdefault'][0] = 0;
        $this->samldata['saml_testdropdownwithnodefault'][0] = 0;
        $this->samldata['saml_testcheckboxdefaultunchecked'][0] = 0;
        $this->samldata['saml_testcheckboxdefaultchecked'][0] = 0;
        $this->samldata['saml_testdate'][0] = 0;
        $this->samldata['saml_testdatetime'][0] = 0;
        $this->samldata['saml_testtext'][0] = 0;
        $this->samldata['saml_testtextarea'][0] = 0;
    }

    /**
     * This functions loads data via the tests/fixtures/auth_swogportal.xml file
     * @return void
     */
    protected function setup_test_data_xml() {
        $this->loadDataSet($this->createXMLDataSet(__DIR__.'/fixtures/auth_saml.xml'));
    }

    /**
     * Test constructor with bad parameters
     * @expectedException coding_exception
     */
    public function test_constructor_with_empty_plugin_name() {
        $mockadminsettingspage = $this->getMock('admin_settingpage', array(), array(), '', false);
        $setting = new profile_fields_lock_options('', $mockadminsettingspage);
    }

    /**
     * Test constructor with bad parameters
     * @expectedException coding_exception
     */
    public function test_constructor_with_empty_settings_instance() {
        $settings = 'bad';
        $setting = new profile_fields_lock_options('', $settings);
    }

    /**
     * Test constructor with bad parameters
     * @expectedException coding_exception
     */
    public function test_constructor_with_invalid_retrieve_options() {
        $mockadminsettingspage = $this->getMock('admin_settingpage', array(), array(), '', false);
        $setting = new profile_fields_lock_options('auth_test', $mockadminsettingspage, 0);
    }

    /**
     * Test constructor
     */
    public function test_constructor() {
        $mockadminsettingspage = $this->getMock('admin_settingpage', array(), array(), '', false);
        $setting = new profile_fields_lock_options('auth_test', $mockadminsettingspage);
        $this->assertInstanceOf('profile_fields_lock_options', $setting);
    }

    /**
     * Test retrieving options flag
     */
    public function test_get_retrieve_options() {
        $mockadminsettingspage = $this->getMock('admin_settingpage', array(), array(), '', false);
        $setting = new profile_fields_lock_options('auth_test', $mockadminsettingspage, true);
        $this->assertTrue($setting->get_retrieve_options());
    }

    /**
     * Test setting options flag value
     * @expectedException coding_exception
     */
    public function test_set_retrieve_options_invalid_parameter() {
        $mockadminsettingspage = $this->getMock('admin_settingpage', array(), array(), '', false);
        $setting = new profile_fields_lock_options('auth_test', $mockadminsettingspage, true);
        $setting->set_retrieve_options(0);
    }

    /**
     * Test setting options flag value
     */
    public function test_set_retrieve_options() {
        $mockadminsettingspage = $this->getMock('admin_settingpage', array(), array(), '', false);
        $setting = new profile_fields_lock_options('auth_test', $mockadminsettingspage, true);
        $setting->set_retrieve_options(false);

        $this->assertFalse($setting->get_retrieve_options());
    }

    /**
     * Test for invalid profile name
     */
    public function test_profile_name_is_valid_return_false() {
        $mockadminsettingspage = $this->getMock('admin_settingpage', array(), array(), '', false);
        $setting = new profile_fields_lock_options('auth_test', $mockadminsettingspage, true);
        $this->assertFalse($setting->profile_name_is_valid('phpunittest'));
    }

    /**
     * Test for valid profile name
     */
    public function test_profile_name_is_valid_return_true() {
        $mockadminsettingspage = $this->getMock('admin_settingpage', array(), array(), '', false);
        $setting = new profile_fields_lock_options('auth_test', $mockadminsettingspage, true);
        $this->assertTrue($setting->profile_name_is_valid('firstname'));
    }

    /**
     * Test passing invalid profile name
     */
    public function test_change_profile_language_string_invalid_profile_name() {
        $mockadminsettingspage = $this->getMock('admin_settingpage', array(), array(), '', false);
        $setting = new profile_fields_lock_options('auth_test', $mockadminsettingspage, true);
        $this->assertFalse($setting->change_profile_language_string('phpunit', 'phpunit'));
    }

    /**
     * Test passing invalid profile name
     */
    public function test_change_profile_language_string_valid_profile_name() {
        $mockadminsettingspage = $this->getMock('admin_settingpage', array(), array(), '', false);
        $setting = new profile_fields_lock_options('auth_test', $mockadminsettingspage, true);
        $this->assertTrue($setting->change_profile_language_string('firstname', 'phpunit'));
    }

    /**
     * Tests validating using an invalid value
     */
    public function test_validate_saml_data_for_menu_returns_false() {
        global $DB;

        $this->resetAfterTest(true);
        $this->setup_test_data_xml();

        $customfield = $DB->get_record('user_info_field', array('id' => 1));

        $samlattribute = 'valuedoesnotexist';

        $result = validate_saml_data_for_menu($customfield, $samlattribute);

        $this->assertFalse($result);
    }

    /**
     * Tests validating using a valid value
     */
    public function test_validate_saml_data_for_menu_returns_true() {
        global $DB;

        $this->resetAfterTest(true);
        $this->setup_test_data_xml();

        $customfield = $DB->get_record('user_info_field', array('id' => 1));

        $samlattribute = 'two';

        $result = validate_saml_data_for_menu($customfield, $samlattribute);

        $this->assertTrue($result);
    }

    /**
     * Tests validating using a valid value
     */
    public function test_validate_saml_data_for_menu_returns_empty_saml_value_false() {
        global $DB;

        $this->resetAfterTest(true);
        $this->setup_test_data_xml();

        $customfield = $DB->get_record('user_info_field', array('id' => 1));

        $samlattribute = '';

        $result = validate_saml_data_for_menu($customfield, $samlattribute);

        $this->assertFalse($result);
    }

    /**
     * Tests validating useing an invalid value
     */
    public function test_validate_saml_data_for_datetime_zero_integer() {
        $customfield = new stdClass();
        $customfield->id = 1;
        $customfield->shortname = 'testdatetime';
        $customfield->name = 'testdatetime';
        $customfield->datatype = 'datetime';
        $customfield->param1 = 2000;
        $customfield->param2 = 2010;

        $samlattribute = '0';

        $result = validate_saml_data_for_datetime($customfield, $samlattribute);

        $this->assertFalse($result);
    }

    /**
     * Tests validating useing an invalid value
     */
    public function test_validate_saml_data_for_datetime_year_out_of_bounds() {
        $customfield = new stdClass();
        $customfield->id = 1;
        $customfield->shortname = 'testdatetime';
        $customfield->name = 'testdatetime';
        $customfield->datatype = 'datetime';
        $customfield->param1 = 2000;
        $customfield->param2 = 2010;

        // Oct 3 21:49:11 1998 GMT
        $samlattribute = '907451351';

        $result = validate_saml_data_for_datetime($customfield, $samlattribute);

        $this->assertFalse($result);
    }

    /**
     * Tests validating useing an invalid value
     */
    public function test_validate_saml_data_for_datetime_year_out_of_bounds_2() {
        $customfield = new stdClass();
        $customfield->id = 1;
        $customfield->shortname = 'testdatetime';
        $customfield->name = 'testdatetime';
        $customfield->datatype = 'datetime';
        $customfield->param1 = 2000;
        $customfield->param2 = 2010;

        // Oct 3 21:49:11 2034 GMT
        $samlattribute = '2043524951';

        $result = validate_saml_data_for_datetime($customfield, $samlattribute);

        $this->assertFalse($result);
    }

    /**
     * Tests validating useing a valid value
     */
    public function test_validate_saml_data_for_datetime_valid() {
        $customfield = new stdClass();
        $customfield->id = 1;
        $customfield->shortname = 'testdatetime';
        $customfield->name = 'testdatetime';
        $customfield->datatype = 'datetime';
        $customfield->param1 = 2000;
        $customfield->param2 = 2010;

        // Oct 3 21:49:11 2005 GMT
        $samlattribute = '1128376151';

        $result = validate_saml_data_for_datetime($customfield, $samlattribute);

        $this->assertTrue($result);
    }

    /**
     * Test cleaning a parameter that has surrounding spaces
     */
    public function test_clean_saml_data_for_custom_profile_value_with_pre_post_spaces() {
        $value = '   test   ';
        $expected = 'test';
        $result = clean_saml_data_for_custom_profile($value);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test cleaning a parameter that has HTML tags
     */
    public function test_clean_saml_data_for_custom_profile_value_with_tags() {
        $value = '<p>test</p>';
        $expected = 'test';
        $result = clean_saml_data_for_custom_profile($value);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test cleaning a parameter that has HTML tags
     */
    public function test_clean_saml_data_for_custom_profile_value_with_raw_input() {
        $value = '<p>test</p>';
        $expected = 'test';
        $result = clean_saml_data_for_custom_profile($value, PARAM_TEXT);
        $this->assertEquals($expected, $result);
    }

    /**
     * Test whether field name matches custom field prefix
     */
    public function test_valid_custom_field_prefix_invalid() {
        $result = valid_custom_field_prefix('field_map_INVALID_custom_testdropdownwithdefault');
        $this->assertFalse($result);
    }

    /**
     * Test whether field name matches custom field prefix
     */
    public function test_valid_custom_field_prefix_valid() {
        $result = valid_custom_field_prefix('field_map_custom_testdropdownwithdefault');
        $this->assertTrue($result);
    }

    /**
     * Test whether the config profile shortname matches a user_info_field shortname
     */
    public function test_valid_matching_config_profile_shortname_invalid() {
        global $DB;

        $this->resetAfterTest(true);
        $this->setup_test_data_xml();
        $tablecolumns = 'shortname,id,name,datatype,defaultdata,param1,param2,param3';
        $customfields = $DB->get_records('user_info_field', array(), null, $tablecolumns);

        $result = valid_matching_config_profile_shortname('phpunit_custom_field', $customfields);

        $this->assertFalse($result);
    }

    /**
     * Test whether the config profile shortname matches a user_info_field shortname
     */
    public function test_valid_matching_config_profile_shortname_valid() {
        global $DB;

        $this->resetAfterTest(true);
        $this->setup_test_data_xml();
        $tablecolumns = 'shortname,id,name,datatype,defaultdata,param1,param2,param3';
        $customfields = $DB->get_records('user_info_field', array(), null, $tablecolumns);

        $result = valid_matching_config_profile_shortname('field_map_custom_testdropdownwithdefault', $customfields);

        $this->assertTrue($result);
    }

    /**
     * Test whether the field mapping value matches a SAML field attribute
     */
    public function test_valid_matching_field_map_to_saml_attribute_invalid() {
        $this->setup_saml_test_data();

        $result = valid_matching_field_map_to_saml_attribute('saml_phpunit_field', $this->samldata);

        $this->assertFalse($result);
    }

    /**
     * Test whether the field mapping value matches a SAML field attribute
     */
    public function test_valid_matching_field_map_to_saml_attribute_empty_value() {
        $this->setup_saml_test_data();

        $result = valid_matching_field_map_to_saml_attribute('', $this->samldata);

        $this->assertFalse($result);
    }

    /**
     * Test whether the field mapping value matches a SAML field attribute
     */
    public function test_valid_matching_field_map_to_saml_attribute_valid() {
        $this->setup_saml_test_data();

        $result = valid_matching_field_map_to_saml_attribute('saml_testdropdownwithdefault', $this->samldata);

        $this->assertTrue($result);
    }

    /**
     * Test whether a field needs updating or not
     */
    public function test_needs_updating_return_oncreate_newuser_return_true() {
        $this->setup_test_authconfig();
        $name = 'testdropdownwithdefault';
        $newuser = true;
        $result = needs_updating($this->authconfig, $name, $newuser);

        $this->assertTrue($result);
    }

    /**
     * Test whether a field needs updating or not
     */
    public function test_needs_updating_return_oncreate_newuser_return_false() {
        $this->setup_test_authconfig();
        $name = 'testdropdownwithdefault';
        $newuser = false;
        $result = needs_updating($this->authconfig, $name, $newuser);

        $this->assertFalse($result);
    }

    /**
     * Test whether a field needs updating or not
     */
    public function test_needs_updating_return_onlogin_newuser_return_true() {
        $this->setup_test_authconfig();
        $this->authconfig['field_updatelocal_custom_testdropdownwithdefault'] = 'onlogin';
        $name = 'testdropdownwithdefault';
        $newuser = true;
        $result = needs_updating($this->authconfig, $name, $newuser);

        $this->assertTrue($result);
    }

    /**
     * Test whether a field needs updating or not
     */
    public function test_needs_updating_return_onlogin_newuser_return_false() {
        $this->setup_test_authconfig();
        $this->authconfig['field_updatelocal_custom_testdropdownwithdefault'] = 'onlogin';
        $name = 'testdropdownwithdefault';
        $newuser = false;
        $result = needs_updating($this->authconfig, $name, $newuser);

        $this->assertTrue($result);
    }

    /**
     * Test that no user custom profile field data was synchronized
     */
    public function test_auth_saml_sync_custom_profile_fields_updating_dropdown() {
        global $DB;

        $this->resetAfterTest(true);
        $this->setup_test_user();
        $this->setup_test_data_xml();
        $this->setup_saml_test_data();
        $this->setup_test_authconfig();

        $tablecolumns = 'shortname,id,name,datatype,defaultdata,param1,param2,param3';
        $customfields = $DB->get_records('user_info_field', array(), null, $tablecolumns);

        $this->authconfig['field_updatelocal_custom_testdropdownwithdefault'] = 'onlogin';
        $this->authconfig['field_updatelocal_custom_testdropdownwithnodefault'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testcheckboxdefaultunchecked'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testcheckboxdefaultchecked'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testdate'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testdatetime'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testtext'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testtextarea'] = 'oncreate';

        $this->samldata['saml_testdropdownwithdefault'][0] = 'four';

        auth_saml_sync_custom_profile_fields($this->user, $this->authconfig, $this->samldata, $customfields, false);

        $result = $DB->get_records('user_info_data', array('userid' => $this->user->id));

        // Id numbers are irrelevant so we copy it from the actual data.
        $newid = key($result);

        $expectedvalue = new stdClass();
        $expectedvalue->id = $newid;
        $expectedvalue->userid = $this->user->id;
        $expectedvalue->fieldid = '1';
        $expectedvalue->data = 'four';
        $expectedvalue->dataformat = '0';

        $expected = array($newid => $expectedvalue);

        $this->assertEquals(1, count($result));
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that no user custom profile field data was synchronized
     */
    public function test_auth_saml_sync_custom_profile_fields_updating_dropdown_using_default() {
        global $DB;

        $this->resetAfterTest(true);
        $this->setup_test_user();
        $this->setup_test_data_xml();
        $this->setup_saml_test_data();
        $this->setup_test_authconfig();

        $tablecolumns = 'shortname,id,name,datatype,defaultdata,param1,param2,param3';
        $customfields = $DB->get_records('user_info_field', array(), null, $tablecolumns);

        $this->authconfig['field_updatelocal_custom_testdropdownwithdefault'] = 'onlogin';
        $this->authconfig['field_updatelocal_custom_testdropdownwithnodefault'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testcheckboxdefaultunchecked'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testcheckboxdefaultchecked'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testdate'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testdatetime'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testtext'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testtextarea'] = 'oncreate';

        $this->samldata['saml_testdropdownwithdefault'][0] = '';

        auth_saml_sync_custom_profile_fields($this->user, $this->authconfig, $this->samldata, $customfields, false);

        $result = $DB->get_records('user_info_data', array('userid' => $this->user->id));

        // If a menu of choices has an empty SAML value then we cannot save the value in the User's profile data
        $this->assertEquals(0, count($result));
    }

    /**
     * Test that no user custom profile field data was synchronized
     */
    public function test_auth_saml_sync_custom_profile_fields_updating_checkbox_unchecked() {
        global $DB;

        $this->resetAfterTest(true);
        $this->setup_test_user();
        $this->setup_test_data_xml();
        $this->setup_saml_test_data();
        $this->setup_test_authconfig();

        $tablecolumns = 'shortname,id,name,datatype,defaultdata,param1,param2,param3';
        $customfields = $DB->get_records('user_info_field', array(), null, $tablecolumns);

        $this->authconfig['field_updatelocal_custom_testdropdownwithdefault'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testdropdownwithnodefault'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testcheckboxdefaultunchecked'] = 'onlogin';
        $this->authconfig['field_updatelocal_custom_testcheckboxdefaultchecked'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testdate'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testdatetime'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testtext'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testtextarea'] = 'oncreate';

        $this->samldata['saml_testcheckboxdefaultunchecked'][0] = '';

        auth_saml_sync_custom_profile_fields($this->user, $this->authconfig, $this->samldata, $customfields, false);

        $result = $DB->get_records('user_info_data', array('userid' => $this->user->id));

        // Id numbers are irrelevant so we copy it from the actual data.
        $newid = key($result);

        $expectedvalue = new stdClass();
        $expectedvalue->id = $newid;
        $expectedvalue->userid = $this->user->id;
        $expectedvalue->fieldid = '3';
        $expectedvalue->data = '0';
        $expectedvalue->dataformat = '0';

        $expected = array($newid => $expectedvalue);

        $this->assertEquals(1, count($result));
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that no user custom profile field data was synchronized
     */
    public function test_auth_saml_sync_custom_profile_fields_updating_checkbox_checked() {
        global $DB;

        $this->resetAfterTest(true);
        $this->setup_test_user();
        $this->setup_test_data_xml();
        $this->setup_saml_test_data();
        $this->setup_test_authconfig();

        $tablecolumns = 'shortname,id,name,datatype,defaultdata,param1,param2,param3';
        $customfields = $DB->get_records('user_info_field', array(), null, $tablecolumns);

        $this->authconfig['field_updatelocal_custom_testdropdownwithdefault'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testdropdownwithnodefault'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testcheckboxdefaultunchecked'] = 'onlogin';
        $this->authconfig['field_updatelocal_custom_testcheckboxdefaultchecked'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testdate'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testdatetime'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testtext'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testtextarea'] = 'oncreate';

        $this->samldata['saml_testcheckboxdefaultunchecked'][0] = 'phpunit_some_value';

        auth_saml_sync_custom_profile_fields($this->user, $this->authconfig, $this->samldata, $customfields, false);

        $result = $DB->get_records('user_info_data', array('userid' => $this->user->id));

        // Id numbers are irrelevant so we copy it from the actual data.
        $newid = key($result);

        $expectedvalue = new stdClass();
        $expectedvalue->id = $newid;
        $expectedvalue->userid = $this->user->id;
        $expectedvalue->fieldid = '3';
        $expectedvalue->data = '1';
        $expectedvalue->dataformat = '0';

        $expected = array($newid => $expectedvalue);

        $this->assertEquals(1, count($result));
        $this->assertEquals(reset($expected), reset($result));
    }

    /**
     * Test that no user custom profile field data was synchronized
     */
    public function test_auth_saml_sync_custom_profile_fields_updating_date() {
        global $DB;

        // If timezone is not set then skip this test.
        $timezone = date_default_timezone_get();
        if (!$timezone) {
            $this->markTestSkipped('Timezone is not set.');
        }

        $this->resetAfterTest(true);
        $this->setup_test_user();
        $this->setup_test_data_xml();
        $this->setup_saml_test_data();
        $this->setup_test_authconfig();

        $tablecolumns = 'shortname,id,name,datatype,defaultdata,param1,param2,param3';
        $customfields = $DB->get_records('user_info_field', array(), null, $tablecolumns);

        $this->authconfig['field_updatelocal_custom_testdropdownwithdefault'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testdropdownwithnodefault'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testcheckboxdefaultunchecked'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testcheckboxdefaultchecked'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testdate'] = 'onlogin';
        $this->authconfig['field_updatelocal_custom_testdatetime'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testtext'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testtextarea'] = 'oncreate';

        // Oct 3 21:49:11 2005 GMT
        $this->samldata['saml_testdate'][0] = 1128376151;
        auth_saml_sync_custom_profile_fields($this->user, $this->authconfig, $this->samldata, $customfields, false);

        $result = $DB->get_records('user_info_data', array('userid' => $this->user->id));

        // Id numbers are irrelevant so we copy it from the actual data.
        $newid = key($result);

        $expectedvalue = new stdClass();
        $expectedvalue->id = $newid;
        $expectedvalue->userid = $this->user->id;
        $expectedvalue->fieldid = '5';
        $pfd = new profile_field_datetime(5);
        // Get the calculated date.
        $testdate = $pfd->edit_save_data_preprocess($this->samldata['saml_testdate'][0], null);
        $expectedvalue->data = $testdate;
        $expectedvalue->dataformat = '0';

        $expected = array($newid => $expectedvalue);

        $this->assertEquals(1, count($result));
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that no user custom profile field data was synchronized
     */
    public function test_auth_saml_sync_custom_profile_fields_updating_datetime() {
        global $DB;

        $this->resetAfterTest(true);
        $this->setup_test_user();
        $this->setup_test_data_xml();
        $this->setup_saml_test_data();
        $this->setup_test_authconfig();

        $tablecolumns = 'shortname,id,name,datatype,defaultdata,param1,param2,param3';
        $customfields = $DB->get_records('user_info_field', array(), null, $tablecolumns);

        $this->authconfig['field_updatelocal_custom_testdropdownwithdefault'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testdropdownwithnodefault'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testcheckboxdefaultunchecked'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testcheckboxdefaultchecked'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testdate'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testdatetime'] = 'onlogin';
        $this->authconfig['field_updatelocal_custom_testtext'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testtextarea'] = 'oncreate';

        // Oct 3 21:49:11 2005 GMT
        $this->samldata['saml_testdatetime'][0] = 1128376151;

        auth_saml_sync_custom_profile_fields($this->user, $this->authconfig, $this->samldata, $customfields, false);

        $result = $DB->get_records('user_info_data', array('userid' => $this->user->id));

        // Id numbers are irrelevant so we copy it from the actual data.
        $newid = key($result);

        $expectedvalue = new stdClass();
        $expectedvalue->id = $newid;
        $expectedvalue->userid = $this->user->id;
        $expectedvalue->fieldid = '6';
        // Oct 3 21:49:11 2005 GMT
        $expectedvalue->data = '1128376151';
        $expectedvalue->dataformat = '0';

        $expected = array($newid => $expectedvalue);

        $this->assertEquals(1, count($result));
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that no user custom profile field data was synchronized
     */
    public function test_auth_saml_sync_custom_profile_fields_updating_textfield() {
        global $DB;

        $this->resetAfterTest(true);
        $this->setup_test_user();
        $this->setup_test_data_xml();
        $this->setup_saml_test_data();
        $this->setup_test_authconfig();

        $tablecolumns = 'shortname,id,name,datatype,defaultdata,param1,param2,param3';
        $customfields = $DB->get_records('user_info_field', array(), null, $tablecolumns);

        $this->authconfig['field_updatelocal_custom_testdropdownwithdefault'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testdropdownwithnodefault'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testcheckboxdefaultunchecked'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testcheckboxdefaultchecked'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testdate'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testdatetime'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testtext'] = 'onlogin';
        $this->authconfig['field_updatelocal_custom_testtextarea'] = 'oncreate';

        // Oct 3 21:49:11 2005 GMT
        $this->samldata['saml_testtext'][0] = 'phpunit test text';

        auth_saml_sync_custom_profile_fields($this->user, $this->authconfig, $this->samldata, $customfields, false);

        $result = $DB->get_records('user_info_data', array('userid' => $this->user->id));

        // Id numbers are irrelevant so we copy it from the actual data.
        $newid = key($result);

        $expectedvalue = new stdClass();
        $expectedvalue->id = $newid;
        $expectedvalue->userid = $this->user->id;
        $expectedvalue->fieldid = '8';
        $expectedvalue->data = 'phpunit test text';
        $expectedvalue->dataformat = '0';

        $expected = array($newid => $expectedvalue);

        $this->assertEquals(1, count($result));
        $this->assertEquals($expected, $result);
    }

    /**
     * Test that no user custom profile field data was synchronized
     */
    public function test_auth_saml_sync_custom_profile_fields_updating_textarea() {
        global $DB;

        $this->resetAfterTest(true);
        $this->setup_test_user();
        $this->setup_test_data_xml();
        $this->setup_saml_test_data();
        $this->setup_test_authconfig();

        $tablecolumns = 'shortname,id,name,datatype,defaultdata,param1,param2,param3';
        $customfields = $DB->get_records('user_info_field', array(), null, $tablecolumns);

        $this->authconfig['field_updatelocal_custom_testdropdownwithdefault'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testdropdownwithnodefault'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testcheckboxdefaultunchecked'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testcheckboxdefaultchecked'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testdate'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testdatetime'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testtext'] = 'oncreate';
        $this->authconfig['field_updatelocal_custom_testtextarea'] = 'onlogin';

        // Oct 3 21:49:11 2005 GMT
        $this->samldata['saml_testtextarea'][0] = 'phpunit test textarea';

        auth_saml_sync_custom_profile_fields($this->user, $this->authconfig, $this->samldata, $customfields, false);

        $result = $DB->get_records('user_info_data', array('userid' => $this->user->id));

        // Id numbers are irrelevant so we copy it from the actual data.
        $newid = key($result);

        $expectedvalue = new stdClass();
        $expectedvalue->id = $newid;
        $expectedvalue->userid = $this->user->id;
        $expectedvalue->fieldid = '7';
        $expectedvalue->data = 'phpunit test textarea';
        $expectedvalue->dataformat = '1';

        $expected = array($newid => $expectedvalue);

        $this->assertEquals(1, count($result));
        $this->assertEquals($expected, $result);
    }
}
