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
 * Tests for Kronos sandbox activity.
 *
 * @package    mod_kronossandvm
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2015 Remote Learner.net Inc http://www.remote-learner.net
 */
class mod_kronossandvm_activity_testcase extends advanced_testcase {
    /**
     * @var array $users Array of test users.
     */
    private $users = null;
    /**
     * @var int $course Course id.
     */
    private $course = null;
    /**
     * @var int $vmcourseid Virtual machine course id.
     */
    private $vmcourseid = null;
    /**
     * @var int $context Context.
     */
    private $context = null;
    /**
     * @var int $instance Instance.
     */
    private $instance = null;
    /**
     * @var int $fieldid Field id of custom field.
     */
    private $fieldid = null;
    /**
     * @var int $studentroleid Role id for students.
     */
    private $studentroleid = null;
    /**
     * @var int $employeeroleid Role id for employees.
     */
    private $employeeroleid = null;
    /**
     * @var int $kronossandvm Record for activity.
     */
    private $kronossandvm = null;

    /**
     * Tests set up.
     */
    public function setUp() {
        global $DB;
        $this->resetAfterTest();
        // Setup vm_courses.
        $obj = new stdClass();
        $obj->coursename = 'testcoursename';
        $obj->imageid = 'testid1';
        $obj->otcourseno = 'testid2';
        $obj->isactive = 1;
        $obj->imagesource = 'testid3';
        $obj->imagetype = 'testid4';
        $obj->tusername = 'testid5';
        $obj->tpassword = 'testid6';
        $obj->imagename = 'testid7';
        $this->vmcourseid = $DB->insert_record('vm_courses', $obj);

        $this->course = $this->getDataGenerator()->create_course();
        $options = array('course' => $this->course->id, 'otcourseid' => $this->vmcourseid);
        $this->kronossandvm = $this->getDataGenerator()->create_module('kronossandvm', $options);
        $this->instance = context_module::instance($this->kronossandvm->cmid);
        $this->context = context_course::instance($this->course->id);
        $roleid = create_role('Employee role', 'employeerole', 'Employee role description');
        $this->employeeroleid = $roleid;
        $role = $DB->get_record('role', array('shortname' => 'student'));
        $this->studentroleid = $role->id;
        assign_capability('mod/kronossandvm:employee', CAP_ALLOW, $this->employeeroleid, $this->context->id);
        $this->users = array();
        $this->users[] = $this->getDataGenerator()->create_user();
        $this->users[] = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($this->users[0]->id, $this->course->id, $role->id);
        $this->getDataGenerator()->enrol_user($this->users[1]->id, $this->course->id, $role->id);
    }

    /**
     * Setup custom field.
     */
    public function setupcustomfield() {
        global $DB;
        // Add a custom field solutionid of text type.
        $this->fieldid = $DB->insert_record('user_info_field', array(
                'shortname' => 'solutionid', 'name' => 'Description of solutionid', 'categoryid' => 1,
                'datatype' => 'text'));
        $this->setcustomfielddata($this->users[0]->id, 'test');
    }

    /**
     * Set custom field data.
     *
     * @param int $userid User id to set the field on.
     * @param string $value Value to set field to.
     */
    public function setcustomfielddata($userid, $value) {
        global $DB;
        // Set up data.
        $record = new stdClass;
        $record->fieldid = $this->fieldid;
        $record->userid = $userid;
        $record->data = $value;
        $DB->insert_record('user_info_data', $record);
    }

    /**
     * Test for message that no customer id exists.
     */
    public function test_nosolutionid() {
        list ($allow, $message) = kronossandvm_get_message($this->context, $this->instance->id);
        $this->assertEquals(get_string('missingsolutionid', 'kronossandvm'), $message);
        $this->assertEquals(0, $allow);
    }

