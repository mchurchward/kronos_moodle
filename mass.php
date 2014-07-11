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
require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/lib.php');

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

// Determine whether debug is enabled.
$debug = $CFG->debugdisplay;

if (!has_capability('moodle/site:config', context_system::instance())) {
    print_error('siteadminonly');
}

// Eventually we need a way to check whether the staging site's data
// is up to date with the production site. For now, a boolean.
// $value = rand(0,1) == 1;
$datacurrent = needs_update();
if ($datacurrent) {
    $displayupdate = ' style="display: none;"';
    $displayplugins = '';
} else {
    $displayupdate = '';
    $displayplugins = ' style="display: none;"';
}

// Print header.
print($OUTPUT->header($pagetitle));

// Get filter renderers.
$output = $PAGE->get_renderer('block_rlagent');

// Print debug introduction.
if (!$datacurrent) {
  echo $output->print_update_available($USER);
}

$startpluginselect = '<div class="plugin-select"'.$displayplugins.'>';
print($startpluginselect);

$addfilters = get_string('btn_addfilters', 'block_rlagent');

// Sort bar
$sortbar = '
    <div class="sort-by row-fluid row ">
        <div class="input-prepend input-append span6">
            <div class="btn-group select-filters">
                <button id="btn-select-filter" class="btn dropdown-toggle" data-toggle="dropdown">'.
                    $addfilters
                    .'<span class="caret"></span>
                </button>
                <ul id="select-filters" class="dropdown-menu">
                    <!-- dropdown menu links -->
                    <li><a href="#">Authentication</a></li>
                    <li><a href="#">Block</a></li>
                    <li><a href="#">Enrollment</a></li>
                    <li><a href="#">Filter</a></li>
                    <li><a href="#">Format</a></li>
                    <li><a href="#">Grade Export</a></li>
                    <li><a href="#">Local</a></li>
                    <li><a href="#">Module</a></li>
                    <li><a href="#">Plagiarism</a></li>
                    <li><a href="#">Question Type</a></li>
                    <li><a href="#">Repository</a></li>
                    <li><a href="#">Theme</a></li>
                    <li><a href="#">TinyMCE</a></li>
                    <li class="divider"></li>
                    <li><a href="#">Installed</a></li>
                    <li><a href="#">Not Installed</a></li>
                    <li class="divider"></li>
                </ul>
            </div>
            <input class="" id="plugin-filter" type="text" value="Type filter and select Enter">
            <button id="clear-filters" class="btn">
                <i class="fa fa-times"></i>
                Clear Filters
            </button>
        </div>
    </div>
    <div id="labels-box" class="labels-box row-fluid" style="display: none;">
        <h4>Applied Filters</h4>
        <div id="filter-labels" class="labels">
        </div>
    </div>
    <div class="view-apply-filters-box row-fluid">
        <div id="plugin-cart" class="cart btn-group">
            <button class="btn dropdown-toggle plugin-actions" style="width: 100%;" data-toggle="dropdown">
                <i class="fa fa-check-square-o"></i>
                Selected Plugins Queue
                <span class="caret"></span>
            </button>
            <ul id="plugin-actions" class="dropdown-menu plugin-actions">
                <!-- dropdown menu links -->
                <li class="actions">
                    <a href="#" class="action">Appointments Block |
                        <i class="fa fa-plus"></i>Install
                        <i class="fa fa-times" alt="Close"></i>
                    </a>
                </li>
            </ul>
        </div>
        <button id="go-update-plugins" class="btn btn-success">
            <i class="fa fa-cogs"></i>
            Update/Install Selected Plugins
        </button>
    </div>';
print($sortbar);

