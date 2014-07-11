YUI.add('moodle-block_rlagent-mass', function (Y, NAME) {

//
// Mockup functionality for MASS plugin.
// To be converted to YUI once mockup stage is complete.
//

M.block_rlagent = M.block_rlagent || {};
M.block_rlagent = {
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
        M.block_rlagent.plugin_rate_plugins();
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
            M.block_rlagent.filter_plugins();
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
            M.block_rlagent.filter_plugins();
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
                    M.block_rlagent.send_rating($rating);
                }
            });
        });
    },

    send_rating: function($rating) {
        Y.log('send_rating()');
        // TODO: AJAX to send rating.
    },

    filter_plugins: function() {
        // Filter plugins based on selected filters
        // Y.log('filter_plugins');
    },

    init: function() {
        // Y.log('mass.js');
        $updateshown = Y.one('.site-update').getStyle('display');
        if ($updateshown === 'none') {
            // Display the plugin selection interface
            M.block_rlagent.plugin_select_init();
        } else {
            // If plugin update available, display that interface
            M.block_rlagent.skip_update_init();
            M.block_rlagent.update_site_init();
        }
    }
};


}, '@VERSION@', {"requires": ["base", "node", "panel", "plugin", "transition", "event", "event-delegate"]});