    /**
     * Test has missing capability, for employees.
     */
    public function test_missing_capability() {
        $this->setupcustomfield();
        $this->setUser($this->users[0]);
        $cap = 'mod/kronossandvm:employee';
        $this->assertFalse(has_capability($cap, $this->context, $this->users[0]->id));
        // Assign employee role to user which has mod/kronossandvm:employee.
        role_assign($this->employeeroleid, $this->users[0]->id, $this->context);
        accesslib_clear_all_caches_for_unit_testing();
        $this->assertTrue(has_capability($cap, $this->context, $this->users[0]->id));
        $this->setUser($this->users[0]);
        list ($allow, $message) = kronossandvm_get_message($this->context, $this->instance->id);
        $this->assertEquals(get_string('restrictkronosemployee', 'kronossandvm'), $message);
        $this->assertEquals(0, $allow);
    }

    /**
     * Test request form shows.
     */
    public function test_get_request_button() {
        $this->setupcustomfield();
        $this->setUser($this->users[0]);
        list ($allow, $message) = kronossandvm_get_message($this->instance, $this->instance->id);
        $this->assertEquals(kronossandvm_get_request_form($this->instance->id), $message);
        $this->assertEquals(1, $allow);
        // Validate the value is a form.
        $this->assertEquals(1, preg_match("/^<form/", $message));
        $this->assertEquals(1, preg_match("/submitted/", $message));
    }

    /**
     * Test max amount of requests for a user in one day.
     */
    public function test_per_user_per_day() {
        global $CFG;
        $this->setupcustomfield();
        $newreq = new stdClass();
        $newreq->vmid = $this->kronossandvm->id;
        $newreq->userid = $this->users[0]->id;
        $reqid = kronossandvm_add_vmrequest($this->context, $this->kronossandvm, $newreq);
        $this->setUser($this->users[0]);
        list ($allow, $message) = kronossandvm_get_message($this->context, $this->instance->id);
        $obj = new stdClass();
        $obj->limit = get_string('one', 'kronossandvm');
        if ($CFG->mod_kronossandvm_requestsuserperday > 1) {
            $obj->limit = $CFG->mod_kronossandvm_requestsuserperday;
        }
        $expectedmessage = get_string('peruserrestriction', 'kronossandvm', $obj);
        $this->assertEquals($expectedmessage, $message);
    }

    /**
     * Test max amount of requests for a customer in one day.
     */
    public function test_per_customer_per_day() {
        global $CFG;
        $this->setupcustomfield();
        $newreq = new stdClass();
        $newreq->vmid = $this->kronossandvm->id;
        $newreq->isactive = 1;
        $newreq->userid = $this->users[0]->id;
        for ($i = 0; $i < ($CFG->mod_kronossandvm_requestssolutionperday + 2); $i++) {
            $reqid = kronossandvm_add_vmrequest($this->context, $this->kronossandvm, $newreq);
        }
        // Login as another user other wise you will get max sessions for user message.
        $this->setcustomfielddata($this->users[1]->id, 'test');
        $this->setUser($this->users[1]);
        list ($allow, $message) = kronossandvm_get_message($this->context, $this->instance->id);
        $this->assertEquals(get_string('persolutionrestriction', 'kronossandvm', $CFG), $message);
    }

    /**
     * Test max amount of requests for a customer in one day.
     */
    public function test_customer_concurrently_per_day() {
        global $CFG;
        $this->setupcustomfield();
        $newreq = new stdClass();
        $newreq->vmid = $this->kronossandvm->id;
        $newreq->isactive = 1;
        $newreq->userid = $this->users[0]->id;
        for ($i = 0; $i < ($CFG->mod_kronossandvm_requestsconcurrentpercustomer + 2); $i++) {
            $reqid = kronossandvm_add_vmrequest($this->context, $this->kronossandvm, $newreq);
        }
        // Login as another user other wise you will get max sessions for user message.
        $this->setcustomfielddata($this->users[1]->id, 'test');
        $this->setUser($this->users[1]);
        list ($allow, $message) = kronossandvm_get_message($this->context, $this->instance->id);
        $this->assertEquals(get_string('conpersolutionrestriction', 'kronossandvm', $CFG), $message);
    }