// Adobe
$adobe =    '<div class="plugin well type-module">

              <!-- Install/Uninstall button and averate rating -->
              <div class="choose">
                <button type="button" class="btn btn-warning btn-uninstall" data-toggle="modal" data-target="#manage_install_modal">
                  <i class="fa fa-minus" ></i>
                  Uninstall
                </button>
                <div class="avg-rating">
                  <h5>Average Rating</h5>
                  <i class="fa fa-star"></i>
                  <i class="fa fa-star"></i>
                  <i class="fa fa-star"></i>
                  <i class="fa fa-star-o"></i>
                  <i class="fa fa-star-o"></i>
                </div>
              </div>

              <!-- Title, description, and metadata -->
              <ul class="media-list">
                <li class="media">
                  <!-- <a class="pull-left plugin-icon" href="#"> -->
                    <i class="pull-left plugin-type fa fa-rocket"></i>
                  <!-- </a> -->
                  <div class="media-body">
                    <h3 class="media-heading">Adobe Connect Pro</h3>
                    <i class="fa fa-check"></i>Plugin is supported.<br />
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis vitae nisi eget leo rutrum tempor non semper sem. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Proin lacus libero, placerat vitae ultrices ut, gravida eu lorem.</p>
                    <p>This plugin has the following dependencies. These dependencies will be automatically installed or updated when this plugin is installed or updated.
                        <ul>
                            <li>Dependency 1</li>
                            <li>Dependency 2</li>
                        </ul>


                    <div class="rate-plugin">
                      <p><strong>Add or update your rating</strong> for this plugin.</p>
                      <i class="fa fa-star-o" title="1 star"></i>
                      <i class="fa fa-star-o" title="2 stars"></i>
                      <i class="fa fa-star-o" title="3 stars"></i>
                      <i class="fa fa-star-o" title="4 stars"></i>
                      <i class="fa fa-star-o" title="5 stars"></i>
                    </div>
                  </div>


                </li>
              </ul>
            </div>';
print($adobe);

//
// Button to use when the item is selected for an action:
//
// <button type="button" class="btn btn-install btn-success" data-toggle="modal" data-target="#manage_install_modal">
//   <i class="fa fa-check-square-o"></i>
//   In Queue
// </button>

// Appointments block
$appointments =    '<div class="plugin well type-block">

                      <!-- Install/Uninstall button and averate rating -->
                      <div class="choose">
                        <button type="button" class="btn btn-upgrade btn-primary" data-toggle="modal" data-target="#manage_install_modal">
                          <i class="fa fa-share"></i>
                          Upgrade
                        </button>
                        <div class="avg-rating">
                          <h5>Average Rating</h5>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                        </div>
                      </div>

                      <!-- Title, description, and metadata -->
                      <ul class="media-list">
                        <li class="media">
                          <!-- <a class="pull-left plugin-icon" href="#"> -->
                            <i class="pull-left plugin-type fa fa-square-o"></i>
                          <!-- </a> -->
                          <div class="media-body">
                            <h3 class="media-heading">Appointments Block</h3>
                            <i class="fa fa-warning"></i>Plugin is not supported.<br />
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis vitae nisi eget leo rutrum tempor non semper sem. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Proin lacus libero, placerat vitae ultrices ut, gravida eu lorem.</p>
                            <div class="rate-plugin">
                              <p><strong>Add or update your rating</strong> for this plugin.</p>
                              <i class="fa fa-star-o" title="1 star"></i>
                              <i class="fa fa-star-o" title="2 stars"></i>
                              <i class="fa fa-star-o" title="3 stars"></i>
                              <i class="fa fa-star-o" title="4 stars"></i>
                              <i class="fa fa-star-o" title="5 stars"></i>
                            </div>
                          </div>


                        </li>
                      </ul>
                    </div>';
print($appointments);

// OpenID auth
$openid =    '<div class="plugin well type-auth">

                      <!-- Install/Uninstall button and averate rating -->
                      <div class="choose">
                        <button type="button" class="btn btn-install btn-primary" data-toggle="modal" data-target="#manage_install_modal">
                          <i class="fa fa-share"></i>
                          Upgrade
                        </button>
                        <div class="avg-rating">
                          <h5>Average Rating</h5>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                        </div>
                      </div>

                      <!-- Title, description, and metadata -->
                      <ul class="media-list">
                        <li class="media">
                          <!-- <a class="pull-left plugin-icon" href="#"> -->
                            <i class="pull-left plugin-type fa fa-key"></i>
                          <!-- </a> -->
                          <div class="media-body">
                            <h3 class="media-heading">OpenID Authentication</h3>
                            <i class="fa fa-warning"></i>Plugin is not supported.<br />
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis vitae nisi eget leo rutrum tempor non semper sem. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Proin lacus libero, placerat vitae ultrices ut, gravida eu lorem.</p>
                            <div class="rate-plugin">
                              <p><strong>Add or update your rating</strong> for this plugin.</p>
                              <i class="fa fa-star-o" title="1 star"></i>
                              <i class="fa fa-star-o" title="2 stars"></i>
                              <i class="fa fa-star-o" title="3 stars"></i>
                              <i class="fa fa-star-o" title="4 stars"></i>
                              <i class="fa fa-star-o" title="5 stars"></i>
                            </div>
                          </div>


                        </li>
                      </ul>
                    </div>';
