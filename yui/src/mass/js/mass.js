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
            // Y.log($value);
            // Y.log($key);
            $displayname = $value.display_name ? $value.display_name : 'Plugin name not available.';
            $description = $value.description ? $value.description : 'Plugin description not available.';
            $myrating = $value.myrating ? $value.myrating : 0;
            $rating = $value.rating ? $value.rating : 0;
            // TODO: Not yet implemented, will convey installed/not installed, etc.
            // $status = $value.status ? $value.status : 'notinstalled';
            $installed = $value.installed ? $value.installed : false;
            $upgradeable = $value.upgradeable ? $value.upgradeable : false;
            $cached = $value.cached ? $value.cached : false;
            $type = $value.type ? $value.type : '1';

            $typeclass = ' type-' + $type; // M.block_rlagent.plugin_types[$type].type;
            $nameclass = ' name-' + String($value.name).replace(' ', '_');
            $datakey = 'data-key="' + String($key).replace(' ', '_') + '" ';
            // $datastatus = 'data-status="' + String($status).replace(' ', '_') + '" ';
            $datainstalled = 'data-installed="' + String($installed).replace(' ', '_') + '" ';
            $dataupgradeable = 'data-upgradeable="' + String($upgradeable).replace(' ', '_') + '" ';
            $datacached = 'data-cached="' + String($cached).replace(' ', '_') + '" ';
            $datatype = 'data-type="' + String($type).replace(' ', '_') + '" ';

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

            $html = '<div class="plugin well' + $typeclass + $nameclass + '" ' +
                $datakey + $datainstalled + $dataupgradeable + $datacached + $datatype + '>';
            $html += '<div class="choose">';
            $html += $buttonmarkup;
            $html += $ratingmarkup;
            $html += '</div>';

            $html += $itemmarkup;

            $html += '</div>';

            Y.one('.plugin-select .plugins').insert($html);
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
        M.block_rlagent.plugin_filter_scroll();
    },

    plugin_manage_dropdown: function() {
        Y.log('plugin_manage_dropdown');
    },

    plugin_capitalise_firstletter: function(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    },

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
            $labelmarkup = '<span class="badge" data-filter-mode="' + $mode +'" data-filter-refine="' + $refine + '">' + $prefix + $filterstring + '<i class="fa fa-times" alt="Remove Filter"></i></span>';
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
            $mode = $target.getAttribute('data-filter-mode');
            Y.log('$mode = '+$mode);
            $refine = $target.getAttribute('data-filter-refine');
            Y.log('$refine = '+$refine);
            M.block_rlagent.plugin_add_filter($label, $mode, $refine);
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
            // Also re-display all plugins.
            Y.all('.plugins .plugin.well').show(true);
        }
    },

    plugin_remove_filter: function() {
        // Remove a single filter.
        $removebutton = Y.all('#filter-labels i.fa-times');
        $removebutton.on('click', function(e) {
            $target = e.target.get('parentNode').remove(true);
            M.block_rlagent.plugin_filter_plugins();
            M.block_rlagent.plugin_hide_filterblock();
        });
    },

    plugin_rate_node: null,

    plugin_rate_plugins: function() {

        // var $node = null;

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

    plugin_send_rating: function($addon, $rating) {
        // Y.log('plugin_send_rating()');
        Y.log('addon = ' + $addon);
        Y.log('rating = ' + $rating);

        // AJAX to send rating.
        YUI().use("io-base", function(Y) {
            var url = M.cfg.wwwroot + '/blocks/rlagent/massrate.php';
            var cfg = {
                method: 'POST',
                data: 'addon=' + $addon + '&rating=' + $rating,
                on: {
                    complete: function(id, o) {
                        Y.log('Ratings complete.');
                    },
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
                    failure: function(id, o) {
                        Y.log('Ratings failure.');
                    }
                }
            };
            $addons = Y.io(url, cfg);
        });
    },

    plugin_filter_plugins: function() {
        // Filter plugins based on selected filters
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
                    } else if (($typevalue === 'upgradeable') && $addonvalue.installed && $addonvalue.upgradeable) {
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
                        // Y.log('string matches.');
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
            $key = String(key).replace(' ', '_')
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
