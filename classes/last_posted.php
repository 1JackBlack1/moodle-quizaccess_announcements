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
 * Class to avoid code duplication for the quizaccess_announcements plugin.
 *
 * @package    quizaccess
 * @subpackage announcements
 * @copyright  Jeffrey Black
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace quizaccess_announcements;
use stdClass;

class last_posted {
    /**
     * Gets the last posted announcement as an object
     *
     * @param int $quizid the id for the quiz.
     * @param int $now the curent timestamp.
     * @return object the last announcement with some key variables.
     */
    public static function get_obj($quizid, $now) {
        global $DB;
        $lastposted = $DB->get_field_sql(
            'SELECT ann.timeposted ' .
            'FROM {quizaccess_announcements_ann} ann ' .
            'WHERE ann.quizid = :quizid ' .
            'ORDER BY ann.timeposted DESC',
            ['quizid' => $quizid],
            IGNORE_MULTIPLE);
        $last = new stdClass();
        if (!empty($lastposted)) {
            $lastposted = (int)$lastposted;
            $lastpostedtime = \quizaccess_announcements\announcement_manager::get_display_time($lastposted);
            $lastpostedago = $now - $lastposted;
            $last->time = $lastposted;
            $last->str = get_string('status_last_announcement', 'quizaccess_announcements', [
                'time' => $lastpostedtime, 'ago' => $lastpostedago]);
        } else {
            $last->time = null;
            $last->str = get_string('status_no_announcements', 'quizaccess_announcements');
        }
        return $last;
    }
}