print($openid);

// Kaltura TinyMCE
$kalturatmce =    '<div class="plugin well type-tinymce">

                      <!-- Install/Uninstall button and averate rating -->
                      <div class="choose">
                        <button type="button" class="btn btn-install btn-primary" data-toggle="modal" data-target="#manage_install_modal">
                          <i class="fa fa-plus"></i>
                          Install
                        </button>
                        <div class="avg-rating">
                          <h5>Average Rating</h5>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                        </div>
                      </div>

                      <!-- Title, description, and metadata -->
                      <ul class="media-list">
                        <li class="media">
                          <!-- <a class="pull-left plugin-icon" href="#"> -->
                            <i class="pull-left plugin-type fa fa-text-height"></i>
                          <!-- </a> -->
                          <div class="media-body">
                            <h3 class="media-heading">Kaltura TinyMCE</h3>
                            <i class="fa fa-warning"></i>Plugin is not supported.<br />
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis vitae nisi eget leo rutrum tempor non semper sem. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Proin lacus libero, placerat vitae ultrices ut, gravida eu lorem.</p>
                            <div class="rate-plugin">
                              <p><strong>Add or update your rating</strong> for this plugin.</p>
                              <i class="fa fa-star-o" title="1 star"></i>
                              <i class="fa fa-star-o" title="2 stars"></i>
                              <i class="fa fa-star-o" title="3 stars"></i>
                              <i class="fa fa-star-o" title="4 stars"></i>
                              <i class="fa fa-star-o" title="5 stars"></i>
                            </div>
                          </div>


                        </li>
                      </ul>
                    </div>';
print($kalturatmce);

// Kaltura TinyMCE
$moodlerooms =    '<div class="plugin well type-local">

                      <!-- Install/Uninstall button and averate rating -->
                      <div class="choose">
                        <button type="button" class="btn btn-install btn-primary" data-toggle="modal" data-target="#manage_install_modal">
                          <i class="fa fa-plus"></i>
                          Install
                        </button>
                        <div class="avg-rating">
                          <h5>Average Rating</h5>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                        </div>
                      </div>

                      <!-- Title, description, and metadata -->
                      <ul class="media-list">
                        <li class="media">
                          <!-- <a class="pull-left plugin-icon" href="#"> -->
                            <i class="pull-left plugin-type fa fa-home"></i>
                          <!-- </a> -->
                          <div class="media-body">
                            <h3 class="media-heading">MoodleRooms Local</h3>
                            <i class="fa fa-warning"></i>Plugin is not supported.<br />
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis vitae nisi eget leo rutrum tempor non semper sem. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Proin lacus libero, placerat vitae ultrices ut, gravida eu lorem.</p>
                            <div class="rate-plugin">
                              <p><strong>Add or update your rating</strong> for this plugin.</p>
                              <i class="fa fa-star-o" title="1 star"></i>
                              <i class="fa fa-star-o" title="2 stars"></i>
                              <i class="fa fa-star-o" title="3 stars"></i>
                              <i class="fa fa-star-o" title="4 stars"></i>
                              <i class="fa fa-star-o" title="5 stars"></i>
                            </div>
                          </div>


                        </li>
                      </ul>
                    </div>';
print($moodlerooms);

