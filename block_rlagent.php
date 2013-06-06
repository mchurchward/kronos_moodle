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
 * Remote Learner Update Manager Block.
 *
 * @package    blocks
 * @subpackage rlagent
 * @author     Remoter-Learner.net Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright  (c) 2012 Remote Learner.net Inc http://www.remote-learner.net
 */
require_once($CFG->libdir .'/tablelib.php');
require_once(dirname(__FILE__) .'/lib/table_schedule.php');

/**
 * The Remote Learner agent block class
 */
class block_rlagent extends block_base {

    /**
     * Set the applicable formats for this block to all
     * @return array
     */
    function applicable_formats() {
        return array('site' => true);
    }

    /**
     * cron - Sends notification emails
     *
     * @return boolean true if all notifications were sent succesfully
     */
    function cron() {
        global $CFG, $DB;

        // Mark old updates as Skipped.
        $query = 'UPDATE {block_rlagent_schedule} SET notification='. table_schedule::NOT_SENT
               .' WHERE status != '. table_schedule::NOT_STARTED
               .' AND notification='. table_schedule::READY .' AND rundate < '. (time() - 3600);
        $DB->execute($query);

        $select = 'status NOT IN ('. table_schedule::NOT_STARTED .', '. table_schedule::IN_PROGRESS .')'
                .' AND notification='. table_schedule::READY;
        $records = $DB->get_records_select('block_rlagent_schedule', $select);

        // There should usually only be one update.
        foreach ($records as $record) {

            if (($record->status == table_schedule::COMPLETED) && empty($CFG->block_rlagent_notify_on_success)) {
                $record->notification = table_schedule::NOT_SENT;
                $record->log .= "\nNotify on success disabled.  Email not sent.";

            } else {

                $data = new stdclass;
                $data->www = $CFG->wwwroot;
                $data->log = $record->log;

                if ($record->status == table_schedule::ERROR) {
                    if (! empty($CFG->block_rlagent_error)) {
                        $data->log = get_string($CFG->block_rlagent_error, $this->blockname);
                    }
                }

                $messages = array(
                    table_schedule::COMPLETED => 'completed',
                    table_schedule::ERROR     => 'error',
                    table_schedule::SKIPPED   => 'skipped',
                );

                $subject = get_string('email_sub_'.  $messages[$record->status], $this->blockname);
                $message = get_string('email_text_'. $messages[$record->status], $this->blockname, $data);
                $html    = get_string('email_html_'. $messages[$record->status], $this->blockname, $data);

                $emails = explode("\n", $CFG->block_rlagent_recipients);

                $users = $this->get_email_users($emails);

                foreach ($users as $user) {
                    ob_start();
                    $log = $user->email .' at '. userdate(time());
                    if (email_to_user($user, 'RL Update Manager', $subject, $message, $html)) {
                        $log = "\nEmail sent to $log.";
                    } else {
                        $log = "\nFailed to send email to $log:\n". ob_get_contents();
                    }
                    $record->log .= $log;
                    ob_end_flush();
                }

                $record->notification = table_schedule::SENT;

                if (empty($users)) {
                    $record->notification = table_schedule::NOT_SENT;
                    $record->log .= "\nNo valid email addresses configured.  Email not sent.";
                }

            }

            $DB->update_record('block_rlagent_schedule', $record);
        }
    }

    /**
     * Gets the content for this block
     */
    function get_content() {
        global $CFG, $DB;

        if (!has_capability('moodle/site:config', context_system::instance())) {
            return '';
        }

        if ($this->content !== NULL) {
            return $this->content;
        }



        $select = 'status = 0 AND scheduleddate >= '. time();
        $records = $DB->get_records_select('block_rlagent_schedule', $select, array(), 'scheduleddate', '*', 0, 1);

        $error = '';
        if (! empty($CFG->block_rlagent_error)) {
            $error = '<span class="warning">'. get_string('warning', $this->blockname) .'</span>'
                   .' <span class="error">'. get_string($CFG->block_rlagent_error, $this->blockname)
                   . '</br>'. get_string('updatedisabled', $this->blockname)
                   . '</span>';
        }

        if (empty($CFG->block_rlagent_enabled)) {
            $text = get_string('disabled', $this->blockname);
        } else if (sizeof($records) == 0) {
            $text = get_string('noupdate', $this->blockname);
        } else {
            $record = reset($records);
            $date = str_replace(',', ',<br />', userdate($record->scheduleddate));
            $text = get_string('nextupdate', $this->blockname, $date) .'<br />'. $date;
        }

        $settings = '<div style="float:left"><a href="'. $CFG->wwwroot .'/admin/settings.php?section=blocksettingrlagent">'
              . get_string('settings', $this->blockname) .'</a></div>';
        $schedule = '<div style="float:right"><a href="'. $CFG->wwwroot .'/blocks/rlagent/schedule.php">'
              . get_string('schedule', $this->blockname) .'</a></div>';
        $text = $error .'<div class="event clear">'. $text ."</div>\n". $settings . $schedule
              .'<br class="clear" />';

        $this->content = new stdClass;
        $this->content->text = $text;
        $this->content->footer = '';

        return $this->content;
    }

    /**
     * Get or make the user objects for the provided email addresses
     */
    function get_email_users($emails) {
        global $DB;

        $found = array();

        foreach ($emails as $key => $email) {
            $email = trim($email);
            $emails[$key] = $email;
            $found[$email] = false;
        }

        $users  = $DB->get_records_list('user', 'email', $emails);

        foreach ($users as $user) {
            $found[$user->email] = true;
        }

        foreach ($found as $email => $exist) {

            if (! $exist) {
                $user = new stdClass();
                $user->id    = 0;
                $user->email = $email;
                $users[] = $user;
            }
        }

        return $users;
    }

    /**
     * Has configuration for disabling automatic updates, scheduling updates and notifications.
     *
     * @return boolean
     */
    function has_config() {
        return true;
    }

    /**
     * Set the initial properties for the block
     */
    function init() {
        $this->blockname = get_class($this);
        $this->title = get_string('pluginname', $this->blockname);
    }

    /**
     * Are multiple instances of this block allowed?  No.
     *
     * @return bool Returns false
     */
    function instance_allow_multiple() {
        return false;
    }
}