    /**
     * Test vmrequest_created event.
     */
    public function test_create_event_vmrequest_created() {
        global $DB;
        $this->setupcustomfield();
        // For capturing events.
        $sink = $this->redirectEvents();
        $newreq = new stdClass();
        $newreq->vmid = $this->kronossandvm->id;
        $newreq->userid = $this->users[0]->id;
        $reqid = kronossandvm_add_vmrequest($this->context, $this->kronossandvm, $newreq);
        $events = $sink->get_events();
        $this->assertCount(1, $events);
        $data = $events[0]->get_data();
        $this->assertEquals('\mod_kronossandvm\event\vmrequest_created', $data['eventname']);
        $this->assertEquals('created', $data['action']);
        $this->assertEquals($this->kronossandvm->id, $data['objectid']);
    }

    /**
     * Test building of kronos link.
     */
    public function test_kronossandvm_buildurl() {
        global $CFG;
        $newreq = new stdClass();
        $newreq->vmid = $this->kronossandvm->id;
        $newreq->userid = $this->users[0]->id;
        $newreq->instanceip = '1.2.3.4';
        $newreq->instanceid = 'bb&#d33';
        $link = kronossandvm_buildurl($newreq);
        $this->assertEquals('http://edweb2.kronos.com/onsite/connectvm.aspx?sIP=1.2.3.4', $link);
        $CFG->mod_kronossandvm_requesturl = 'http://edweb2.kronos.com/test.asp?ip={instanceip}&userid={userid}&vmid={vmid}&instanceid={instanceid}';
        $expected = 'http://edweb2.kronos.com/test.asp?ip='.$newreq->instanceip;
        $expected .= '&userid='.$newreq->userid.'&vmid='.$newreq->vmid.'&instanceid='.urlencode($newreq->instanceid);
        $link = kronossandvm_buildurl($newreq);
        $this->assertEquals($expected, $link);
    }

    /**
     * Test retrieving virtual machine requests.
     */
    public function test_webservice_vm_requests() {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/mod/kronossandvm/externallib.php');
        // Setup.
        $webservice = new mod_kronossandvm_external();
        $result = $webservice->vm_requests();
        $this->assertCount(0, $result);
        $this->setupcustomfield();
        $this->setcustomfielddata($this->users[1]->id, 'test');
        // Add first request.
        $newreq = new stdClass();
        $newreq->vmid = $this->kronossandvm->id;
        $newreq->userid = $this->users[0]->id;
        $reqid = kronossandvm_add_vmrequest($this->context, $this->kronossandvm, $newreq);
        // Test there is one request.
        $result = $webservice->vm_requests();
        $this->assertEquals($this->users[0]->id, $result[1]->userid);
        $this->assertCount(1, $result);
        $this->assertEquals($this->users[0]->id, $result[1]->userid);
        // Add second request.
        $newreq->userid = $this->users[1]->id;
        $reqid = kronossandvm_add_vmrequest($this->context, $this->kronossandvm, $newreq);
        $result = $webservice->vm_requests();
        // Test there is two requests.
        $this->assertCount(2, $result);
        $this->assertEquals($this->users[1]->id, $result[2]->userid);
        // Update request.
        $request = $DB->get_record('vm_requests', array('id' => $result[1]->id));
        $request->isscript = 1;
        $request->instanceip = '1.2.3.4';
        $DB->update_record('vm_requests', $request);
        // Test there is two requests.
        $result = $webservice->vm_requests();
        $this->assertCount(2, $result);
        $this->assertEquals($this->users[1]->id, $result[2]->userid);
        // Update request set isactive = 0.
        $request->isactive = 0;
        $DB->update_record('vm_requests', $request);
        // Test there is one requests.
        $result = $webservice->vm_requests();
        $this->assertCount(1, $result);
        $this->assertEquals($this->users[1]->id, $result[2]->userid);
        // Update request.
        $result = array_pop($result);
        $request = $DB->get_record('vm_requests', array('id' => $result->id));
        $request->isactive = 0;
        $request->isscript = 1;
        $request->instanceip = '1.2.3.4';
        $DB->update_record('vm_requests', $request);
        // Test there is no requests.
        $result = $webservice->vm_requests();
        $this->assertCount(0, $result);
    }