// Collapsed Topics course format
$collapsedtopics =    '<div class="plugin well type-format">

                      <!-- Install/Uninstall button and averate rating -->
                      <div class="choose">
                        <button type="button" class="btn btn-install btn-primary" data-toggle="modal" data-target="#manage_install_modal">
                          <i class="fa fa-plus"></i>
                          Install
                        </button>
                        <div class="avg-rating">
                          <h5>Average Rating</h5>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                        </div>
                      </div>

                      <!-- Title, description, and metadata -->
                      <ul class="media-list">
                        <li class="media">
                          <!-- <a class="pull-left plugin-icon" href="#"> -->
                            <i class="pull-left plugin-type fa fa-columns"></i>
                          <!-- </a> -->
                          <div class="media-body">
                            <h3 class="media-heading">Collapsed Topics Course Format</h3>
                            <i class="fa fa-warning"></i>Plugin is not supported.<br />
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis vitae nisi eget leo rutrum tempor non semper sem. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Proin lacus libero, placerat vitae ultrices ut, gravida eu lorem.</p>
                            <div class="rate-plugin">
                              <p><strong>Add or update your rating</strong> for this plugin.</p>
                              <i class="fa fa-star-o" title="1 star"></i>
                              <i class="fa fa-star-o" title="2 stars"></i>
                              <i class="fa fa-star-o" title="3 stars"></i>
                              <i class="fa fa-star-o" title="4 stars"></i>
                              <i class="fa fa-star-o" title="5 stars"></i>
                            </div>
                          </div>


                        </li>
                      </ul>
                    </div>';
print($collapsedtopics);

$datahub =    '<div class="plugin well type-enrol">

                      <!-- Install/Uninstall button and averate rating -->
                      <div class="choose">
                        <button type="button" class="btn btn-install btn-primary" data-toggle="modal" data-target="#manage_install_modal">
                          <i class="fa fa-plus"></i>
                          Install
                        </button>
                        <div class="avg-rating">
                          <h5>Average Rating</h5>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                        </div>
                      </div>

                      <!-- Title, description, and metadata -->
                      <ul class="media-list">
                        <li class="media">
                          <!-- <a class="pull-left plugin-icon" href="#"> -->
                            <i class="pull-left plugin-type fa fa-group"></i>
                          <!-- </a> -->
                          <div class="media-body">
                            <h3 class="media-heading">Datahub</h3>
                            <i class="fa fa-warning"></i>Plugin is not supported.<br />
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis vitae nisi eget leo rutrum tempor non semper sem. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Proin lacus libero, placerat vitae ultrices ut, gravida eu lorem.</p>
                            <div class="rate-plugin">
                              <p><strong>Add or update your rating</strong> for this plugin.</p>
                              <i class="fa fa-star-o" title="1 star"></i>
                              <i class="fa fa-star-o" title="2 stars"></i>
                              <i class="fa fa-star-o" title="3 stars"></i>
                              <i class="fa fa-star-o" title="4 stars"></i>
                              <i class="fa fa-star-o" title="5 stars"></i>
                            </div>
                          </div>


                        </li>
                      </ul>
                    </div>';
print($datahub);

$checklist =    '<div class="plugin well type-gradeexport">

                      <!-- Install/Uninstall button and averate rating -->
                      <div class="choose">
                        <button type="button" class="btn btn-install btn-primary" data-toggle="modal" data-target="#manage_install_modal">
                          <i class="fa fa-plus"></i>
                          Install
                        </button>
                        <div class="avg-rating">
                          <h5>Average Rating</h5>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                        </div>
                      </div>

                      <!-- Title, description, and metadata -->
                      <ul class="media-list">
                        <li class="media">
                          <!-- <a class="pull-left plugin-icon" href="#"> -->
                            <i class="pull-left plugin-type fa fa-download"></i>
                          <!-- </a> -->
                          <div class="media-body">
                            <h3 class="media-heading">Checklist Grade Export</h3>
                            <i class="fa fa-warning"></i>Plugin is not supported.<br />
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis vitae nisi eget leo rutrum tempor non semper sem. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Proin lacus libero, placerat vitae ultrices ut, gravida eu lorem.</p>
                            <div class="rate-plugin">
                              <p><strong>Add or update your rating</strong> for this plugin.</p>
                              <i class="fa fa-star-o" title="1 star"></i>
                              <i class="fa fa-star-o" title="2 stars"></i>
                              <i class="fa fa-star-o" title="3 stars"></i>
                              <i class="fa fa-star-o" title="4 stars"></i>
                              <i class="fa fa-star-o" title="5 stars"></i>
                            </div>
                          </div>


                        </li>
                      </ul>
                    </div>';
print($checklist);

