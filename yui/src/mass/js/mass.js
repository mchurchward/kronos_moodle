//
// Mockup functionality for MASS plugin.
// To be converted to YUI once mockup stage is complete.
//

M.block_rlagent = M.block_rlagent || {};
M.block_rlagent = {
    // Object to contain selected actions.
    data_actions: {},

    // Object to contain addons.
    data_addons: {},

    plugin_types: {
        "block" :  {"type":"block", "title":"Block", "icon":"fa-square-o"},
        "mod":  {"type":"mod", "title":"Module", "icon":"fa-rocket"},
        "auth":  {"type":"auth", "title":"Authentication", "icon":"fa-key"},
        "enrol":  {"type":"enrol", "title":"Enrolment", "icon":"fa-group"},
        "filter":  {"type":"filter", "title":"Filter", "icon":"fa-filter"},
        "format":  {"type":"format", "title":"Course Format", "icon":"fa-columns"},
        "gradeexport":  {"type":"gradeexport", "title":"Grade Export", "icon":"fa-download"},
        "local":  {"type":"local", "title":"Local", "icon":"fa-home"},
        "qtype":  {"type":"qtype", "title":"Question Type", "icon":"fa-check-square-o"},
        "repository":  {"type":"repository", "title":"Repository", "icon":"fa-folder-open"},
        "theme":  {"type":"theme", "title":"Theme", "icon":"fa-picture-o"},
        "tinymce":  {"type":"tinymce", "title":"TinyMCE", "icon":"fa-text-height"},
        "plagiarism":  {"type":"plagiarism", "title":"Plagiarism", "icon":"fa-eye"}
    },

    plugin_fetch_addons: function() {
        Y.log('plugin_fetch_addons');
        YUI().use("io-base", function(Y) {
            $url = M.cfg.wwwroot + '/blocks/rlagent/addons.php?type=addonlist';
            Y.log($url);
            function complete(id, o, args) {
                Y.log('complete');
            }
            function success(id, o, args) {
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
                Y.log($response);
                M.block_rlagent.data_addons = $response;
                Y.log(M.block_rlagent.data_addons);
                M.block_rlagent.plugin_write_addons();
            }
            function failure(id, o, args) {
                Y.log('Failure: '+args[0]);
            }
            Y.on('io:complete', complete, Y, []);
            Y.on('io:success', success, Y, []);
            Y.on('io:failure', failure, Y, ['AJAX request failed.']);
            $addons = Y.io($url);
        });
    },

    plugin_write_addons: function() {
        // Inject addon HTML into page.
        Y.log('plugin_write_addons');
        Y.Object.each(M.block_rlagent.data_addons, function($value, $key) {
            $displayname = $value.display_name;
            $description = $value.description;
            $rating = $value.rating;
            // Not yet implemented, will convey installed/not installed, etc.
            // $status = $value.status;
            $type = $value.type;

            // This will have to be updated when we start sending the plugin status.
            $buttonmarkup = '<button type="button" class="btn btn-install btn-primary" data-toggle="modal" data-target="#manage_install_modal">';
            $buttonmarkup += '<i class="fa fa-plus"></i>';
            $buttonmarkup += 'Install';
            $buttonmarkup += '</button>';

            // Build markup for plugin average rating.
            $ratingmarkup = '<div class="avg-rating"><h5>Average Rating</h5>';
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
            $itemmarkup += '<i class="pull-left plugin-type fa ' + M.block_rlagent.plugin_types[$type].icon + '"></i>';
            $itemmarkup += '<div class="media-body">';
            $itemmarkup += '<h3 class="media-heading">' + $displayname + '</h3>';
            $itemmarkup += '<p>' + $description + '</p>';
            $itemmarkup += '<div class="rate-plugin">';
            $itemmarkup += '<p><strong>Add or update your rating</strong> for this plugin.</p>';
            $itemmarkup += '<i class="fa fa-star-o" title="1 star"></i>';
            $itemmarkup += '<i class="fa fa-star-o" title="2 stars"></i>';
            $itemmarkup += '<i class="fa fa-star-o" title="3 stars"></i>';
            $itemmarkup += '<i class="fa fa-star-o" title="4 stars"></i>';
            $itemmarkup += '<i class="fa fa-star-o" title="5 stars"></i>';
            $itemmarkup += '</div>';
            $itemmarkup += '</div>';
            $itemmarkup += '</li></ul>';

            $html = '<div class="plugin well type-' + M.block_rlagent.plugin_types[$type].type + '">';
            $html += '<div class="choose">';
            $html += $buttonmarkup;
            $html += $ratingmarkup;
            $html += '</div>';

            $html += $itemmarkup;

            $html += '</div>';

            Y.one('.plugin-select .plugins').insert($html, 'after');
            // Init rate plugins *after* the plugins are printed to the page.
            M.block_rlagent.plugin_rate_plugins();
        });

    },

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

    update_site_init: function() {
        // Actions performed if user chooses to update site.
        Y.one('#doupdate').on('click', function(e) {
            // Disable button.
            e.target.setAttribute('disabled');
            // Show spinner.
            Y.one('.site-update-spinner').show(true);
            // Get email to contact when update is complete.
            $useremail = Y.one('#useremail').get('text');
            // Call site update function
            // TODO: AJAX request, sending $useremail as a parameter.
            // TODO?: Reload page?
        });
    },

    plugin_select_init: function() {
        // Inits functions for filtering and rating plugins.
        // Called when the plugins list and filter controls are first shown.
        M.block_rlagent.plugin_select_filter();
        M.block_rlagent.plugin_clear_filters();
        M.block_rlagent.plugin_input_filter();
        M.block_rlagent.plugin_dropdown_visibility();
        M.block_rlagent.plugin_fetch_addons();
    },

    plugin_manage_dropdown: function() {
        Y.log('plugin_manage_dropdown');
    },

    plugin_add_filter: function($filterstring) {
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
        // If the filter is not yet displayed, display it.
        if ($labelarr.indexOf($filterstring) <= -1) {
            $labelmarkup = '<span class="badge">'+$filterstring+'<i class="fa fa-times" alt="Remove Filter"></i></span>';
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

    plugin_select_filter: function() {
        // Called when filter in filter dropdown is selected.
        Y.all('ul#select-filters li').on('click', function(e) {
            // Capture target.
            $target = Y.one(e.currentTarget);
            // Capture the content of the li.
            $label = $target.one('a').get('text');
            M.block_rlagent.plugin_add_filter($label);
        });
    },

    plugin_dropdown_visibility: function() {
        // Scripts the behavior of the filter dropdown.
        // This is done because there are some disprepancies between
        // Moodle's YUI revamp of the dropdown (directed mainly at the
        // Moodle custom menu), and the default Bootstrap functionality
        // necessary for this plugin filtering mechanism.
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

    },

    plugin_input_filter: function() {
        // Scripts the behavior of the filter input field.
        $input = Y.one('input#plugin-filter');
        $inputengaged = false;

        // Enter key adds the input contents to filter list.
        function enter_key_press () {
            $filter = Y.one('input#plugin-filter').get('value');
            if ($filter.length >= 1) {
                M.block_rlagent.plugin_add_filter($filter);
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

    plugin_clear_filters: function() {
        // Clears any added filters.
        $clearbutton = Y.one('button#clear-filters');
        $clearbutton.on('click', function() {
            Y.all('#filter-labels span.badge').remove(true);
            M.block_rlagent.plugin_filter_plugins();
            M.block_rlagent.plugin_hide_filterblock();
        });
    },

    plugin_show_filterblock: function() {
        // Displays the filter block if there are filters.
        if (Y.all('#filter-labels span.badge').size() >= 1) {
            Y.one('#labels-box').show(true);
        }
    },

    plugin_hide_filterblock: function() {
        // If there are no filters, hide the filter block.
        if (Y.all('#filter-labels span.badge').size() <= 0) {
            Y.one('#labels-box').hide(true);
        }
    },

    plugin_remove_filter: function() {
        // Remove a single filter.
        $removebutton = Y.all('#filter-labels i.fa-times');
        $removebutton.on('click', function(e) {
            $target = e.target.get('parentNode').remove(true);
            M.block_rlagent.plugin_hide_filterblock();
        });
    },

    plugin_rate_plugins: function() {
        Y.all('.rate-plugin .fa-star-o, .rate-plugin .fa-star').on('click', function(e) {
            $target = Y.one(e.target);

            // Get full set of clicked star plus siblings.
            $starset = $target.get('parentNode').get('children').filter('.fa');

            // Get star index.
            $clickedindex = $starset.indexOf($target);

            // Fill star and all stars of index below it.
            $fillstars = $starset.slice(0, $clickedindex + 1);

            // Empty all stars with index above it.
            $emptystars = $starset.slice($clickedindex + 1, $starset.size() + 1);

            // Rating is 1-based: 1-5.
            $rating = $clickedindex + 1;

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
                    M.block_rlagent.plugin_send_rating($rating);
                }
            });
        });
    },

    plugin_send_rating: function($rating) {
        Y.log('plugin_send_rating()');
        // TODO: AJAX to send rating.
    },

    plugin_filter_plugins: function() {
        // Filter plugins based on selected filters
        // Y.log('plugin_filter_plugins');
    },

    init: function() {
        // Y.log('mass.js');
        $updateshown = Y.all('.site-update'); // .getStyle('display');
        if ($updateshown.length >= 1) {
            Y.log($updateshown.length);
            // If plugin update available, display that interface
            M.block_rlagent.skip_update_init();
            M.block_rlagent.update_site_init();
        } else {
            // Display the plugin selection interface
            M.block_rlagent.plugin_select_init();
        }
    }
};