    /**
     * Test updating virtual machine request.
     */
    public function test_webservice_update_vm_request() {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/mod/kronossandvm/externallib.php');
        // Setup.
        $webservice = new mod_kronossandvm_external();
        $this->setupcustomfield();
        $this->setcustomfielddata($this->users[1]->id, 'test');
        // Add first request.
        $newreq = new stdClass();
        $newreq->vmid = $this->kronossandvm->id;
        $newreq->userid = $this->users[0]->id;
        $reqid = kronossandvm_add_vmrequest($this->context, $this->kronossandvm, $newreq);
        // Test updating all records.
        $requesttime = '1978-03-01 05:00:00';
        $starttime = '1979-01-09 01:00:00';
        $endtime = '1979-01-09 16:00:00';
        $result = $webservice->update_vm_request($reqid, $requesttime, $starttime, $endtime, 8, '1.2.3.4', 1, 'user1', 'password1', 1);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals($reqid, $result['id']);

        // Testing if fields are set.
        $request = $DB->get_record('vm_requests', array('id' => $reqid));
        $this->assertEquals($requesttime, userdate($request->requesttime, '%Y-%m-%d %H:%M:%S', 99, false));
        $this->assertEquals($starttime, userdate($request->starttime, '%Y-%m-%d %H:%M:%S', 99, false));
        $this->assertEquals($endtime, userdate($request->endtime, '%Y-%m-%d %H:%M:%S', 99, false));
        $this->assertEquals('password1', $request->password);

        // Test updating some records with values already assigned.
        $starttime = '1979-01-09 01:15:00';
        $result = $webservice->update_vm_request($reqid, null, $starttime);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals($reqid, $result['id']);

        $request = $DB->get_record('vm_requests', array('id' => $reqid));
        $this->assertEquals($requesttime, userdate($request->requesttime, '%Y-%m-%d %H:%M:%S', 99, false));
        $this->assertEquals($starttime, userdate($request->starttime, '%Y-%m-%d %H:%M:%S', 99, false));
        $this->assertEquals($endtime, userdate($request->endtime, '%Y-%m-%d %H:%M:%S', 99, false));
        $this->assertEquals('password1', $request->password);

        // Test updating some records with values already assigned.
        $starttime = '1979-01-09 01:25:00';
        $endtime = '2015-01-09 18:25:00';
        $result = $webservice->update_vm_request($reqid, null, $starttime, $endtime);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals($reqid, $result['id']);
        $request = $DB->get_record('vm_requests', array('id' => $reqid));
        $this->assertEquals($requesttime, userdate($request->requesttime, '%Y-%m-%d %H:%M:%S', 99, false));
        $this->assertEquals($starttime, userdate($request->starttime, '%Y-%m-%d %H:%M:%S', 99, false));
        $this->assertEquals($endtime, userdate($request->endtime, '%Y-%m-%d %H:%M:%S', 99, false));
        $this->assertEquals('password1', $request->password);

        // Adding second record.
        $newreq->userid = $this->users[1]->id;
        $reqid = kronossandvm_add_vmrequest($this->context, $this->kronossandvm, $newreq);
        $request = $DB->get_record('vm_requests', array('id' => $reqid));
        // Test updating some records with values unassigned.
        $starttime = '2017-01-09 02:25:00';
        $result = $webservice->update_vm_request($reqid, null, $starttime, null, 'instanceid', '4.3.2.1', null, 'username');
        $this->assertEquals('success', $result['status']);
        $this->assertEquals($reqid, $result['id']);
        $request = $DB->get_record('vm_requests', array('id' => $reqid));
        $this->assertEquals($starttime, userdate($request->starttime, '%Y-%m-%d %H:%M:%S', 99, false));
        $this->assertEquals('4.3.2.1', $request->instanceip);
        // Test value remains unchanged.
        $this->assertEquals(null, $request->password);
        // Test updating some record that does not exist.
        $this->setExpectedException('invalid_parameter_exception',
                'Invalid parameter value detected (Record does not exist for id: -99)');
        $result = $webservice->update_vm_request(-99, null, 77, null, 'instanceid', '4.3.2.1', null, 'username');
    }