$rein =    '<div class="plugin well type-filter">

                      <!-- Install/Uninstall button and averate rating -->
                      <div class="choose">
                        <button type="button" class="btn btn-install btn-primary" data-toggle="modal" data-target="#manage_install_modal">
                          <i class="fa fa-plus"></i>
                          Install
                        </button>
                        <div class="avg-rating">
                          <h5>Average Rating</h5>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                        </div>
                      </div>

                      <!-- Title, description, and metadata -->
                      <ul class="media-list">
                        <li class="media">
                          <!-- <a class="pull-left plugin-icon" href="#"> -->
                            <i class="pull-left plugin-type fa fa-filter"></i>
                          <!-- </a> -->
                          <div class="media-body">
                            <h3 class="media-heading">REIN Filter</h3>
                            <i class="fa fa-warning"></i>Plugin is not supported.<br />
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis vitae nisi eget leo rutrum tempor non semper sem. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Proin lacus libero, placerat vitae ultrices ut, gravida eu lorem.</p>
                            <div class="rate-plugin">
                              <p><strong>Add or update your rating</strong> for this plugin.</p>
                              <i class="fa fa-star-o" title="1 star"></i>
                              <i class="fa fa-star-o" title="2 stars"></i>
                              <i class="fa fa-star-o" title="3 stars"></i>
                              <i class="fa fa-star-o" title="4 stars"></i>
                              <i class="fa fa-star-o" title="5 stars"></i>
                            </div>
                          </div>


                        </li>
                      </ul>
                    </div>';
print($rein);

$turnitin =    '<div class="plugin well type-plagiarism">

                      <!-- Install/Uninstall button and averate rating -->
                      <div class="choose">
                        <button type="button" class="btn btn-install btn-primary" data-toggle="modal" data-target="#manage_install_modal">
                          <i class="fa fa-plus"></i>
                          Install
                        </button>
                        <div class="avg-rating">
                          <h5>Average Rating</h5>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                        </div>
                      </div>

                      <!-- Title, description, and metadata -->
                      <ul class="media-list">
                        <li class="media">
                          <!-- <a class="pull-left plugin-icon" href="#"> -->
                            <i class="pull-left plugin-type fa fa-eye"></i>
                          <!-- </a> -->
                          <div class="media-body">
                            <h3 class="media-heading">TurnItIn Direct</h3>
                            <i class="fa fa-warning"></i>Plugin is not supported.<br />
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis vitae nisi eget leo rutrum tempor non semper sem. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Proin lacus libero, placerat vitae ultrices ut, gravida eu lorem.</p>
                            <div class="rate-plugin">
                              <p><strong>Add or update your rating</strong> for this plugin.</p>
                              <i class="fa fa-star-o" title="1 star"></i>
                              <i class="fa fa-star-o" title="2 stars"></i>
                              <i class="fa fa-star-o" title="3 stars"></i>
                              <i class="fa fa-star-o" title="4 stars"></i>
                              <i class="fa fa-star-o" title="5 stars"></i>
                            </div>
                          </div>


                        </li>
                      </ul>
                    </div>';
print($turnitin);

$gapselect =    '<div class="plugin well type-qtype">

                      <!-- Install/Uninstall button and averate rating -->
                      <div class="choose">
                        <button type="button" class="btn btn-install btn-primary" data-toggle="modal" data-target="#manage_install_modal">
                          <i class="fa fa-plus"></i>
                          Install
                        </button>
                        <div class="avg-rating">
                          <h5>Average Rating</h5>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                        </div>
                      </div>

                      <!-- Title, description, and metadata -->
                      <ul class="media-list">
                        <li class="media">
                          <!-- <a class="pull-left plugin-icon" href="#"> -->
                            <i class="pull-left plugin-type fa fa-check-square-o"></i>
                          <!-- </a> -->
                          <div class="media-body">
                            <h3 class="media-heading">Gap Select Question Type</h3>
                            <i class="fa fa-warning"></i>Plugin is not supported.<br />
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis vitae nisi eget leo rutrum tempor non semper sem. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Proin lacus libero, placerat vitae ultrices ut, gravida eu lorem.</p>
                            <div class="rate-plugin">
                              <p><strong>Add or update your rating</strong> for this plugin.</p>
                              <i class="fa fa-star-o" title="1 star"></i>
                              <i class="fa fa-star-o" title="2 stars"></i>
                              <i class="fa fa-star-o" title="3 stars"></i>
                              <i class="fa fa-star-o" title="4 stars"></i>
                              <i class="fa fa-star-o" title="5 stars"></i>
                            </div>
                          </div>


                        </li>
                      </ul>
                    </div>';
