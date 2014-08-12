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
 * Remote Learner Update Manager - Moodle Addon Self Service page
 *
 * @package   block_rlagent
 * @copyright 2014 Remote Learner Inc http://www.remote-learner.net
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__).'/../../../config.php');
require_once(dirname(__FILE__).'/../lib.php');

require_login(SITEID);
global $USER;

$pluginname = get_string('pluginname', 'block_rlagent');
$pagetitle  = get_string('pagetitle', 'block_rlagent');

$PAGE->set_url('/blocks/rlagent/install.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title($pluginname);
$PAGE->set_pagelayout('standard');
$PAGE->navbar->add($pluginname);
$PAGE->navbar->add($pagetitle);

$PAGE->requires->css('/blocks/rlagent/css/font-awesome.min.css');
$PAGE->requires->css('/blocks/rlagent/css/bootstrap.css');

$PAGE->requires->yui_module('moodle-block_rlagent-mass', 'M.block_rlagent.init');

$strings = array(
        'actions_completed_success', 'actions_completed_failure', 'actions_in_progress', 'add',
        'add_or_update_rating_bold', 'add_or_update_rating_normal', 'ajax_request_failed', 'average_rating', 'cancel',
        'close', 'confirm', 'dependencies', 'dependency_will_be_added', 'failure', 'no_dependencies',
        'plugin_description_not_available', 'plugin_name_not_available', 'plugins_will_be_added',
        'plugins_will_be_removed', 'plugins_will_be_updated', 'preparing_actions', 'remove', 'remove_action',
        'remove_filter', 'success', 'title_auth', 'title_block', 'title_enrol', 'title_filter', 'title_format',
        'title_gradeexport', 'title_local', 'title_module', 'title_plagiarism', 'title_qtype',
        'title_repository', 'title_theme', 'title_tinymce',
        'to_be_added', 'to_be_removed', 'to_be_updated', 'update'
);
$PAGE->requires->strings_for_js($strings, 'block_rlagent');

// Determine whether debug is enabled.
$debug = $CFG->debugdisplay;

if (!has_capability('moodle/site:config', context_system::instance())) {
    print_error('siteadminonly');
}

// Print header.
print($OUTPUT->header($pagetitle));

// Get filter renderers.
$output = $PAGE->get_renderer('block_rlagent');

// Eventually we need a way to check whether the staging site's data
// is up to date with the production site. For now, a boolean.
$stale = block_rlagent_needs_update();
$displayplugins = '';
if ($stale) {
    echo $output->print_update_available($USER);
    $displayplugins = ' style="display: none;"';
}

$addfilters = get_string('btn_addfilters', 'block_rlagent');

$addontypes = array(
        'auth', 'block', 'enrol', 'filter', 'format', 'gradeexport', 'local', 'plagiarism', 'qtype', 'repository',
        'theme', 'tinymce'
);

$filters = array('<!-- dropdown menu links -->');
foreach ($addontypes as $type) {
    $filters[] = '<li data-filter-mode="type" data-filter-refine="'.$type.'"><a href="#">'.get_string("title_{$type}", 'block_rlagent').'</a></li>';
}
$filters[] = '<li class="divider"></li>';
$filters[] = '<li data-filter-mode="status" data-filter-refine="installed"><a href="#">'.get_string("title_installed", 'block_rlagent').'</a></li>';
$filters[] = '<li data-filter-mode="status" data-filter-refine="notinstalled"><a href="#">'.get_string("title_not_installed", 'block_rlagent').'</a></li>';
$filters[] = '<li data-filter-mode="status" data-filter-refine="updateable"><a href="#">'.get_string("title_updateable", 'block_rlagent').'</a></li>';
$filters[] = '<li class="divider"></li>';

$filterhtml = implode("\n", $filters);

// Sort bar
$sortbar = '
    <div id="filter-placeholder"></div>
    <div id="filter-form" class="filter-form filter-position-relative">
        <div class="sort-by row-fluid row ">
            <div class="input-prepend input-append span6">
                <div class="btn-group select-filters">
                    <button id="btn-select-filter" class="btn dropdown-toggle" data-toggle="dropdown">'.
                        $addfilters
                        .'<span class="caret"></span>
                    </button>
                    <ul id="select-filters" class="dropdown-menu">'.
                        $filterhtml
                    .'</ul>
                </div>
                <input class="" id="plugin-filter" type="text" value="'.get_string('type_filter', 'block_rlagent').'">
                <button id="clear-filters" class="btn">
                    <i class="fa fa-times"></i>
                    '.get_string('clear_filters', 'block_rlagent').'
                </button>
            </div>
        </div>
        <div id="labels-box" class="labels-box row-fluid" style="display: none;">
            <h4>'.get_string('applied_filters', 'block_rlagent').'</h4>
            <div id="filter-labels" class="labels">
            </div>
        </div>
        <div class="view-apply-filters-box row-fluid">
            <div id="plugin-cart" class="cart btn-group">
                <button class="btn dropdown-toggle plugin-actions disabled" style="width: 100%;" data-toggle="dropdown">
                    <i class="fa fa-check-square-o"></i>
                    '.get_string('selected_plugins_queue', 'block_rlagent').'
                    <span class="caret"></span>
                </button>
                <ul id="plugin-actions" class="dropdown-menu plugin-actions">
                    <!-- dropdown menu links -->
                </ul>
            </div>
            <button id="go-update-plugins" class="btn btn-success disabled">
                <i class="fa fa-cogs"></i>
                '.get_string('update_selected_plugins', 'block_rlagent').'
            </button>
            <div id="trust-filter-box" class="checkbox pull-right">
                <input id="trust-filter" type="checkbox" value="1" checked="checked"/>
                '.get_string('trusted_addons_only', 'block_rlagent').'
            </div>
        </div>
    </div>
    <div class="plugins"></div>';

$pluginselect = "<div class=\"plugin-select\"{$displayplugins}>{$sortbar}</div>";
print($pluginselect);

$modal = '
  <div class="modal fade" id="manage_actions_modal" tabindex="-1" role="dialog" aria-labelledby="installModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title" id="myModalLabel"></h4>
        </div>
        <div class="modal-body">

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>';

print($modal);


// Print footer.
print($OUTPUT->footer());
