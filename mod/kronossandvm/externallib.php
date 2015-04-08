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
 * Kronos virtual machine request web service.
 *
 * @package    mod_kronossandvm
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2015 Remote Learner.net Inc http://www.remote-learner.net
 */

require_once($CFG->libdir."/externallib.php");

/**
 * Web service to get and update request for webservices.
 */
class mod_kronossandvm_external extends external_api {

    /**
     * Returns description of method parameters for vm_requests
     *
     * @return external_function_parameters
     */
    public static function vm_requests_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * Get virtual machine requests.
     *
     * @return array Array of virtual machine requests.
     */
    public static function vm_requests() {
        global $DB;
        $sql = 'SELECT vmr.*, ud.data solutionid,
                       c.coursename, c.imageid, c.otcourseno, c.imagesource, c.imagetype,
                       c.tusername, c.tpassword, c.imagename, a.duration
                  FROM {vm_requests} vmr,
                       {user_info_data} ud,
                       {user_info_field} udf,
                       {kronossandvm} a,
                       {vm_courses} c
                 WHERE ud.userid = vmr.userid
                       AND ud.fieldid = udf.id
                       AND udf.shortname = \'solutionid\'
                       AND vmr.vmid = a.id
                       AND a.otcourseid = c.id
                       AND vmr.isactive = 1
              ORDER BY vmr.id';
        $vmrequests = $DB->get_records_sql($sql);
        // Convert Moodle unix timestamp into string date format YYYY-MM-DD HH:MM:SS.
        foreach ($vmrequests as $i => $resquest) {
            foreach (array('requesttime', 'starttime', 'endtime') as $name) {
                if (!empty($vmrequests[$i]->$name)) {
                    $vmrequests[$i]->$name = userdate($vmrequests[$i]->$name, '%Y-%m-%d %H:%M:%S');
                }
            }
        }
        return $vmrequests;
    }

    /**
     * Returns description of vm_requests return value
     *
     * @return external_multiple_structure
     */
    public static function vm_requests_returns() {
        return new external_multiple_structure(
            new external_single_structure(array(
                'id' => new external_value(PARAM_INT, 'Id of request'),
                'vmid' => new external_value(PARAM_INT, 'Virtual machine id'),
                'userid' => new external_value(PARAM_INT, 'Id of user who make request'),
                'requesttime' => new external_value(PARAM_TEXT, 'Request time of session with format YYYY-MM-DD HH:MM:SS'),
                'starttime' => new external_value(PARAM_TEXT, 'Start time of session with format YYYY-MM-DD HH:MM:SS'),
                'endtime' => new external_value(PARAM_TEXT, 'End time of session with format YYYY-MM-DD HH:MM:SS'),
                'instanceid' => new external_value(PARAM_TEXT, 'Instance id'),
                'instanceip' => new external_value(PARAM_TEXT, 'Instance ip with format #.#.#.#'),
                'isscript' => new external_value(PARAM_INT, 'Is script flag'),
                'username' => new external_value(PARAM_TEXT, 'VM Request username'),
                'password' => new external_value(PARAM_TEXT, 'VM Request password'),
                'isactive' => new external_value(PARAM_INT, 'Is active flag, 1 = Active, 0 = Inactive'),
                'solutionid' => new external_value(PARAM_TEXT, 'Customer id'),
                'coursename' => new external_value(PARAM_TEXT, 'Course name'),
                'imageid' => new external_value(PARAM_TEXT, 'Image Id'),
                'otcourseno' => new external_value(PARAM_TEXT, 'OT Course No'),
                'imagesource' => new external_value(PARAM_TEXT, 'Image source'),
                'imagetype' => new external_value(PARAM_TEXT, 'Image type'),
                'tusername' => new external_value(PARAM_TEXT, 'Username'),
                'tpassword' => new external_value(PARAM_TEXT, 'Password'),
                'imagename' => new external_value(PARAM_TEXT, 'Name of image'),
                'duration' => new external_value(PARAM_INT, 'Duration virtual machine is active.')
            ))
        );
    }

    /**
     * Returns description of method parameters for get_vm_request
     *
     * @return external_function_parameters
     */
    public static function get_vm_request_parameters() {
        return new external_function_parameters(array(
            'id' => new external_value(PARAM_INT, 'Id of request')
        ));
    }

