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
 * Defines deleter class for the quizaccess_announcements plugin.
 *
 * @package    quizaccess_announcements
 * @copyright  Jeffrey Black
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace quizaccess_announcements;

class deleter {
    /**
     * Delete any a single announcement.
     *
     * @param int $contextid the id of the context instance for the quiz.
     * @param int $announcementid the id of the announcement.
     */
    public static function delete_announcement($contextid, $announcementid) {
        global $DB;
        self::delete_announcement_files($contextid, $announcementid);
        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid, 'quizaccess_announcements',
            'announcement', $announcementid);
        foreach ($files as $file) {
            $file->delete();
        }
        $DB->delete_records('quizaccess_announcements_ann',
            ['id' => $announcementid]);
    }

    /**
     * Delete files for a specific announcement.
     *
     * @param int $contextid the id of the context instance for the quiz.
     * @param int $announcementid the id of the announcement.
     */
    public static function delete_announcement_files($contextid, $announcementid) {
        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid, 'quizaccess_announcements',
            'announcement', $announcementid);
        foreach ($files as $file) {
            $file->delete();
        }
    }

    /**
     * Delete all announcements for a quiz.
     *
     * @param int $contextid the id of the context instance for the quiz.
     * @param int $quizid the id of the quiz.
     */
    public static function delete_all_announcements($contextid, $quizid) {
        global $DB;
        // First delete all announcement files.
        $announcements = $DB->get_records('quizaccess_announcements_ann',
            ['quizid' => $quizid]);
        foreach ($announcements as $announcement) {
            self::delete_announcement_files($contextid, $announcement->id);
        }
        // Then delete the actual announcements.
        $DB->delete_records('quizaccess_announcements_ann',
            ['quizid' => $quizid]);
    }

    /**
     * Delete any currently stored status for a quiz.
     *
     * @param int $quizid the id of the quiz.
     */
    public static function delete_statuses($quizid) {
        global $DB;
        $announcements = $DB->delete_records('quizaccess_announcements_sta',
            ['quizid' => $quizid]);
    }

    /**
     * Delete all information related to a quiz.
     *
     * @param int $contextid the id of the context instance for the quiz.
     * @param int $quizid the id of the quiz.
     */
    public static function delete_rule($contextid, $quizid) {
        global $DB;
        // First delete all announcements.
        self::delete_all_announcements($contextid, $quizid);
        // Then student status.
        self::delete_statuses($quizid);
        // Then the rule itself.
        $DB->delete_records('quizaccess_announcements_qui',
            ['quizid' => $quizid]);
    }
}
