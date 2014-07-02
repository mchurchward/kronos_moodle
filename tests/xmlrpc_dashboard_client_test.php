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
 * Remote Learner Update Manager - Plugin data provider page
 *
 * @package   block_rlagent
 * @copyright 2014 Remote Learner Inc http://www.remote-learner.net
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__).'/../lib/xmlrpc_dashboard_client.php');

defined('MOODLE_INTERNAL') || die();

class block_rlagent_xmlrpc_dashboard_client_testcase extends advanced_testcase {
    /** @var object The test version of the dashboard client */
    private $client;
    /**
     * Do common setup tasks
     *
     * @see PHPUnit_Framework_TestCase::setUp()
     */
    public function setUp() {
        $client = $this->getMockBuilder('block_rlagent_xmlrpc_dashboard_client')
                        ->setMethods(array('get_webservices_config', 'send_request'))
                        ->getMock();
        $client->expects($this->any())
               ->method('get_webservices_config')
               ->will($this->returnValue(true));
        $client->expects($this->any())
               ->method('send_request')
               ->will($this->returnValue('10-4 Good Buddy'));

        $this->client = $client;
    }

    public function test_get_addon_data() {
        $result = $this->client->get_addon_data();
        $this->assertEquals('10-4 Good Buddy', $result);
    }

    public function test_get_group_data() {
        $result = $this->client->get_group_data();
        $this->assertEquals('10-4 Good Buddy', $result);
    }
}