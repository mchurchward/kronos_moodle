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
 * Remote Learner Update Manager - Moodle Addon Self Service User Interface code
 *
 * @package   block_rlagent
 * @copyright 2014 Remote Learner Inc http://www.remote-learner.net
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.block_rlagent = M.block_rlagent || {};
M.block_rlagent = {
    /* @var object Contains the actions the user has selected. */
    data_actions: {
        "add": {},
        "remove": {},
        "update": {}
    },

    /* @var object Contains the list of addons that the user is allowed to manage. */
    data_addons: {},

    /* @var object The modal that displays the confirmation message for actions. */
    plugin_modal: null,

    /* @var object Placeholder for last clicked node.  Used to deduplicate rate events */
    plugin_rate_node: null,

    /* @var object Contains the list of plugin types that are current supported with icons. */
    plugin_types: {
        "block": {"type":"block", "title":M.util.get_string('title_block', 'block_rlagent'), "icon":"fa-square-o"},
        "mod": {"type":"mod", "title":M.util.get_string('title_module', 'block_rlagent'), "icon":"fa-rocket"},
        "auth": {"type":"auth", "title":M.util.get_string('title_auth', 'block_rlagent'), "icon":"fa-key"},
        "enrol": {"type":"enrol", "title":M.util.get_string('title_enrol', 'block_rlagent'), "icon":"fa-group"},
        "filter": {"type":"filter", "title":M.util.get_string('title_filter', 'block_rlagent'), "icon":"fa-filter"},
        "format": {"type":"format", "title":M.util.get_string('title_format', 'block_rlagent'), "icon":"fa-columns"},
        "gradeexport": {"type":"gradeexport", "title":M.util.get_string('title_gradeexport', 'block_rlagent'), "icon":"fa-download"},
        "local": {"type":"local", "title":M.util.get_string('title_local', 'block_rlagent'), "icon":"fa-home"},
        "qtype": {"type":"qtype", "title":M.util.get_string('title_qtype', 'block_rlagent'), "icon":"fa-check-square-o"},
        "repository": {"type":"repository", "title":M.util.get_string('title_repository', 'block_rlagent'), "icon":"fa-folder-open"},
        "theme": {"type":"theme", "title":M.util.get_string('title_theme', 'block_rlagent'), "icon":"fa-picture-o"},
        "tinymce": {"type":"tinymce", "title":M.util.get_string('title_tinymce', 'block_rlagent'), "icon":"fa-text-height"},
        "plagiarism": {"type":"plagiarism", "title":M.util.get_string('title_plagiarism', 'block_rlagent'), "icon":"fa-eye"}
    },

    /**
     * Fetch the addons from the addons script
     */
    plugin_fetch_addons: function() {
        Y.log('plugin_fetch_addons');
        YUI().use("io-base", function(Y) {
            $url = M.cfg.wwwroot + '/blocks/rlagent/mass/addons.php?type=addonlist';
            Y.log($url);

            /**
             * Handler for returned "complete" status
             */
            function complete() {
                Y.log('complete');
            }

            /**
             * Handler for returned "success" status
             */
            function success(id, o) {
                Y.log('success');
                var $response = null;
                YUI().use('json-parse', function (Y) {
                    try {
                        $response = Y.JSON.parse(o.responseText);
                    }
                    catch (e) {
                        Y.log("Parsing failed.");
                    }
                });
                M.block_rlagent.data_addons = $response;
                Y.log(M.block_rlagent.data_addons);
                M.block_rlagent.plugin_write_addons();
            }

            /**
             * Handler for returned "failure" status
             *
             * @param array args Arguments passed to the failure function
             */
            function failure(args) {
                Y.log('Failure: '+args[0]);
            }
            Y.on('io:complete', complete, Y, []);
            Y.on('io:success', success, Y, []);
            Y.on('io:failure', failure, Y, [M.util.get_string('ajax_request_failed', 'block_rlagent')]);
            $addons = Y.io($url);
        });
    },

    /**
     * Write the addons to the list array of the page.
     */
    plugin_write_addons: function() {
        // Inject addon HTML into page.
        Y.Object.each(M.block_rlagent.data_addons, function($value, $key) {
            $displayname = $value.display_name ? $value.display_name : M.util.get_string('plugin_name_not_available', 'block_rlagent');
            $description = $value.description ? $value.description :  M.util.get_string('plugin_description_not_available', 'block_rlagent');
            $myrating = $value.myrating ? $value.myrating : 0;
            $rating = $value.rating ? $value.rating : 0;
            $installed = $value.installed ? $value.installed : false;
            $updateable = $value.upgradeable ? $value.upgradeable : false;
            $cached = $value.cached ? $value.cached : false;
            $type = $value.type ? $value.type : '1';
            $typeclass = ' type-' + $type; // M.block_rlagent.plugin_types[$type].type;
            $nameclass = ' name-' + String($value.name).replace(' ', '_');
            $datakey = 'data-key="' + String($key).replace(' ', '_') + '" ';
            $datainstalled = 'data-installed="' + String($installed).replace(' ', '_') + '" ';
            $dataupdateable = 'data-updateable="' + String($updateable).replace(' ', '_') + '" ';
            $datacached = 'data-cached="' + String($cached).replace(' ', '_') + '" ';
            $datatype = 'data-type="' + String($type).replace(' ', '_') + '" ';

            // Install button options.
            $installstring = M.util.get_string('add', 'block_rlagent');
            $installclass = 'btn-install btn-success';
            $installicon = 'fa fa-plus';
            $installdataaction = 'data-action="add"';
            if ($installed) {
                // If plugin is installed, show "Remove".
                $installstring = M.util.get_string('remove', 'block_rlagent');
                $installclass = 'btn-remove btn-danger';
                $installicon = 'fa fa-times';
                $installdataaction = 'data-action="remove"';
            }

            // Update button options.
            $updatedisabled = 'display:none;';
            // If plugin is not updateable,
            if ($updateable) {
                $updatedisabled = '';
            }

            // This will have to be updated when we start sending the plugin status.
            $buttonmarkup = '<button type="button" class="btn ' + $installclass +
                '"' + $installdataaction + '>';
            $buttonmarkup += '<i class="' + $installicon + '"></i>';
            $buttonmarkup += $installstring;
            $buttonmarkup += '</button>';

            // Update button markup.
            $upbuttonmarkup = '<button type="button" style="' + $updatedisabled + '" class="btn btn-update btn-primary"'
                + ' data-action="update">';
            $upbuttonmarkup += '<i class="fa fa-level-up"></i>';
            $upbuttonmarkup += M.util.get_string('update', 'block_rlagent');
            $upbuttonmarkup += '</button>';

            // Build markup for plugin average rating.
            $ratingmarkup = '<div class="avg-rating"><h5>'+M.util.get_string('average_rating', 'block_rlagent')+'</h5>';
            for (i = 1; i < 6; i++) {
                if (i <= $rating) {
                    $ratingmarkup += '<i class="fa fa-star"></i>';
                } else {
                    $ratingmarkup += '<i class="fa fa-star-o"></i>';
                }
            }
            $ratingmarkup += '</div>';

            // Build markup for plugin title and description.
            $itemmarkup = '<ul class="media-list"><li class="media">';
            $itemmarkup += '<i class="pull-left plugin-type fa '+M.block_rlagent.plugin_types[$type].icon+'"></i>';
            $itemmarkup += '<div class="media-body">';
            $itemmarkup += '<h3 class="media-heading">'+$displayname+'</h3>';
            $itemmarkup += '<p>'+$description+'</p>';
            $itemmarkup += '<div class="rate-plugin">';
            $itemmarkup += '<p><strong>'+M.util.get_string('add_or_update_rating_bold', 'block_rlagent')+
                           '</strong>'+M.util.get_string('add_or_update_rating_normal', 'block_rlagent')+'</p>';
            for (i = 1; i < 6; i++) {
                if (i <= $myrating) {
                    $itemmarkup += '<i class="fa fa-star" title="'+i+' stars"></i>';
                } else {
                    $itemmarkup += '<i class="fa fa-star-o" title="'+i+' stars"></i>';
                }
            }
            $itemmarkup += '</div>';
            $itemmarkup += '</div>';
            $itemmarkup += '</li></ul>';

            $html = '<div class="plugin well'+$typeclass+$nameclass+'" '+$datakey+
                    $datainstalled+$dataupdateable+$datacached+$datatype+'>';
            $html += '<div class="choose">';
            $html += $buttonmarkup;
            $html += $upbuttonmarkup;
            $html += $ratingmarkup;
            $html += '</div>';

            $html += $itemmarkup;

            $html += '</div>';

            Y.one('.plugin-select .plugins').insert($html);
        });

        // Init rate plugins *after* the plugins are printed to the page.
        M.block_rlagent.plugin_rate_plugins();
        // Init action buttons *after* the plugins are printed to the page.
        M.block_rlagent.plugin_buttons();
    },

    /**
     * Perform the actions required when the user skips a site update.
     */
    skip_update_init: function() {
        // Actions performed if user skips site update.
        $button = Y.one('#skipupdate');
        Y.one('#skipupdate').on('click', function() {
            Y.one('.site-update').hide(true, function() {
                Y.one('.plugin-select').setStyle('display', 'block').hide().show(true);
            });
            M.block_rlagent.plugin_select_init();
        });
    },

    /**
     * Perform the actions required when the user choose to update the site.
     */
    update_site_init: function() {
        // Actions performed if user chooses to update site.
        Y.one('#doupdate').on('click', function(e) {
            // Disable button.
            e.target.setAttribute('disabled');
            // Show spinner.
            Y.one('.site-update-spinner').show(true);
            // Get email to contact when update is complete.
            $useremail = Y.one('.useremail').get('text');
            // Call site update function
            // TODO: AJAX request, sending $useremail as a parameter.
            // TODO?: Reload page?
        });
    },

    /**
     * Initialize the filtering and rating systems.
     */
    plugin_select_init: function() {
        // Inits functions for filtering and rating plugins.
        // Called when the plugins list and filter controls are first shown.
        M.block_rlagent.plugin_select_filter();
        M.block_rlagent.plugin_clear_filters();
        M.block_rlagent.plugin_input_filter();
        M.block_rlagent.plugin_dropdown_visibility();
        M.block_rlagent.plugin_fetch_addons();
        M.block_rlagent.plugin_filter_scroll();
    },

    /**
     * Capitalise the first letter of the string.
     *
     * @param string The string that needs it's first letter capitalised.
     */
    plugin_capitalise_firstletter: function(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    },

    /**
     * Add a filter to the UI.
     *
     * @param string $filterstring The filter string
     * @param string $mode The filter mode
     * @param string $refine The refine value
     */
    plugin_add_filter: function($filterstring, $mode, $refine) {
        // Called when a filter is added by any means.
        // If filters list not displayed, display it.
        $labelsbox = Y.one('#labels-box');
        if ($labelsbox.getStyle('display') === "none") {
            $labelsbox.setStyle('display', 'block');
        }
        // Get all of the displayed filters.
        $filters = Y.all('#filter-labels span.badge');
        $filterlist = $filters.get('text');
        // Gather all displayed filter labels into an array.
        var $labelarr = [];
        Y.Array.each($filterlist, function(value) {
            $labelarr.push(value.trim());
        });
        $prefix = M.block_rlagent.plugin_capitalise_firstletter($mode) + ' | ';
        // If the filter is not yet displayed, display it.
        if ($labelarr.indexOf($prefix + $filterstring) <= -1) {
            $labelmarkup = '<span class="badge" data-filter-mode="' + $mode
                +'" data-filter-refine="' + $refine
                + '">' + $prefix + $filterstring
                + '<i class="fa fa-times" alt="'+M.util.get_string('remove_filter', 'block_rlagent')+'"></i></span>';
            if ($filters.length >= 1) {
                // If there are other filters, add after the last one.
                $item = $filters.item($filterlist.length -1).insert($labelmarkup, 'after');
            } else {
                // Otherwise, add inside the parent container.
                $item = Y.one('#filter-labels').insert($labelmarkup);
            }
            M.block_rlagent.plugin_filter_plugins();
            M.block_rlagent.plugin_remove_filter();
            M.block_rlagent.plugin_show_filterblock();
            return $item;
        } else {
            return false;
        }
    },

    /**
     * Code that is called when a filter is clicked on.
     */
    plugin_select_filter: function() {
        // Called when filter in filter dropdown is selected.
        Y.all('ul#select-filters li').on('click', function(e) {
            // Capture target.
            $target = Y.one(e.currentTarget);
            // Capture the content of the li.
            $label = $target.one('a').get('text');
            $mode = $target.getAttribute('data-filter-mode');
            Y.log('$mode = '+$mode);
            $refine = $target.getAttribute('data-filter-refine');
            Y.log('$refine = '+$refine);
            M.block_rlagent.plugin_add_filter($label, $mode, $refine);
        });
    },

    /**
     * Scripts the behavior of the filter dropdown.
     * This is done because there are some disprepancies between
     * Moodle's YUI revamp of the dropdown (directed mainly at the
     * Moodle custom menu), and the default Bootstrap functionality
     * necessary for this plugin filtering mechanism.
     */
    plugin_dropdown_visibility: function() {
        $filter_ul = Y.one('ul#select-filters');
        $filter_li = Y.all('ul#select-filters li');
        $filter_btn = Y.one('#btn-select-filter');

        $filter_btn.on('click', function() {
            if ($filter_ul.getStyle('display') === 'block') {
                // Y.log('display is block');
                $filter_ul.setStyle('display', 'none');
                $filter_ul.get('parentNode').toggleClass('open');
            } else {
                // Y.log('display is none');
                $filter_ul.setStyle('display', 'block');
                $filter_ul.get('parentNode').toggleClass('open');
            }
        });

        $filter_li.on('click', function() {
            // Y.log('filter li click');
            if ($filter_ul.getStyle('display') === 'block') {
                // Y.log('display is block');
                $filter_ul.setStyle('display', 'none');
                $filter_ul.get('parentNode').toggleClass('open');
            } else {
                // Y.log('display is none');
                $filter_ul.setStyle('display', 'block');
                $filter_ul.get('parentNode').toggleClass('open');
            }
        });

        $filter_ul.on('mouseleave', function() {
            $filter_ul.setStyle('display', 'none');
            $filter_ul.get('parentNode').toggleClass('open');
        });
    },

    /**
     * Scripts the behavior of the filter input field.
     */
    plugin_input_filter: function() {
        $input = Y.one('input#plugin-filter');
        $inputengaged = false;

        // Enter key adds the input contents to filter list.
        function enter_key_press () {
            $filter = Y.one('input#plugin-filter').get('value');
            if ($filter.length >= 1) {
                M.block_rlagent.plugin_add_filter($filter, 'string', $filter);
                $input.set('value', '');
            }
            M.block_rlagent.plugin_show_filterblock();
        }

        // Select of input clears default input contents.
        $input.on('click', function() {
            $input.set('value', '');
            $inputengaged = true;
            $input.on('blur', function() {
                $inputengaged = false;
            });
            $input.on('key', enter_key_press, 'enter');
        });
    },

    /**
     * Clear the added filters.
     */
    plugin_clear_filters: function() {
        $clearbutton = Y.one('button#clear-filters');
        $clearbutton.on('click', function() {
            Y.all('#filter-labels span.badge').remove(true);
            M.block_rlagent.plugin_filter_plugins();
            M.block_rlagent.plugin_hide_filterblock();
        });
    },

    /**
     * Displays the filter block if there are filters.
     */
    plugin_show_filterblock: function() {
        if (Y.all('#filter-labels span.badge').size() >= 1) {
            Y.one('#labels-box').show(true);
        }
    },

    /**
     * If there are no filters, hide the filter block.
     */
    plugin_hide_filterblock: function() {
        if (Y.all('#filter-labels span.badge').size() <= 0) {
            Y.one('#labels-box').hide(true);
            // Also re-display all plugins.
            Y.all('.plugins .plugin.well').show(true);
        }
    },

    /**
     * Remove a single filter.
     */
    plugin_remove_filter: function() {
        $removebutton = Y.all('#filter-labels i.fa-times');
        $removebutton.on('click', function(e) {
            $target = e.target.get('parentNode').remove(true);
            M.block_rlagent.plugin_filter_plugins();
            M.block_rlagent.plugin_hide_filterblock();
        });
    },

    /**
     * Handle the rating of plugins
     */
    plugin_rate_plugins: function() {


        /**
         * Handle the event when someone clicks on a star
         */
        function starclick(e) {
            // Y.log('starclick function');
            e.preventDefault();
            $this = e.target;

            // Required because for whatever reason the click event is calling the callback
            // function hundreds of times for each click.
            if (!M.block_rlagent.plugin_rate_node || $this !== M.block_rlagent.plugin_rate_node) {
                // If no existing node, or $this is a new node, start over.
                Y.log('M.block_rlagent.plugin_rate_node is null');
                // Y.log($this);
                // Y.log(M.block_rlagent.plugin_rate_node);
                M.block_rlagent.plugin_rate_node = $this;
                // Y.log('M.block_rlagent.plugin_rate_node after saved $this into it:');
                // Y.log(M.block_rlagent.plugin_rate_node);

                // Get full set of clicked star plus siblings.
                $starset = M.block_rlagent.plugin_rate_node.get('parentNode').get('children').filter('.fa');

                // Get star index.
                $clickedindex = $starset.indexOf(M.block_rlagent.plugin_rate_node);

                // Fill star and all stars of index below it.
                $fillstars = $starset.slice(0, $clickedindex + 1);

                // Empty all stars with index above it.
                $emptystars = $starset.slice($clickedindex + 1, $starset.size() + 1);

                // Rating is 1-based: 1-5.
                var $rating = $clickedindex + 1;
                // Get the addon object key to send with rating call.
                var $addon = M.block_rlagent.plugin_rate_node.ancestor('.plugin.well').getAttribute('data-key'); // Get addon name.

                $starset.each(function($star) {
                    // Remove existing classes if present.
                    if($star.hasClass('fa-star-o')) {
                        $star.removeClass('fa-star-o');
                    }
                    if($star.hasClass('fa-star')) {
                        $star.removeClass('fa-star');
                    }

                    // Apply appropriate classes and call send rating.
                    if ($starset.indexOf($star) === $starset.size() - 1) {
                        $emptystars.addClass('fa-star-o');
                        $fillstars.addClass('fa-star');
                        M.block_rlagent.plugin_send_rating($addon, $rating);
                    }
                });
            } else {
                // If node has already been acted upon, exit.
                Y.log('Conditions not met. M.block_rlagent.plugin_rate_node not null or matches existing e.target.');
                return false;
            }
        }

        Y.one('.plugins').delegate('click', starclick, '.rate-plugin .fa-star-o, .rate-plugin .fa-star');
    },

    /**
     * Update dropdown listing plugin actions.
     */
    plugin_update_action_dropdown: function() {
        Y.log('update_dropdown');

        $ul = Y.one('ul#plugin-actions');
        $dropbtn = Y.one('button.plugin-actions');
        $updatebtn = Y.one('#go-update-plugins');

        Y.log($ul);

        /**
         * Remove an action from the action list.
         */
        function remove_action(e) {
            e.preventDefault;
            $this = e.target;
            Y.log('remove_action');
            Y.log(e.target);
            // Fetch key.
            $key = $this.ancestor('li').getAttribute('data-key');
            // Fetch action.
            $action = $this.ancestor('li').getAttribute('data-action');
            // Remove list item.
            $this.ancestor('li').remove(true);
            // Remove action from object.
            delete M.block_rlagent.data_actions[$action][$key];
            Y.log('attempted to delete relevant action node, displaying new data_actions object below');
            Y.log(M.block_rlagent.data_actions);

            // Reactivate plugin button pertaining to action.
            $btn = Y.one('.plugin.well[data-key="' + $key + '"] button[data-action="' + $action + '"]');
            if ($btn.hasClass('disabled')) {
                $btn.removeClass('disabled');
            }

            // If no actions, disable buttons.
            if (Object.keys(M.block_rlagent.data_actions.add).length === 0 &&
                Object.keys(M.block_rlagent.data_actions.remove).length === 0 &&
                Object.keys(M.block_rlagent.data_actions.update).length === 0 ) {

                if (!$dropbtn.hasClass('disabled')) {
                    $dropbtn.addClass('disabled');
                }
                if (!$updatebtn.hasClass('disabled')) {
                    $updatebtn.addClass('disabled');
                }
            }
        }

        // Remove all click listeners in the list as they will have to be redone
        // and we don't want duplicates.
        $ul.detach('click');

        // If the data_actions object is empty, remove list items and disable buttons.
        if (Object.keys(M.block_rlagent.data_actions.add).length === 0 &&
            Object.keys(M.block_rlagent.data_actions.remove).length === 0 &&
            Object.keys(M.block_rlagent.data_actions.update).length === 0 ) {

            // Then remove all list items.
            $items = $ul.children(li);
            if ($items.size()) {
                $ul.empty();
            }

            // And disable buttons.
            if (!$dropbtn.hasClass('disabled')) {
                $dropbtn.addClass('disabled');
            }
            if (!$updatebtn.hasClass('disabled')) {
                $updatebtn.addClass('disabled');
            }

            return false;
        }

        // Address all add actions.
        if (Object.keys(M.block_rlagent.data_actions.add).length > 0) {
            // Empty all add plugins
            $ul.all('[data-action="add"]').remove(true);
            // Insert new addon plugins
            Y.Object.each(M.block_rlagent.data_actions.add, function($value, $key) {
                // Build markup for individual list item.
                var $markup;
                $markup = '<li data-action="add" ';
                $markup += 'data-key="' + $key + '">';
                $markup += '<i class="fa fa-plus" alt="'+M.util.get_string('to_be_added', 'block_rlagent')+'"></i>';
                $markup += M.block_rlagent.data_addons[$key].display_name; // Addon name
                $markup += '<i class="fa fa-times-circle rm-action" alt="'+M.util.get_string('remove_action', 'block_rlagent')+'"></i>';
                $markup += '</li>';
                // Insert markup.
                $ul.append($markup);
            });

            // If buttons are disabled, enable them.
            if ($dropbtn.hasClass('disabled')) {
                Y.log('dropbtn has disabled class');
                $dropbtn.removeClass('disabled');
            }
            if ($updatebtn.hasClass('disabled')) {
                Y.log('updatebtn has disabled class');
                $updatebtn.removeClass('disabled');
            }
        }

        // Address all remove actions.
        if (Object.keys(M.block_rlagent.data_actions.remove).length > 0) {
            // Empty all add plugins
            $ul.all('[data-action="remove"]').remove(true);
            // Insert new addon plugins
            Y.Object.each(M.block_rlagent.data_actions.remove, function($value, $key) {
                // Build markup for individual list item.
                var $markup;
                $markup = '<li data-action="remove" ';
                $markup += 'data-key="' + $key + '">';
                $markup += '<i class="fa fa-times" alt="'+M.util.get_string('to_be_added', 'block_rlagent')+'"></i>';
                $markup += M.block_rlagent.data_addons[$key].display_name; // Addon name
                $markup += '<i class="fa fa-times-circle rm-action" alt="'+M.util.get_string('remove_action', 'block_rlagent')+'"></i>';
                $markup += '</li>';
                // Insert markup.
                $ul.append($markup);
            });

            // If buttons are disabled, enable them.
            if ($dropbtn.hasClass('disabled')) {
                Y.log('dropbtn has disabled class');
                $dropbtn.removeClass('disabled');
            }
            if ($updatebtn.hasClass('disabled')) {
                Y.log('updatebtn has disabled class');
                $updatebtn.removeClass('disabled');
            }
        }

        // Address all update actions.
        if (Object.keys(M.block_rlagent.data_actions.update).length > 0) {
            // Empty all add plugins
            $ul.all('[data-action="update"]').remove(true);
            // Insert new addon plugins
            Y.Object.each(M.block_rlagent.data_actions.update, function($value, $key) {
                // Build markup for individual list item.
                var $markup;
                $markup = '<li data-action="update" ';
                $markup += 'data-key="' + $key + '">';
                $markup += '<i class="fa fa-level-up" alt="'+M.util.get_string('to_be_updated', 'block_rlagent')+'"></i>';
                $markup += M.block_rlagent.data_addons[$key].display_name; // Addon name
                $markup += '<i class="fa fa-times-circle rm-action" alt="'+M.util.get_string('remove_action', 'block_rlagent')+'"></i>';
                $markup += '</li>';
                // Insert markup.
                $ul.append($markup);
            });

            // If buttons are disabled, enable them.
            if ($dropbtn.hasClass('disabled')) {
                Y.log('dropbtn has disabled class');
                $dropbtn.removeClass('disabled');
            }
            if ($updatebtn.hasClass('disabled')) {
                Y.log('updatebtn has disabled class');
                $updatebtn.removeClass('disabled');
            }
        }

        // Redo all click listeners in the list.
        $ul.delegate('click', remove_action, '.rm-action');

        Y.log('add length');
        Y.log(Object.keys(M.block_rlagent.data_actions.add).length);
    },

    /**
     * Handle the clicking of plugin buttons
     */
    plugin_buttons: function() {
        /**
         * Handle the clicking of the install button
         *
         * @param event e The event triggered by clicking the install button
         */
        function install_click(e) {
            e.preventDefault();
            $this = e.target;
            Y.log($this);
            // Get the addon object key to send with rating call.
            var $addon = $this.ancestor('.plugin.well').getAttribute('data-key'); // Get addon name.

            Y.log('Install button clicked for '+$addon);
            // Add addon to actions array.
            M.block_rlagent.data_actions.add[$addon] = $addon;
            Y.log(M.block_rlagent.data_actions);

            // Disable button for this plugin.
            // TODO: Re-enable for this plugin if the action is removed from the actions list.
            $this.addClass('disabled');
            M.block_rlagent.plugin_update_action_dropdown();
        }

        /**
         * Handle the clicking of the remove button
         *
         * @param event e The event triggered by clicking the remove button
         */
        function remove_click(e) {
            e.preventDefault();
            $this = e.target;
            // Get the addon object key to send with rating call.
            var $addon = $this.ancestor('.plugin.well').getAttribute('data-key'); // Get addon name.

            Y.log('Remove button clicked for '+$addon);
            M.block_rlagent.data_actions.remove[$addon] = $addon;

            // Disable button for this plugin.
            $this.addClass('disabled');
            M.block_rlagent.plugin_update_action_dropdown();
        }

        /**
         * Handle the clicking of the update button
         *
         * @param event e The event triggered by clicking the update button
         */
        function update_click(e) {
            e.preventDefault();
            $this = e.target;
            // Get the addon object key to send with rating call.
            var $addon = $this.ancestor('.plugin.well').getAttribute('data-key'); // Get addon name.

            Y.log('Update button clicked for '+$addon);
            M.block_rlagent.data_actions.update[$addon] = $addon;

            // Disable button for this plugin.
            $this.addClass('disabled');
            M.block_rlagent.plugin_update_action_dropdown();
        }

        /**
         * Handle the clicking of the do-the-actions button
         *
         * @param event e The event triggered by clicking the button
         */
        function do_actions(e) {
            e.preventDefault();

            var $confirm = '<ul>'+M.util.get_string('plugins_will_be_added', 'block_rlagent');
            var plugin;
            for (plugin in M.block_rlagent.data_actions.add) {
                $confirm += '<li>' + plugin + '</li>';
            }
            $confirm += '</ul>';

            $confirm += '<ul>'+M.util.get_string('plugins_will_be_updated', 'block_rlagent');
            for (plugin in M.block_rlagent.data_actions.update) {
                $confirm += '<li>' + plugin + '</li>'; // $confirm += plugin+"\n";
            }
            $confirm += '</ul>';

            $confirm += '<ul>'+M.util.get_string('plugins_will_be_removed', 'block_rlagent');
            for (plugin in M.block_rlagent.data_actions.remove) {
                $confirm += '<li>' + plugin + '</li>'; // $confirm += plugin+"\n";
            }
            $confirm += '</ul>';

            Y.log($confirm);

            // Rebuild the modal each time, since the content of the modal is
            // so unique, and enabling and disabling the button would have to be
            // redone anyway.
            $bodycontent = '<div id="modal-content">';
            $bodycontent += '<h4>'+M.util.get_string('preparing_actions', 'block_rlagent')+'</h4>';
            $bodycontent += '<p>' + $confirm + '</p>';
            $bodycontent += '<div id="action-results"><i class="fa fa-spinner fa-spin fa-4"></i></div>';
            $bodycontent += '</div>';

            YUI().use("panel", function (Y) {
                // We'll write example code here where we have Y.Datatable, Y.Plugin.Drag and Y.Panel available
                M.block_rlagent.plugin_modal = new Y.Panel({
                    srcNode: '<div></div>',
                    id: 'manage-actions-modal',
                    headerContent: M.util.get_string('actions_in_progress', 'block_rlagent'),
                    bodyContent: $bodycontent,
                    buttons: [
                        {
                            id: 'modal-close-button',
                            label: 'Close',
                            section: 'footer',
                            visible: false,
                            action: function(e) {
                                e.preventDefault();
                                M.block_rlagent.plugin_modal.hide();
                                M.block_rlagent.plugin_modal.destroy();
                            },
                            classNames: 'modal-close-button',
                            disabled: true
                        }
                    ],
                    classNames: 'manage-actions-modal',
                    width: '60%',
                    height: 500,
                    zIndex: 10000,
                    centered: true,
                    modal: true,
                    visible: true,
                    render: true
                });
            });

            // Formulate AJAX request, make request, and write results to modal.
            // Build the query string sent in POST request.
            var $data = '';
            var $dependency_report = '<h4>'+M.util.get_string('dependencies', 'block_rlagent')+'</h4>';
            // Loop through all add actions.
            // Check dependencies for each add action, and add that action and each dependency not yet installed.
            Y.Object.each(M.block_rlagent.data_actions.add, function($value, $key) {
                Y.log('Loop through add actions.');
                if ($data.length > 0) {
                    $data += '&';
                }
                $data += 'add[]=' + $key;

                // For each dependency, if it's not installed in the addons object, add.
                Y.Object.each(M.block_rlagent.data_addons[$key].dependencies, function($dvalue, $dkey) {
                    if (!M.block_rlagent.data_addons[$dkey].installed) {
                        $data += 'add[]=' + $dkey;
                        var $dependency = {'source': $key, 'target': $dkey};
                        $dependency_report += '<ul>'+M.util.get_string('dependency_will_be_added', 'block_rlagent', $dependency);
                    }
                });
            });

            // Loop through all update actions.
            // Check dependencies for each action, and add that action and each dependency not yet installed.
            Y.Object.each(M.block_rlagent.data_actions.update, function($value, $key) {
                Y.log('Loop through update actions.');
                if ($data.length > 0) {
                    $data += '&';
                }
                $data += 'update[]=' + $key;

                // For each dependency, if it's not installed in the addons object, add.
                Y.Object.each(M.block_rlagent.data_addons[$key].dependencies, function($dvalue, $dkey) {
                    if (!M.block_rlagent.data_addons[$dkey].installed) {
                        $data += 'update[]=' + $dkey;
                        var $dependency = {'source': $key, 'target': $dkey};
                        $dependency_report += '<ul>'+M.util.get_string('dependency_will_be_added', 'block_rlagent', $dependency);
                    }
                });
            });
            // Loop through all remove actions.
            // Check dependencies for each action, and remove that action and any dependency not required
            // by another plugin (crazy).
            Y.Object.each(M.block_rlagent.data_actions.remove, function($value, $key) {
                Y.log('Loop through remove actions.');
                if ($data.length > 0) {
                    $data += '&';
                }
                $data += 'remove[]=' + $key;

                // For each dependency, if it's not installed in the addons object, add.
                Y.Object.each(M.block_rlagent.data_addons[$key].dependencies, function($dvalue, $dkey) {
                    $neededby = [];
                    Y.Object.each(M.block_rlagent.data_addons, function($rvalue, $rkey) {
                        if ($rvalue.dependencies[$dvalue]) {
                            $neededby.push($rkey);
                        }
                    });

                    if ($neededby.length <= 0) {
                        data += '&remove[]=' + $dkey;
                    }
                });
            });

            if ($dependency_report.length <= 0) {
                $dependency_report = M.util.get_string('no_dependencies', 'block_rlagent');
                Y.log($dependency_report);
            }
            Y.one('#action-results').insert($dependency_report, 'before');

            Y.log('$data = ' + $data);

            /**
             * Finish off the modal
             *
             * @param boolean $success Whether the action dispatching script reported success.
             */
            function finish_modal($success) {
                Y.log('finish_modal');
                Y.log($success);
                if ($success) {
                    var $msg = '<h4>'+M.util.get_string('success', 'block_rlagent')+'</h4>';
                    $msg += M.util.get_string('actions_completed_success', 'block_rlagent');
                } else {
                    var $msg = '<h4>'+M.util.get_string('failure', 'block_rlagent')+'</h4>';
                    $msg += M.util.get_string('actions_completed_failure', 'block_rlagent');
                }
                Y.one('#action-results').insert($msg, 'before');
                // Hide the spinner.
                Y.one('#modal-content .fa-spinner').hide();
                // Enable the close button.
                button = Y.one('#manage-actions-modal button');
                button.set('disabled', false);
                // Don't know why this isn't done automatically.  YUI bug?
                button.removeClass('yui3-button-disabled');
            }

            // AJAX to send rating.
            YUI().use("io-base", function(Y) {
                var url = M.cfg.wwwroot + '/blocks/rlagent/mass/action.php';
                var cfg = {
                    method: 'POST',
                    data: $data,
                    on: {
                        success: function(id, o) {
                            Y.log('success');
                            var $response = null;
                            YUI().use('json-parse', function (Y) {
                                try {
                                    $response = Y.JSON.parse(o.responseText);
                                }
                                catch (e) {
                                    Y.log("Parsing failed.");
                                }
                            });
                            Y.log($response[0]);
                            var $success = false;
                            if (!$response[0]) {
                                $success = true;
                                Y.log('JSON response is success.');
                            } else {
                                Y.log('JSON response reports a failure.');
                            }
                            finish_modal($success);
                            // TODO: Disabled update buttons
                        },
                        failure: function() {
                            Y.log('Action failure.');
                            // TODO: Inject a failure message into the panel.
                            // Enable the close button.
                            Y.one('#manage-actions-modal button').set('disabled', false);
                            // Hide the spinner.
                            Y.one('#modal-content fa-spinner').hide();
                        }
                    }
                };
                $addons = Y.io(url, cfg);
            });
        }

        /**
         * Handle the clicking of the plugin queue button
         *
         * @param event e The event triggered by clicking the button
         */
        function toggle_actions(e) {
            var $actions = Y.one('ul#plugin-actions');

            if ($actions.getStyle('display') === 'none') {
                $actions.setStyle('display', 'block');
            } else {
                $actions.setStyle('display', 'none');
            }
            $actions.get('parentNode').toggleClass('open');
        }

        var $plugins = Y.one('.plugins');
        $plugins.delegate('click', install_click, 'button.btn-install');
        $plugins.delegate('click', remove_click, '.btn-remove');
        $plugins.delegate('click', update_click, '.btn-update');
        Y.one('#go-update-plugins').on('click', do_actions);
        Y.one('button.plugin-actions').on('click', toggle_actions);
        Y.one('ul#plugin-actions').on('mouseleave', toggle_actions);
    },

    /**
     * Send plugin ratings to the plugin rating script.
     *
     * @param string $addon The addon name
     * @param int $rating The numberical rating value (1-5)
     */
    plugin_send_rating: function($addon, $rating) {
        // Y.log('plugin_send_rating()');
        Y.log('addon = ' + $addon);
        Y.log('rating = ' + $rating);

        // AJAX to send rating.
        YUI().use("io-base", function(Y) {
            var url = M.cfg.wwwroot + '/blocks/rlagent/mass/rate.php';
            var cfg = {
                method: 'POST',
                data: 'addon=' + $addon + '&rating=' + $rating,
                on: {
                    success: function(id, o) {
                        Y.log('success');
                        var $response = null;
                        YUI().use('json-parse', function (Y) {
                            try {
                                $response = Y.JSON.parse(o.responseText);
                            }
                            catch (e) {
                                Y.log("Parsing failed.");
                            }
                        });
                        Y.log('$response = ');
                        Y.log($response);
                    },
                    failure: function() {
                        Y.log('Ratings failure.');
                    }
                }
            };
            $addons = Y.io(url, cfg);
        });
    },

    /**
     * Filter the plugins based on the selected filters.
     */
    plugin_filter_plugins: function() {
        Y.log('plugin_filter_plugins');
        // Hide addons.
        Y.all('.plugins .plugin.well').hide(true);
        // Get all of the displayed filters.
        $filters = Y.all('#filter-labels span.badge');
        // Retrieve and sort the filters, in order to treat them one by one.
        $typefilters = [];
        $filters.filter('[data-filter-mode="type"]').each(function(node) {
            var $refine = Y.one(node).getAttribute('data-filter-refine');
            var $refinenum; // TODO: This variable should be unnecessary once type is a string.
            // This Object.each() shouldn't be necessary once type is a string.
            Y.Object.each(M.block_rlagent.plugin_types, function($value, $key) {
                // If the object type matches the refine variable, we save the value key.
                if ($value.type === $refine) {
                    $refinenum = $key;
                }
            });
            $typefilters.push($refine);
            $typefilters.push($refinenum); // TODO: Remove once type numbers are replaced with strings.
        });
        $statusfilters = [];
        $filters.filter('[data-filter-mode="status"]').each(function(node) {
            $refine = Y.one(node).getAttribute('data-filter-refine');
            $statusfilters.push($refine);
        });
        $stringfilters = [];
        $filters.filter('[data-filter-mode="string"]').each(function(node) {
            $refine = Y.one(node).getAttribute('data-filter-refine');
            $stringfilters.push($refine);
        });
        // Addons object to manipulate.
        $addons = JSON.parse(JSON.stringify(M.block_rlagent.data_addons));
        Y.Object.each($addons, function($addonvalue, $addonkey) {
            $addonvalue.filtered = false;
        });
        Y.log('Start filtering');
        Y.log($addons);

        /**
         *  Handle plugin-type filters.
         */
        function filter_type() {
            // If there are no type filters, move on to the
            // next set of filters.
            if ($typefilters.length <= 0) {
                filter_status();
                return false;
            }
            var $hide;
            // Additive filtering. If match, add to object.
            Y.Object.each($addons, function($addonvalue, $addonkey) {
                $hide = true;
                Y.Array.each($typefilters, function($typevalue, $typeindex) {
                    if ($addonvalue.type === $typevalue) {
                        $hide = false;
                    }
                });
                Y.log("Type filter for "+$addonkey+": "+$hide);
                if ($hide) {
                    $addonvalue.filtered = true;
                }
            });
            filter_status();
        }

        /**
         * Handle plugin-status filters.
         */
        function filter_status() {
            // TODO: Refine and test once status is available.
            Y.log('filter_status()');
            Y.log($addons);
            Y.log('statusfilters length: '+$statusfilters.length);
            Y.log($statusfilters);
            if ($statusfilters.length <= 0) {
                Y.log('statusfilters is empty, moving on to next.');
                filter_string();
                return false;
            }
            // Now remove plugins matching status from type object.
            var $hide;
            Y.Object.each($addons, function($addonvalue, $addonkey) {
                Y.log('filter_status() looping through ');
                $hide = true;
                Y.Array.each($statusfilters, function($typevalue, $typeindex) {
                    Y.log('$typevalue: '+$typevalue);
                    Y.log('$typeindex: '+$typeindex);
                    if (($typevalue === 'installed') && $addonvalue.installed) {
                        Y.log('type matches = '+$typevalue);
                        $hide = false;
                    } else if (($typevalue === 'notinstalled') && !$addonvalue.installed) {
                        Y.log('type matches = '+$typevalue);
                        $hide = false;
                    } else if (($typevalue === 'updateable') && $addonvalue.installed && $addonvalue.upgradeable) {
                        Y.log('type matches = '+$typevalue);
                        $hide = false;
                    }
                });
                Y.log("Status filter for "+$addonkey+": "+$hide);
                if ($hide) {
                    $addonvalue.filtered = true;
                }
            });
            filter_string();
        }

        /**
         *  Handle plugin-string filters.
         */
        function filter_string() {
            // Now subtract plugins not matching string from $addonsshow.
            // Y.log('filter_string()');
            // Now remove all plugins not matching string from status object.
            var $hide;
            Y.Object.each($addons, function($addonvalue, $addonkey) {
                // Establish the lowercase strings to search.
                $heading = String($addonvalue.display_name).toLowerCase();
                $description = String($addonvalue.description).toLowerCase();
                $name = String($addonkey).toLowerCase();
                // For each string, test for matches.
                $hide = false;
                Y.Array.each($stringfilters, function($stringvalue, $stringindex) {
                    // Get lowercaps value of string
                    $stringlowcaps = $stringvalue.toLowerCase();
                    // Determine whether each contains the search string.
                    $isinheading = $heading.indexOf($stringlowcaps);
                    $isindesc = $description.indexOf($stringlowcaps);
                    $isinname = $name.indexOf($stringlowcaps);

                    // Delete from $addsonsstringfiltered if no match.
                    if (($isinheading < 0) && ($isindesc < 0) && ($isinname < 0)) {
                        // Y.log('string does not match, deleting.');
                        $hide = true;
                    } else {
                        Y.log('string matches.');
                    }
                });
                Y.log("String filter for "+$addonkey+": "+$hide);
                if ($hide) {
                    $addonvalue.filtered = true;
                }
            });
        }

        filter_type();

        Y.log('done filtering.');
        Y.log($addons);

        Y.Object.each($addons, function(value, key) {
            Y.log("Reviewing "+key);
            // Show addons not filtered out.
            $key = String(key).replace(' ', '_');
            $selector = '.plugin[data-key="' + $key + '"]';
            var $obj = Y.one($selector);
            // This truthiness check is to ignore errors in JSON object.
            if ($obj && !value.filtered) {
                Y.log("Showing "+key);
                // Show addon.
                $obj.show(true);
            }
        });
    },

    /**
     * This function forces the filter bar to "hover" when the page is scrolled down.
     */
    plugin_filter_scroll: function() {
        // My recommendation is that we not implement this functionality
        // at all. It will be nearly impossible to accommodate every possible
        // theme design. It is an inelegant modification to an otherwise elegant
        // and responsive interface. If the filters are working well, the user
        // should not need to scroll in the first place. This functionality
        // will interfere with responsiveness in all but the best-maintained code.
        // Simpler, IMHO, will turn out, in this case, to have been better. -AG

        // Y.log('plugin_filter_scroll');
        // $filterform = Y.one('#filter-form');
        // $placeholder = Y.one('#filter-placeholder');
        // var $filterformwidth;
        // var $filterformheight;
        // var $filterformY;

        // // Set height and width of $paceholder same as $filterform.
        // YUI().use('node', function(Y) {
        //     $filterformwidth = $filterform.getStyle('width');
        //     Y.log('$filterformwidth = '+$filterformwidth);
        //     $filterformheight = $filterform.getStyle('height');
        //     Y.log('$filterformheight = '+$filterformheight);
        //     $filterformY = $filterform.getY();
        //     Y.log('$filterformY = '+$filterformY);
        // });

        // $placeholder.setStyle('width', $filterformwidth);
        // $placeholder.setStyle('height', $filterformheight);

        // $filterform.setStyle('width', $filterformwidth);
        // $filterform.setStyle('position', 'fixed');
    },

    /**
     * Initialization function.
     */
    init: function() {
        Y.log('Init function called:');
        $update = Y.one('.site-update');
        if ($update) {
            $shown = $update.getStyle('display');
            Y.log('Update found.  Display: '+$shown);
        }
        if ($shown == 'block') {
            // If plugin update available, display that interface
            M.block_rlagent.skip_update_init();
            M.block_rlagent.update_site_init();
        } else {
            // Display the plugin selection interface
            M.block_rlagent.plugin_select_init();
        }
    }
};
