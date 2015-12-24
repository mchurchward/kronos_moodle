<?php
/**
 * ELIS(TM): Enterprise Learning Intelligence Suite
 * Copyright (C) 2008-2015 Remote-Learner.net Inc (http://www.remote-learner.net)
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
 * @package    local_elisprogram
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2015 Remote Learner.net Inc http://www.remote-learner.net
 */

/**
 * A filter providing selection of a single value.
 */
class kronos_track_enroled_switch extends deepsight_filter_switch {
    const TYPE = 'switch_kronos_trackenrol';
    public static function get_custom_choices() {
        $langshowenrolall = get_string('showenrolall', 'eliswidget_trackenrol');
        $langshowenrol = get_string('showenrol', 'eliswidget_trackenrol');
        return array('onlyenrol' => $langshowenrol, 'all' => $langshowenrolall);
    }

    /**
     * Gets filter SQL based on the assigned fields, and chosen values.
     *
     * @param mixed $data The data from the filter send from the javascript.
     * @return array An array of filter SQL, and SQL parameters.
     */
    public function get_filter_sql($data) {
        return array('', array());
    }
}