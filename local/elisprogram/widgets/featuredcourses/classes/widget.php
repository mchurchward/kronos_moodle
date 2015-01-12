<?php
/**
 * ELIS(TM): Enterprise Learning Intelligence Suite
 * Copyright (C) 2008-2014 Remote-Learner.net Inc (http://www.remote-learner.net)
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
 * @package    eliswidget_featuredcourses
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2015 Remote-Learner.net Inc (http://www.remote-learner.net)
 * @author     James McQuillan <james.mcquillan@remote-learner.net>
 * @author     Akinsaya Delamarre <adelamarre@remote-learner.net>
 */

namespace eliswidget_featuredcourses;

/**
 * A widget allowing students to enrol in classes.
 */
class widget extends \local_elisprogram\lib\widgetbase {
    /**
     * Generate an SVG progress bar.
     *
     * @param int $percentcomplete The percent complete the progress bar is.
     * @return string SVG code for the progress bar.
     */
    public function generateprogressbar($percentcomplete) {
        $decile = floor($percentcomplete/10);
        $progressrectattrs = [
            'x' => '0',
            'y' => '0',
            'height' => '100%',
            'width' => $percentcomplete.'%',
            'class' => 'decile'.$decile,
        ];
        if ($decile >= 8) {
            $colorcode = 3;
        } else if ($decile >= 5) {
            $colorcode = 2;
        } else {
            $colorcode = 1;
        }
        $progressrectattrs['class'] .= ' colorcode'.$colorcode;

        $progressrect = \html_writer::tag('rect', '', $progressrectattrs);

        $progressbarattrs = ['class' => 'elisprogress'];
        $progressbar = \html_writer::tag('svg', $progressrect, $progressbarattrs);
        return $progressbar;
    }

    /**
     * Get program data to display.
     *
     * @param int $userid The ELIS user id of the user we're getting program data for.
     * @param bool $displayarchived Whether we're displaying archived programs as well.
     * @param int $programid A program ID if displaying only one program.
     * @return \moodle_recordset A recordset of programs.
     */
    public function get_program_data($userid, $displayarchived = false, $programid = null) {
        global $DB;

        require_once(\elispm::lib('data/curriculum.class.php'));
        require_once(\elispm::lib('data/curriculumstudent.class.php'));
        require_once(\elispm::lib('data/curriculumcourse.class.php'));
        require_once(\elispm::lib('data/course.class.php'));
        require_once(\elispm::lib('data/programcrsset.class.php'));

        $joins = [];
        $restrictions = ['pgmstu.userid = :userid'];
        $params = ['userid' => $userid];

        // Add in joins and restrictions if we're hiding archived programs.
        if ($displayarchived !== true) {
            $joins[] = 'JOIN {context} ctx ON ctx.instanceid = pgm.id AND ctx.contextlevel = :pgmctxlvl';
            $joins[] = 'LEFT JOIN {local_eliscore_fld_data_int} flddata ON flddata.contextid = ctx.id';
            $joins[] = 'LEFT JOIN {local_eliscore_field} field ON field.id = flddata.fieldid';
            $restrictions[] = '((field.shortname = :archiveshortname AND flddata.data = 0) OR flddata.id IS NULL)';
            $params['pgmctxlvl'] = \local_eliscore\context\helper::get_level_from_name('curriculum');
            $params['archiveshortname'] = '_elis_program_archive';
        }

        if (!empty($programid) || is_numeric($programid)) {
            $restrictions[] = 'pgm.id = :pgmid';
            $params['pgmid'] = $programid;
        }

        // Add another restrictions to only list programs containing at least one course that is marked as a featured course.
        $featuredcoursefield = get_config('eliswidget_featuredcourses', 'featuredcoursefield');
        $ctxlevel = \local_eliscore\context\helper::get_level_from_name('course');

        $restrictions[] = 'EXISTS (SELECT sub_field.id
                                     FROM {local_eliscore_field} sub_field
                                     JOIN {context} ctx ON ctx.contextlevel = :ctxlevel
                                     JOIN {local_eliscore_fld_data_int} fld_data ON fld_data.contextid = ctx.id AND fld_data.fieldid = sub_field.id AND fld_data.data = 1
                                    WHERE sub_field.shortname = :featuredcoursefield
                                          AND ctx.instanceid = pgmcrs.courseid)';

        $params['ctxlevel'] = $ctxlevel;
        $params['featuredcoursefield'] = $featuredcoursefield;