    /**
     * Test updating virtual machine request with an invalid date.
     */
    public function test_webservice_update_vm_request_invaliddate() {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/mod/kronossandvm/externallib.php');
        // Setup.
        $webservice = new mod_kronossandvm_external();
        $this->setupcustomfield();
        $this->setcustomfielddata($this->users[1]->id, 'test');
        // Add first request.
        $newreq = new stdClass();
        $newreq->vmid = $this->kronossandvm->id;
        $newreq->userid = $this->users[0]->id;
        $reqid = kronossandvm_add_vmrequest($this->context, $this->kronossandvm, $newreq);
        // Test updating all records.
        $requesttime = '1978/03/01 05:00:00';
        $starttime = '1979-01-09 01:00:00';
        $endtime = '1979-01-09 16:00:00';
        $this->setExpectedException('invalid_parameter_exception',
                'Invalid parameter value detected (Invalid value for requesttime, date format should be YYYY-MM-DD HH:MM:SS)');
        $result = $webservice->update_vm_request($reqid, $requesttime, $starttime, $endtime, 8, '1.2.3.4', 1, 'user1', 'password1', 1);
    }

    /**
     * Test deleting virtual machine request.
     */
    public function test_webservice_delete_vm_request() {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/mod/kronossandvm/externallib.php');
        // Setup.
        $webservice = new mod_kronossandvm_external();
        $this->setupcustomfield();
        $this->setcustomfielddata($this->users[1]->id, 'test');
        // Add first request.
        $newreq = new stdClass();
        $newreq->vmid = $this->kronossandvm->id;
        $newreq->userid = $this->users[0]->id;
        $reqidone = kronossandvm_add_vmrequest($this->context, $this->kronossandvm, $newreq);
        // Add second request.
        $newreq = new stdClass();
        $newreq->vmid = $this->kronossandvm->id;
        $newreq->userid = $this->users[0]->id;
        $reqidtwo = kronossandvm_add_vmrequest($this->context, $this->kronossandvm, $newreq);
        $result = $webservice->vm_requests();
        $this->assertCount(2, $result);
        // Test deleting one record.
        $result = $webservice->delete_vm_request($reqidtwo);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals($reqidtwo, $result['id']);
        $result = $webservice->vm_requests();
        $this->assertCount(1, $result);
        // Delete first record.
        $result = $webservice->delete_vm_request($reqidone);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals($reqidone, $result['id']);
        $result = $webservice->vm_requests();
        $this->assertCount(0, $result);
    }

    /**
     * Test deleting virtual machine request that does not exist.
     */
    public function test_webservice_delete_vm_request_exception() {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/mod/kronossandvm/externallib.php');
        // Setup.
        $webservice = new mod_kronossandvm_external();
        $this->setupcustomfield();
        $this->setcustomfielddata($this->users[1]->id, 'test');
        // Add first request.
        $newreq = new stdClass();
        $newreq->vmid = $this->kronossandvm->id;
        $newreq->userid = $this->users[0]->id;
        $reqidone = kronossandvm_add_vmrequest($this->context, $this->kronossandvm, $newreq);
        // Delete request.
        $result = $webservice->delete_vm_request($reqidone);
        // Delete record that does not exist.
        $this->setExpectedException('invalid_parameter_exception',
                'Invalid parameter value detected (Record does not exist for id: '.$reqidone.')');
        $result = $webservice->delete_vm_request($reqidone);
        $this->assertEquals('fail', $result['status']);
        $this->assertEquals($reqidone, $result['id']);
    }
}
