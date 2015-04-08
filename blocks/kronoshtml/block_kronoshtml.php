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
 * Kronos HTML block.
 *
 * @package    block_kronoshtml
 * @author     Remote-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (C) 2014 Remote Learner.net Inc http://www.remote-learner.net
 */
class block_kronoshtml extends block_base {
    /**
     * The maximum userset depth allowed.
     */
    const MAXDEPTH = 10;

    /**
     * The minimum depth a user set can have (the root user set).
     */
    const MINDEPTH = 1;

    /** @var string Holds the block language string name. */
    public $blockname = null;

    /**
     * Set the initial properties for the block
     */
    public function init() {
        $this->blockname = get_class($this);
        $this->title = get_string('pluginname', $this->blockname);
    }

    /**
     * Overridden from parent class.  This block has a global settings page.
     */
    public function has_config() {
        return true;
    }

    /**
     * Overridden from parent class. Sets the formats.
     */
    public function applicable_formats() {
        return array('all' => true);
    }

    /**
     * Overridden from parent class. This game sets the formats.
     */
    public function specialization() {
        $this->title = isset($this->config->title) ? format_string($this->config->title) : format_string(get_string('newkronoshtml', $this->blockname));
    }

    /**
     * Overridden from parent class. Set multiple instances.
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Overridden from parent class.
     */
    public function get_content() {
        global $CFG, $DB, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        require_once($CFG->libdir.'/filelib.php');
        require_once($CFG->dirroot.'/local/elisprogram/lib/data/usermoodle.class.php');
        require_once($CFG->dirroot.'/local/elisprogram/lib/data/userset.class.php');

        $elisuser = new usermoodle(false);
        $display = true;

        // If the user doesn't have the capability to edit the block.  Find the user's User Set and determine if they block is displayed to them.
        if (!has_capability('moodle/block:edit', $this->context)) {
            $display = false;

            $record = $DB->get_record(usermoodle::TABLE, array('muserid' => $USER->id));

            if (empty($record) || !isset($this->config->userset) || empty($this->config->userset)) {
                return $this->content;
            }

            // Check each userset the user is assigned to.
            $usersets = cluster_get_user_clusters($record->cuserid);
            foreach ($usersets as $userset) {
                // Check if the user's current userset matches the userset configured for this instance of the block.
                if ($userset->clusterid == $this->config->userset) {
                    $display = true;
                    break;
                }

                // If not and the depth of the userset is greater then we need to check if the parent userset ids match the the userset configured for this block.
                $usersetinstance = new userset($userset->clusterid);
                $depth = $usersetinstance->depth;

                // If the user's userset is deeper than the maximum then break out of this loop.
                if (self::MAXDEPTH < $depth) {
                    break;
                }

                for ($i = $depth; $i >= self::MINDEPTH; $i--) {
                    if ($this->config->userset == $usersetinstance->id) {
                        $display = true;
                        break;
                    }

                    $usersetinstance = new userset($usersetinstance->parent);
                }
            }

            $usersets->close();
        }

        if (!$display) {
            return $this->content;
        }

        $filteropt = new stdClass;
        $filteropt->overflowdiv = true;
        if ($this->content_is_trusted()) {
            // Fancy html allowed only on course, category and system blocks.
            $filteropt->noclean = true;
        }

        $this->content = new stdClass;
        $this->content->footer = '';
        if (isset($this->config->text)) {
            // Rewrite url.
            $this->config->text = file_rewrite_pluginfile_urls($this->config->text, 'pluginfile.php', $this->context->id, 'block_kronoshtml', 'content', null);
            // Default to FORMAT_HTML which is what will have been used before the editor was properly implemented for the block.
            $format = FORMAT_HTML;
            // Check to see if the format has been properly set on the config.
            if (isset($this->config->format)) {
                $format = $this->config->format;
            }
            $this->content->text = format_text($this->config->text, $format, $filteropt);
        } else {
            $this->content->text = '';
        }

        // Memory footprint.
        unset($filteropt);

        return $this->content;
    }

    /**
     * Overridden from parent class.  Serialize and store config data
     */
    public function instance_config_save($data, $nolongerused = false) {
        global $DB;

        $config = clone($data);
        // Move embedded files into a proper filearea and adjust HTML links to match.
        $config->text = file_save_draft_area_files($data->text['itemid'], $this->context->id, 'block_html', 'content', 0, array('subdirs' => true), $data->text['text']);
        $config->format = $data->text['format'];

        parent::instance_config_save($config, $nolongerused);
    }

    /**
     * Overridden from parent class.  Delete file area.
     */
    public function instance_delete() {
        global $DB;
        $fs = get_file_storage();
        $fs->delete_area_files($this->context->id, 'block_html');
        return true;
    }

    /**
     * Overridden from parent class.
     */
    public function content_is_trusted() {
        global $SCRIPT;

        if (!$context = context::instance_by_id($this->instance->parentcontextid, IGNORE_MISSING)) {
            return false;
        }
        // Find out if this block is on the profile page.
        if ($context->contextlevel == CONTEXT_USER) {
            if ($SCRIPT === '/my/index.php') {
                // This is exception - page is completely private, nobody else may see content there that is why we allow JS here.
                return true;
            } else {
                // No JS on public personal pages, it would be a big security issue.
                return false;
            }
        }

        return true;
    }

    /**
     * Overridden from parent class.  The block should only be dockable when the title of the block is not empty
     * and when parent allows docking.
     */
    public function instance_can_be_docked() {
        return (!empty($this->config->title) && parent::instance_can_be_docked());
    }

    /*
     * Overridden from parent class.  Add custom html attributes to aid with theming and styling
     */
    public function html_attributes() {
        global $CFG;

        $attributes = parent::html_attributes();

        if (!empty($CFG->block_kronoshtml_allowcssclasses)) {
            if (!empty($this->config->classes)) {
                $attributes['class'] .= ' '.$this->config->classes;
            }
        }

        return $attributes;
    }
}