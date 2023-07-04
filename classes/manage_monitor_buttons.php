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

namespace quizaccess_announcements;
use moodle_url;

/**
 * Class to avoid code duplication for the quizaccess_announcements plugin.
 *
 * @package    quizaccess_announcements
 * @copyright  Jeffrey Black
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manage_monitor_buttons {
    /**
     * Gets the buttons for staff associated with the quiz
     *
     * @param int $quizid the id for the quiz.
     * @param module $context the context module for the quiz.
     * @return string html of buttons for staff.
     */
    public static function buttons($quizid, $context) {
        $output = '';
        $output .= self::manage_button($quizid, $context);
        $output .= self::monitor_button($quizid, $context);
        return $output;
    }

    /**
     * Gets the manage announcement button
     *
     * @param int $quizid the id for the quiz.
     * @param module $context the context module for the quiz.
     * @return string html of manage announcement button.
     */
    public static function manage_button($quizid, $context) {
        if (!(has_capability('quizaccess/announcements:make_announcement', $context) ||
                has_capability('quizaccess/announcements:delete_announcement', $context))) {
            return '';
        }
        $url = '/mod/quiz/accessrule/announcements/announcements.php';
        $text = get_string('manage_button_text', 'quizaccess_announcements');
        return self::button($quizid, $url, $text);
    }

    /**
     * Gets the monitor status button
     *
     * @param int $quizid the id for the quiz.
     * @param module $context the context module for the quiz.
     * @return string html of monitor status button.
     */
    public static function monitor_button($quizid, $context) {
        $output = '';
        if (!(has_capability('quizaccess/announcements:view_status', $context))) {
            return '';
        }
        $url = '/mod/quiz/accessrule/announcements/monitor.php';
        $text = get_string('monitor_button_text', 'quizaccess_announcements');
        return self::button($quizid, $url, $text);
    }

    /**
     * Renders a button.
     *
     * @param int $quizid the id for the quiz.
     * @param string $url the base url string for this button.
     * @param string $text the text to display on the button.
     * @return string html of the desired button.
     */
    private static function button($quizid, $url, $text) {
        global $OUTPUT;
        $params = ['quizid' => $quizid];
        $murl = new moodle_url($url, $params);
        return $OUTPUT->single_button($murl, $text, 'get');
    }
}
