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
 * announcement manager class for the quizaccess_announcements plugin.
 *
 * @package    quizaccess
 * @subpackage announcements
 * @copyright  Jeffrey Black
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quizaccess_announcements;
use context_module;
use stdClass;

class announcement_manager {
    /** raw announcements from DB */
    private $rawannouncements = [];
    /** Announcements passed through format_text */
    private $formattedannouncements = [];
    /** final rendered announcements */
    private $renderedannouncements = [];
    /** id for the quiz */
    private $quizid;
    /** The context module for the quiz */
    private $context;
    /** Bool indicating if announcements have been loaded */
    private $loaded = false;
    /** Bool indicating if announcements have been rendered */
    private $rendered = false;
    /** table of format options */
    private $formatoptions = [];

    /**
     * Constructor, gets the quiz and context, and sets up format options.
     *
     * @param int $quizid the id of the quiz
     * @param object $context the context module for the quiz.
     */
    public function __construct($quizid, $context) {
        $this->quizid = $quizid;
        $this->context = $context;
        $this->formatoptions = ['noclean' => true, 'context' => $context];
    }

    /**
     * Called to check announcements are loaded. Will load if not.
     */
    private function ensure_loaded() {
        if (!$this->loaded) {
            $this->load_announcements();
        }
    }

    /**
     * Loads announcements from db as calls function to format them.
     */
    private function load_announcements() {
        $this->load_announcements_from_db();
        $this->formattedannouncements = $this->format_announcements($this->rawannouncements);
        $this->loaded = true;
    }

    /**
     * Loads announcements from db.
     */
    private function load_announcements_from_db() {
        global $DB;
        $this->rawannouncements = $DB->get_records('quizaccess_announcements_ann',
            ['quizid' => $this->quizid], 'timeposted');
    }

    /**
     * Loads unseen announcements from database.
     *
     * @param int $time unix timestamp of last time announcements were seen.
     * @return objects[] array of raw announcement objects.
     */
    private function load_new_announcements_from_db($time) {
        global $DB;
        $sql = "SELECT *
                  FROM {quizaccess_announcements_ann}
                 WHERE quizid = :quizid AND timeposted >= :time
                 ORDER BY timeposted ASC";
        $params = ['quizid' => $this->quizid, 'time' => $time];
        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Formats provided announcements.
     *
     * @param object[] $announcements array of raw annoncement objects.
     * @return object[] array of formatted announcement objects.
     */
    private function format_announcements($announcements) {
        $formatted = [];
        foreach ($announcements as $announcement) {
            $formatted[] = $this->format_announcement($announcement);
        }
        return $formatted;
    }

    /**
     * Formats provided announcement.
     *
     * @param object $announcement raw annoncement objects.
     * @return object formatted announcement objects.
     */
    public function format_announcement($announcement) {
        $formatted = new stdClass();
        $formatted->id = $announcement->id;
        $formatted->timeposted = $announcement->timeposted;
        $rewritten = file_rewrite_pluginfile_urls(
            $announcement->content,
            'pluginfile.php',
            $this->context->id,
            'quizaccess_announcements',
            'announcement',
            $announcement->id);
        $formatted->content = format_text(
            $rewritten,
            $announcement->contentformat,
            $this->formatoptions);
        return $formatted;
    }

    /**
     * Gets a display string for time.
     *
     * @param int $time unix timestamp.
     * @return string formatted text showing time.
     */
    public static function get_display_time($time) {
        $time = (int)$time;
        $midnight = usergetmidnight(time());
        $timeformat = '';
        if ($midnight > $time) {
            $timeformat = get_string('strftimedatetimeshort', 'core_langconfig');
        } else {
            $timeformat = get_string('strftimetime24', 'core_langconfig');
        }
        return userdate($time, $timeformat);
    }

    /**
     * Called to check announcements are rendered. Will render if not.
     */
    private function ensure_rendered() {
        if (!$this->rendered) {
            $this->ensure_loaded();
            $this->renderedannouncements = $this->render_announcements($this->formattedannouncements);
            $this->rendered = true;
        }
    }

    /**
     * Renders provided announcements.
     * Used as function here rather than as subset of template, as it may be required twice.
     *
     * @param object[] $announcements array of formatted annoncement objects.
     * @return object[] array of rendered announcement objects.
     */
    public function render_announcements($announcements) {
        $output = [];
        foreach ($announcements as $announcement) {
            $output[] = self::render_announcement($announcement);
        }
        return $output;
    }

    /**
     * Renderes provided announcement.
     *
     * @param object $announcement formatted annoncement object.
     * @return object rendered announcement object.
     */
    public static function render_announcement($announcement) {
        global $OUTPUT;
        $data = new stdClass();
        $data->content = $announcement->content;
        $data->timeposted = self::get_display_time($announcement->timeposted);
        $rendered = new stdClass();
        $rendered->id = $announcement->id;
        $rendered->timeposted = $announcement->timeposted;
        $rendered->content = $OUTPUT->render_from_template('quizaccess_announcements/announcement', $data);
        return $rendered;
    }

    /**
     * Gets announcements for the quiz.
     *
     * @return object[] array of raw announcement objects.
     */
    public function get_announcements() {
        $this->ensure_loaded();
        return $this->rawannouncements;
    }

    /**
     * Gets announcements for the quiz for output.
     *
     * @return object[] array of rendered announcement objects.
     */
    public function get_rendered_announcements() {
        $this->ensure_rendered();
        return $this->renderedannouncements;
    }

    /**
     * Gets announcements for the quiz for output as string.
     *
     * @return string HTML of rendered announcements.
     */
    public function get_rendered_announcements_html() {
        $this->ensure_rendered();
        return implode('', array_column($this->renderedannouncements, 'content'));
    }

    /**
     * Helper to make a function to filter announcements by time.
     *
     * @param int $lasttime time to exclude announcements before.
     * @return function returns true if announcement time is greater than $lasttime.
     */
    private static function make_array_filter($lasttime) {
        $lasttime = (int)$lasttime;
        return function($announcement) use ($lasttime) {
            return $announcement->timeposted >= $lasttime;
        };
    }

    /**
     * Gets unseen announcements for the quiz for output as string.
     *
     * @param int $lasttime time to exclude announcements before.
     * @return string HTML of rendered announcements.
     */
    public function get_rendered_new_announcements_html($lasttime) {
        $new = [];
        if ($this->rendered) {
            $new = array_filter(
                $this->renderedannouncements,
                self::make_array_filter($lasttime));
        } else if ($this->loaded) {
            $new = array_filter(
                $this->formattedannouncements,
                self::make_array_filter($lasttime));
            $new = $this->render_announcements($new);
        } else {
            $new = $this->load_new_announcements_from_db($lasttime);
            $new = $this->format_announcements($new);
            $new = $this->render_announcements($new);
        }
        return implode('', array_column($new, 'content'));
    }
}