        $restrictions = implode(' AND ', $restrictions);
        $sql = 'SELECT pgmstu.id as pgmstuid,
                       pgmstu.curriculumid as pgmid,
                       pgm.name as pgmname,
                       pgm.reqcredits as pgmreqcredits,
                       count(pgmcrsset.id) as numcrssets
                  FROM {'.\curriculumstudent::TABLE.'} pgmstu
                  JOIN {'.\curriculum::TABLE.'} pgm ON pgm.id = pgmstu.curriculumid
                  JOIN {'.\curriculumcourse::TABLE.'} pgmcrs ON pgmcrs.curriculumid = pgm.id
             LEFT JOIN {'.\programcrsset::TABLE.'} pgmcrsset ON pgmcrsset.prgid = pgm.id
                       '.implode(' ', $joins).'
                 WHERE '.$restrictions.'
              GROUP BY pgm.id
              ORDER BY pgm.priority ASC, pgm.name ASC';

        return $DB->get_recordset_sql($sql, $params);
    }

    /**
     * Get HTML to display the widget.
     *
     * @param bool $fullscreen Whether the widget is being displayed full-screen or not.
     * @return string The HTML to display the widget.
     */
    public function get_html($fullscreen = false) {
        global $CFG, $USER;
        require_once(\elispm::lib('data/user.class.php'));
        require_once(\elispm::lib('lib.php'));
        pm_update_user_information($USER->id);

        $uniqid = uniqid();

        $html = \html_writer::start_tag('div', ['id' => $uniqid]);

        $config = get_config('eliswidget_featuredcourses');
        $euserid = \user::get_current_userid();

        if (!empty($config->progressbarenabled)) {
            $html .= '<style>';
            $html .= '.eliswidget_featuredcourses svg.elisprogress rect.colorcode1 { fill: '.$config->progressbarcolor1.'; }';
            $html .= '.eliswidget_featuredcourses svg.elisprogress rect.colorcode2 { fill: '.$config->progressbarcolor2.'; }';
            $html .= '.eliswidget_featuredcourses svg.elisprogress rect.colorcode3 { fill: '.$config->progressbarcolor3.'; }';
            $html .= '</style>';
        }

        // Add assigned programs.
        $programdata = $this->get_program_data($euserid);
        foreach ($programdata as $program) {
            $pgmwrapperattrs = [
                'id' => 'program_'.$program->pgmid,
                'class' => 'program',
                'data-id' => $program->pgmid,
                'data-numcrssets' => $program->numcrssets
            ];
            $html .= \html_writer::start_tag('div', $pgmwrapperattrs);
            if (!empty($program->pgmreqcredits) && $program->pgmreqcredits > 0) {
                if (!empty($config->progressbarenabled)) {
                    $pgmstu = new \curriculumstudent(['curriculumid' => $program->pgmid, 'userid' => $euserid]);
                    $pgmstu->load();
                    $html .= $this->generateprogressbar($pgmstu->get_percent_complete());
                }
            }
            $html .= \html_writer::tag('h5', $program->pgmname, ['class' => 'header']);
            $html .= \html_writer::tag('div', '', ['class' => 'childrenlist']);
            $html .= \html_writer::end_tag('div');
        }

        $enrolallowed = get_config('enrol_elis', 'enrol_from_course_catalog');
        $enrolallowed = (!empty($enrolallowed) && $enrolallowed == '1') ? '1' : '0';
        $unenrolallowed = get_config('enrol_elis', 'unenrol_from_course_catalog');
        $unenrolallowed = (!empty($unenrolallowed) && $unenrolallowed == '1') ? '1' : '0';

        $initopts = [
            'endpoint' => $CFG->wwwroot.'/local/elisprogram/widgets/featuredcourses/ajax.php',
            'enrolallowed' => $enrolallowed,
            'unenrolallowed' => $unenrolallowed,
            'lang' => [
                'status_available' => get_string('status_available', 'eliswidget_featuredcourses'),
                'status_notenroled' => get_string('status_notenroled', 'eliswidget_featuredcourses'),
                'status_enroled' => get_string('status_enroled', 'eliswidget_featuredcourses'),
                'status_passed' => get_string('status_passed', 'eliswidget_featuredcourses'),
                'status_failed' => get_string('status_failed', 'eliswidget_featuredcourses'),
                'status_unavailable' => get_string('status_unavailable', 'eliswidget_featuredcourses'),
                'status_waitlist' => get_string('status_waitlist', 'eliswidget_featuredcourses'),
                'status_prereqnotmet' => get_string('status_prereqnotmet', 'eliswidget_featuredcourses'),
                'more' => get_string('more', 'eliswidget_featuredcourses'),
                'less' => get_string('less', 'eliswidget_featuredcourses'),
                'coursesets' => get_string('coursesets', 'eliswidget_featuredcourses'),
                'courses' => get_string('courses', 'eliswidget_featuredcourses'),
                'classes' => get_string('classes', 'eliswidget_featuredcourses'),
                'data_status' => get_string('data_status', 'eliswidget_featuredcourses'),
                'data_grade' => get_string('data_grade', 'eliswidget_featuredcourses'),
                'data_instructors' => get_string('data_instructors', 'eliswidget_featuredcourses'),
                'action_unenrol' => get_string('action_unenrol', 'eliswidget_featuredcourses'),
                'action_leavewaitlist' => get_string('action_leavewaitlist', 'eliswidget_featuredcourses'),
                'action_enrol' => get_string('action_enrol', 'eliswidget_featuredcourses'),
                'working' => get_string('working', 'eliswidget_featuredcourses'),
                'nonefound' => get_string('nonefound', 'eliswidget_featuredcourses'),
                'generatortitle' => get_string('generatortitle', 'eliswidget_featuredcourses'),
                'cancel' => get_string('cancel'),
                'enddate' => get_string('enddate', 'eliswidget_featuredcourses'),
                'enrol_confirm_enrol' => get_string('enrol_confirm_enrol', 'eliswidget_featuredcourses'),
                'enrol_confirm_leavewaitlist' => get_string('enrol_confirm_leavewaitlist', 'eliswidget_featuredcourses'),
                'enrol_confirm_title' => get_string('enrol_confirm_title', 'eliswidget_featuredcourses'),
                'enrol_confirm_unenrol' => get_string('enrol_confirm_unenrol', 'eliswidget_featuredcourses'),
                'idnumber' => get_string('idnumber', 'eliswidget_featuredcourses'),
                'startdate' => get_string('startdate', 'eliswidget_featuredcourses'),
                'yes' => get_string('yes'),
            ],
        ];
        $initjs = "\n(function($) {"."\n";
        $initjs .= "$(function() { ";
        $initjs .= "$('#".$uniqid."').parents('.eliswidget_featuredcourses').eliswidget_featuredcourses(".json_encode($initopts)."); ";
        $initjs .= "});\n";
        $initjs .= "\n".'})(jQuery); jQuery.noConflict()';
        $html .= \html_writer::tag('script', $initjs);

        $html .= \html_writer::end_tag('div');
        return $html;
    }

    /**
     * Get an array of CSS files that are needed by the widget.
     *
     * @param bool $fullscreen Whether the widget is being displayed full-screen or not.
     * @return array Array of URLs or \moodle_url objects to require for the widget.
     */
    public function get_css_dependencies($fullscreen = false) {
        return [new \moodle_url('/local/elisprogram/widgets/featuredcourses/css/widget.css'),
                new \moodle_url('/lib/jquery/ui-1.10.4/css/base/jquery-ui.min.css')
        ];
    }

    /**
     * Get an array of javascript files that are needed by the widget and must be loaded in the head of the page.
     *
     * @param bool $fullscreen Whether the widget is being displayed full-screen or not.
     * @return array Array of URLs or \moodle_url objects to require for the widget.
     */
    public function get_js_dependencies_head($fullscreen = false) {
        return [
                new \moodle_url('/local/elisprogram/lib/deepsight/js/jquery-1.9.1.min.js'),
                new \moodle_url('/local/elisprogram/lib/deepsight/js/jquery-ui-1.10.1.custom.min.js')
        ];
    }

    /**
     * Get an array of js files that are needed by the widget.
     *
     * @param bool $fullscreen Whether the widget is being displayed full-screen or not.
     * @return array Array of URLs or \moodle_url objects to require for the widget.
     */
    public function get_js_dependencies($fullscreen = false) {
        return [
                new \moodle_url('/local/elisprogram/lib/deepsight/js/support.js'),
                new \moodle_url('/local/elisprogram/lib/deepsight/js/filters/deepsight_filterbar.js'),
                new \moodle_url('/local/elisprogram/lib/deepsight/js/filters/deepsight_filter_generator.js'),
                new \moodle_url('/local/elisprogram/lib/deepsight/js/filters/deepsight_filter_textsearch.js'),
                new \moodle_url('/local/elisprogram/lib/deepsight/js/filters/deepsight_filter_date.js'),
                new \moodle_url('/local/elisprogram/lib/deepsight/js/filters/deepsight_filter_searchselect.js'),
                new \moodle_url('/local/elisprogram/widgets/featuredcourses/js/widget.js'),
        ];
    }
}