print($gapselect);

$kalrepo =    '<div class="plugin well type-repository">

                      <!-- Install/Uninstall button and averate rating -->
                      <div class="choose">
                        <button type="button" class="btn btn-install btn-primary" data-toggle="modal" data-target="#manage_install_modal">
                          <i class="fa fa-plus"></i>
                          Install
                        </button>
                        <div class="avg-rating">
                          <h5>Average Rating</h5>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                        </div>
                      </div>

                      <!-- Title, description, and metadata -->
                      <ul class="media-list">
                        <li class="media">
                          <!-- <a class="pull-left plugin-icon" href="#"> -->
                            <i class="pull-left plugin-type fa fa-folder-open"></i>
                          <!-- </a> -->
                          <div class="media-body">
                            <h3 class="media-heading">Kaltura Repository</h3>
                            <i class="fa fa-warning"></i>Plugin is not supported.<br />
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis vitae nisi eget leo rutrum tempor non semper sem. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Proin lacus libero, placerat vitae ultrices ut, gravida eu lorem.</p>
                            <div class="rate-plugin">
                              <p><strong>Add or update your rating</strong> for this plugin.</p>
                              <i class="fa fa-star-o" title="1 star"></i>
                              <i class="fa fa-star-o" title="2 stars"></i>
                              <i class="fa fa-star-o" title="3 stars"></i>
                              <i class="fa fa-star-o" title="4 stars"></i>
                              <i class="fa fa-star-o" title="5 stars"></i>
                            </div>
                          </div>


                        </li>
                      </ul>
                    </div>';
print($kalrepo);

$essential =    '<div class="plugin well type-repository">

                      <!-- Install/Uninstall button and averate rating -->
                      <div class="choose">
                        <button type="button" class="btn btn-install btn-primary" data-toggle="modal" data-target="#manage_install_modal">
                          <i class="fa fa-plus"></i>
                          Install
                        </button>
                        <div class="avg-rating">
                          <h5>Average Rating</h5>
                          <i class="fa fa-star"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                          <i class="fa fa-star-o"></i>
                        </div>
                      </div>

                      <!-- Title, description, and metadata -->
                      <ul class="media-list">
                        <li class="media">
                          <!-- <a class="pull-left plugin-icon" href="#"> -->
                            <i class="pull-left plugin-type fa fa-picture-o"></i>
                          <!-- </a> -->
                          <div class="media-body">
                            <h3 class="media-heading">Essential Theme</h3>
                            <i class="fa fa-warning"></i>Plugin is not supported.<br />
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis vitae nisi eget leo rutrum tempor non semper sem. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Proin lacus libero, placerat vitae ultrices ut, gravida eu lorem.</p>
                            <div class="rate-plugin">
                              <p><strong>Add or update your rating</strong> for this plugin.</p>
                              <i class="fa fa-star-o" title="1 star"></i>
                              <i class="fa fa-star-o" title="2 stars"></i>
                              <i class="fa fa-star-o" title="3 stars"></i>
                              <i class="fa fa-star-o" title="4 stars"></i>
                              <i class="fa fa-star-o" title="5 stars"></i>
                            </div>
                          </div>


                        </li>
                      </ul>
                    </div>';
print($essential);

$endpluginselect = '</div>';
print($endpluginselect);


$pluginicons = '
    <div class="plugin-icon">
        <i class="fa fa-key">auth</i><br />
        <i class="fa fa-square-o">block</i><br />
        <i class="fa fa-group">enrol</i><br />
        <i class="fa fa-filter">filter</i><br />
        <i class="fa fa-columns">format</i><br />
        <i class="fa fa-download">gradeexport</i><br />
        <i class="fa fa-home">local</i><br />
        <i class="fa fa-rocket">module</i><br />
        <i class="fa fa-eye">plagiarism</i><br />
        <i class="fa fa-check-square-o">qtype</i><br />
        <i class="fa fa-folder-open">repository</i><br />
        <i class="fa fa-picture-o">theme</i><br />
        <i class="fa fa-text-height">tinymce</i><br />
    </div>
';

// print($pluginicons);

$modal = '
  <div class="modal fade" id="manage_install_modal" tabindex="-1" role="dialog" aria-labelledby="installModalLabel" aria-hidden="true">
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

// print($modal);


// Print footer.
print($OUTPUT->footer());