    /**
     * Get virtual machine request.
     *
     * @return array Array of virtual machine request.
     */
    public static function get_vm_request($id) {
        global $DB;
        $sql = 'SELECT vmr.*, ud.data solutionid,
                       c.coursename, c.imageid, c.otcourseno, c.imagesource, c.imagetype,
                       c.tusername, c.tpassword, c.imagename, a.duration
                  FROM {vm_requests} vmr,
                       {user_info_data} ud,
                       {user_info_field} udf,
                       {kronossandvm} a,
                       {vm_courses} c
                 WHERE ud.userid = vmr.userid
                       AND ud.fieldid = udf.id
                       AND udf.shortname = \'solutionid\'
                       AND vmr.vmid = a.id
                       AND a.otcourseid = c.id
                       AND vmr.id = ?';
        $vmrequests = $DB->get_records_sql($sql, array($id));
        if (count($vmrequests) > 0) {
            $result = (array)array_pop($vmrequests);
            // Convert Moodle unix timestamp into string date format YYYY-MM-DD HH:MM:SS.
            foreach (array('requesttime', 'starttime', 'endtime') as $name) {
                if (!empty($result[$name])) {
                    $result[$name] = userdate($result[$name], '%Y-%m-%d %H:%M:%S');
                }
            }
            $result['status'] = 'success';
            return $result;
        }
        throw new invalid_parameter_exception(get_string('exceptionnotexists', 'mod_kronossandvm', $id));
    }

    /**
     * Returns description of get_vm_request return value
     *
     * @return external_multiple_structure
     */
    public static function get_vm_request_returns() {
        return new external_single_structure(array(
            'status' => new external_value(PARAM_TEXT, 'Status of request'),
            'id' => new external_value(PARAM_INT, 'Id of request'),
            'vmid' => new external_value(PARAM_INT, 'Virtual machine id', VALUE_DEFAULT),
            'userid' => new external_value(PARAM_INT, 'Id of user who make request', VALUE_DEFAULT),
            'requesttime' => new external_value(PARAM_TEXT, 'Request time of session', VALUE_DEFAULT),
            'starttime' => new external_value(PARAM_TEXT, 'Start time of session', VALUE_DEFAULT),
            'endtime' => new external_value(PARAM_TEXT, 'End time of session', VALUE_DEFAULT),
            'instanceid' => new external_value(PARAM_TEXT, 'Instance id', VALUE_DEFAULT),
            'instanceip' => new external_value(PARAM_TEXT, 'Instance ip', VALUE_DEFAULT),
            'isscript' => new external_value(PARAM_INT, 'Is script', VALUE_DEFAULT),
            'username' => new external_value(PARAM_TEXT, 'VM Request username', VALUE_DEFAULT),
            'password' => new external_value(PARAM_TEXT, 'VM Request password', VALUE_DEFAULT),
            'isactive' => new external_value(PARAM_INT, 'Is active flag', VALUE_DEFAULT),
            'solutionid' => new external_value(PARAM_TEXT, 'Customer id', VALUE_DEFAULT),
            'coursename' => new external_value(PARAM_TEXT, 'Course name', VALUE_DEFAULT),
            'imageid' => new external_value(PARAM_TEXT, 'Image Id', VALUE_DEFAULT),
            'otcourseno' => new external_value(PARAM_TEXT, 'OT Course No', VALUE_DEFAULT),
            'imagesource' => new external_value(PARAM_TEXT, 'Image source', VALUE_DEFAULT),
            'imagetype' => new external_value(PARAM_TEXT, 'Image type', VALUE_DEFAULT),
            'tusername' => new external_value(PARAM_TEXT, 'Username', VALUE_DEFAULT),
            'tpassword' => new external_value(PARAM_TEXT, 'Password', VALUE_DEFAULT),
            'imagename' => new external_value(PARAM_TEXT, 'Name of image', VALUE_DEFAULT),
            'duration' => new external_value(PARAM_INT, 'Duration virtual machine is active.', VALUE_DEFAULT)
        ));
    }

