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
 * Render markup for MASS interface.
 *
 * @package   block_rlagent
 * @copyright 2013 onwards Remote-Learner {@link http://www.remote-learner.net/}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_rlagent_renderer extends plugin_renderer_base {

    /*
     * Output the update available option markup.
     *
     * @param  array $reinoptions Options passed to the format_text(), which displays the widget
     * @param  array $urlparams HTML attributes for <a> tag which opens pop-up with widget markup
     * @return string HTML fragment
     */

    public function print_update_available($USER) {
        global $CFG;

        // Container div.
        $content = html_writer::start_div('site-update');

        // Heading.
        $content .= html_writer::tag('h3', get_string('update_available_heading', 'block_rlagent'));

        // Info/instructions.
        $content .= html_writer::tag('div', get_string('update_available', 'block_rlagent'), array('class' => 'instr'));

        // Display update complete email to admin user.
        $content .= html_writer::start_span('notify-email');
        $content .= get_string('notification_email', 'block_rlagent');
        $content .= html_writer::end_span();
        $content .= html_writer::start_span('useremail');
        $content .= $USER->email;
        $content .= html_writer::end_span();

        // Print buttons.
        $content .= html_writer::start_div('update-controls');
        // Skip button.
        $content .= html_writer::start_tag('button', array('id' => 'skipupdate', 'class' => 'btn btn-warning', 'type' => 'button'));
        $content .= html_writer::tag('i', '', array('class' => 'fa fa-times'));
        $content .= get_string('skipupdate', 'block_rlagent');
        $content .= html_writer::end_tag('button');
        // Perform update button.
        $content .= html_writer::start_tag('button', array('id' => 'doupdate', 'class' => 'btn btn-success', 'type' => 'button'));
        $content .= html_writer::tag('i', '', array('class' => 'fa fa-check-circle-o'));
        $content .= get_string('syncsite', 'block_rlagent');
        $content .= html_writer::end_tag('button');
        $content .= html_writer::end_div();

        // Print update spinner.
        $content .= html_writer::start_tag('div', array('class' => 'site-update-spinner', 'style' => 'display: none;'));
        $content .= html_writer::tag('h4', get_string('updatingdata', 'block_rlagent'));
        $content .= html_writer::tag('i', '', array('class' => 'fa fa-spinner fa-spin fa-2x'));
        $content .= html_writer::end_tag('div');

        // Close container div.
        $content .=  html_writer::end_div();

        return $content;
    }


    /*
     * Output the markup for the filter controls.
     *
     * @param  array $reinoptions Options passed to the format_text(), which displays the widget
     * @param  array $urlparams HTML attributes for <a> tag which opens pop-up with widget markup
     * @return string HTML fragment
     */

    public function print_filter_controls() {
        global $CFG;
    }



    //     /**
    //  * Output the markup for the accordion widget in the debug interface.
    //  *
    //  * @param  array $reinoptions Options passed to the format_text(), which displays the widget
    //  * @param  array $urlparams HTML attributes for <a> tag which opens pop-up with widget markup
    //  * @return string HTML fragment
    //  */
    // public function print_debug_accordion($reinoptions, $urlparams) {
    //     global $CFG;

    //     // Print header.
    //     $content = html_writer::tag('h2', get_string('accordiontitle', 'filter_rein'));
    //     // Print view.
    //     $content .= html_writer::tag('div', get_string('accordiondesc', 'filter_rein'), array('class' => 'desc'));
    //     // Print instructions.
    //     $content .= html_writer::tag('h3', get_string('widgetinstr', 'filter_rein'));
    //     $content .= html_writer::tag('div', get_string('accordioninstr', 'filter_rein'), array('class' => 'instr'));
    //     // Print Should Look Like header.
    //     $content .= html_writer::tag('h3', get_string('widgetshouldlook', 'filter_rein'), array('class' => 'shouldlook'));
    //     // Print preview image.
    //     $imgparams = array(
    //         'src' => $CFG->wwwroot.'/filter/rein/pix/demo/accordion.png',
    //         'class' => 'shouldlook',
    //         'width' => '800',
    //         'height' => '320'
    //     );
    //     $content .= html_writer::tag('img', '', $imgparams);
    //     // Print Live preview header.
    //     $content .= html_writer::tag('h3', get_string('widgetview', 'filter_rein'));
    //     // Print Widget.
    //     $getmarkup = file_get_contents(dirname(__FILE__).'/markup/accordion.php');
    //     $content .= format_text($getmarkup, $format = FORMAT_HTML, $options = $reinoptions, $courseid_do_not_use = null);
    //     // Print link to markup.
    //     $urlparams['href'] = $CFG->wwwroot.'/filter/rein/markup/returntext.php?markup=accordion';
    //     $content .= html_writer::tag('a', get_string('accordionviewmarkup', 'filter_rein'), $urlparams);

    //     return $content;
    // }


}