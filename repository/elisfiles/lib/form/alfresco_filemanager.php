<?php
/**
 * ELIS(TM): Enterprise Learning Intelligence Suite
 * Copyright (C) 2013 onwards Remote-Learner.net Inc (http://www.remote-learner.net)
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
 * @package    repository_elisfiles
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2008-2016 Remote-Learner.net Inc (http://www.remote-learner.net)
 *
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once("{$CFG->libdir}/form/filemanager.php");

class alfresco_filemanager extends MoodleQuickForm_filemanager {
    /**
     * the name used to identify the formslib element
     */
    const NAME = 'alfresco_filemanager';

    /**
     * Construct a alfresco_filemanager.
     *
     * @param string $elementName Element's name
     * @param mixed $elementLabel Label(s) for an element
     * @param array $options Options to control the element's display
     * @param mixed $attributes Either a typical HTML attribute string or an associative array
     */
    public function __construct($elementName=null, $elementLabel=null, $attributes=null, $options=null) {
        parent::__construct($elementName, $elementLabel, $attributes, $options);
    }

    /**
     * Due to implementation in: lib/pear/HTML/QuickForm/element.php on line 363
     * This 'classname' method is required for Moodle.
     * Construct an alfresco_filemanager.
     *
     * @param string $elementname Element's name
     * @param mixed $elementlabel Label(s) for an element
     * @param array $options Options to control the element's display
     * @param mixed $attributes Either a typical HTML attribute string or an associative array
     */
    public function alfresco_filemanager($elementname=null, $elementlabel=null, $attributes=null, $options=null) {
        return self::__construct($elementname, $elementlabel, $attributes, $options);
    }
}

/* first argument is the string that will be used to identify the element.
 * second argument is the filename that contains the class definition
 * third argument is the class name
 */
MoodleQuickForm::registerElementType(alfresco_filemanager::NAME, __FILE__, 'alfresco_filemanager');