    /**
     * Returns description of method parameters for update_vm_requests
     *
     * @return external_function_parameters
     */
    public static function update_vm_request_parameters() {
        return new external_function_parameters(array(
            'id' => new external_value(PARAM_INT, 'Id of request'),
            'requesttime' => new external_value(PARAM_TEXT, 'Request time of session', VALUE_DEFAULT),
            'starttime' => new external_value(PARAM_TEXT, 'Start time of session', VALUE_DEFAULT),
            'endtime' => new external_value(PARAM_TEXT, 'End time of session', VALUE_DEFAULT),
            'instanceid' => new external_value(PARAM_TEXT, 'Instance id', VALUE_DEFAULT),
            'instanceip' => new external_value(PARAM_TEXT, 'Instance ip', VALUE_DEFAULT),
            'isscript' => new external_value(PARAM_INT, 'Is script', VALUE_DEFAULT),
            'username' => new external_value(PARAM_TEXT, 'VM Request username', VALUE_DEFAULT),
            'password' => new external_value(PARAM_TEXT, 'VM Request password', VALUE_DEFAULT),
            'isactive' => new external_value(PARAM_INT, 'Is active flag', VALUE_DEFAULT)
        ));
    }

    /**
     * Update virtual machine request.
     *
     * @param int $id Id of session.
     * @param string $requesttime Request time of sesion.
     * @param string $starttime Start time of session.
     * @param string $endtime End time of session.
     * @param string $instanceid Id of instance.
     * @param string $instanceip Ip of instance.
     * @param int $isscript Is request proccessed.
     * @param string $username Username.
     * @param string $password Password.
     * @param int $isactive Is request active.
     *
     * @return array Array of virtual machine requests.
     */
    public static function update_vm_request($id, $requesttime = null, $starttime = null, $endtime = 0, $instanceid = null,
            $instanceip = null, $isscript = null, $username = null, $password = null, $isactive = null) {
        global $DB;
        $request = $DB->get_record('vm_requests', array('id' => $id));
        if (empty($request)) {
            throw new invalid_parameter_exception(get_string('exceptionnotexists', 'mod_kronossandvm', $id));
        }

        $args = func_get_args();
        $i = 1;

        // Convert string date format YYYY-MM-DD HH:MM:SS into Moodle timestamp.
        foreach (array('requesttime', 'starttime', 'endtime') as $name) {
            if (!empty($args[$i])) {
                $datetime = date_create_from_format('Y-m-d H:i:s', $args[$i]);
                if (is_object($datetime)) {
                    $timestamp = make_timestamp($datetime->format('Y'), $datetime->format('m'), $datetime->format('d'),
                            $datetime->format('H'), $datetime->format('i'), $datetime->format('s'));
                    $request->$name = $timestamp;
                } else {
                    throw new invalid_parameter_exception(get_string('exceptiondate', 'mod_kronossandvm', $name));
                }
            }
            $i++;
        }

        $i = 4;
        foreach (array( 'instanceid', 'instanceip', 'isscript', 'username', 'password', 'isactive') as $name) {
            if (isset($args[$i]) && $args[$i] !== null) {
                $request->$name = $args[$i];
            }
            $i++;
        }

        $DB->update_record('vm_requests', $request);
        return array('id' => $id, 'status' => 'success');
    }

    /**
     * Returns description of update_vm_requests return value.
     *
     * @return external_single_structure
     */
    public static function update_vm_request_returns() {
        return new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'Id of request'),
            'status' => new external_value(PARAM_TEXT, 'Status of update request. "success" on successful update. Execption thrown on invalid id, date, isactive, isscript.')
        ));
    }

    /**
     * Returns description of method parameters for delete_vm_request
     *
     * @return external_function_parameters
     */
    public static function delete_vm_request_parameters() {
        return new external_function_parameters(array(
            'id' => new external_value(PARAM_INT, 'Id of request')
        ));
    }

    /**
     * Delete virtual machine request.
     *
     * @return array Array of virtual machine requests.
     */
    public static function delete_vm_request($id) {
        global $DB;
        $request = $DB->get_record('vm_requests', array('id' => $id));
        if (empty($request)) {
            throw new invalid_parameter_exception(get_string('exceptionnotexists', 'mod_kronossandvm', $id));
        }
        $DB->delete_records('vm_requests', array('id' => $id));
        return array('id' => $id, 'status' => 'success');
    }

    /**
     * Returns description of delete_vm_request return value
     *
     * @return external_single_structure
     */
    public static function delete_vm_request_returns() {
        return new external_single_structure(array(
            'id' => new external_value(PARAM_INT, 'Id of request'),
            'status' => new external_value(PARAM_TEXT, 'Status of update request. "success" on successful update. Exception thrown on error.')
        ));
    }
}
