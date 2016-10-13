<?php
/**
 * ELIS(TM): Enterprise Learning Intelligence Suite
 * Copyright (C) 2008-2016 Remote-Learner.net Inc (http://www.remote-learner.net)
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
 * @package    eliswidget_trackenrol
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2016 Onwards Remote Learner.net Inc http://www.remote-learner.net
 * @author     Remote-Learner.net Inc
 *
 */

define('AJAX_SCRIPT', true);

require_once(dirname(__FILE__).'/../../lib/setup.php');
require_once(elispm::file('lib/deepsight/lib/lib.php'));

require_login();

$action = required_param('action', PARAM_TEXT);

$solutionid = \eliswidget_trackenrol\savedsearch::get_user_solution_id($USER->id);
if (empty($solutionid)) {
    $context = \context_system::instance();
} else {
    $auth = get_auth_plugin('kronosportal');
    $usersetcontext = $auth->userset_solutionid_exists($solutionid);
    if (!empty($usersetcontext)) {
        $context = $usersetcontext;
    }
}
$result = array('result' => 'success');
$savedsearch = new \eliswidget_trackenrol\savedsearch(\context::instance_by_id($context->id), 'trackenrol');
switch ($action) {
    case 'search':
        $query = required_param('q', PARAM_TEXT);
        $result['results'] = $savedsearch->search($query);
    break;
    case 'save':
        $jsondata = required_param('searchdata', PARAM_RAW);
        $search = json_decode($jsondata);
        $result['id'] = $savedsearch->save($search);
        break;
    case 'delete':
        $id = required_param('id', PARAM_INT);
        $savedsearch->delete($id);
        break;
}
echo 'throw 1;'.json_encode($result);
