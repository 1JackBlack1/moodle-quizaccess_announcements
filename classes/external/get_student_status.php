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
 * Define get_student_status service for the quizaccess_announcements plugin.
 *
 * @package    quizaccess
 * @subpackage announcements
 * @copyright  Jeffrey Black
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace quizaccess_announcements\external;

// Aliases for Moodle < 4.2.
// Means @runInSeparateProcess is needed for tests.
if (!(class_exists('\core_external\external_api'))) {
    class_alias('external_api', '\core_external\external_api');
    class_alias('external_function_parameters', '\core_external\external_function_parameters');
    class_alias('external_single_structure', '\core_external\external_single_structure');
    class_alias('external_multiple_structure', '\core_external\external_multiple_structure');
    class_alias('external_value', '\core_external\external_value');
}

use context_module;
use DateTime;
use stdClass;

class get_student_status extends \core_external\external_api {

    /**
     * External function parameters.
     *
     * @return \core_external\external_function_parameters
     */
    public static function execute_parameters(): \core_external\external_function_parameters {
        return new \core_external\external_function_parameters([
           'quizid' => new \core_external\external_value(PARAM_INT, 'Quiz ID',
                VALUE_REQUIRED, null, NULL_NOT_ALLOWED)
        ]);
    }

    /**
     * Get last time students have accessed quiz.
     *
     * @param string $quizid Quiz ID.
     * @param string $last timestamp of the last announcement that had been fetched.
     * @return array with 2 elements:
     *     1. last: the details of the last announcement.
     *     2. status: the details of student status.
     */
    public static function execute(string $quizid ): array {
        global $DB;
        list('quizid' => $quizid) = self::validate_parameters(
                self::execute_parameters(),
                ['quizid' => $quizid]);

        $context = context_module::instance(
            get_coursemodule_from_instance(
            'quiz', $quizid, false, false, MUST_EXIST)->id);
        self::validate_context($context);
        if (!has_capability('quizaccess/announcements:make_announcement', $context)) {
            throw new moodle_exception('cantmonitor', 'quizaccess_announcements');
        }
        $now = time();
        $last = \quizaccess_announcements\last_posted::get_obj($quizid, $now);
        $stats = $DB->get_records_sql(
            'SELECT userid, timefetched ' .
            'FROM {quizaccess_announcements_sta} ' .
            'WHERE quizid = :quizid ' .
            'ORDER BY timefetched',
            ['quizid' => $quizid]);
        $formattedstats = [];
        foreach ($stats as $stat) {
            $stattime = (int)$stat->timefetched;
            $formatted = new stdClass();
            $formatted->userid = $stat->userid;
            $formatted->time = $stattime;
            $formatted->str = \quizaccess_announcements\announcement_manager::get_display_time($stattime);
            $formatted->ago = $now - $stattime;
            $formattedstats[] = $formatted;
        }
        $result = ['last' => $last, 'status' => $formattedstats];
        return $result;
    }

    /**
     * External function returns.
     *
     * @return \core_external\external_single_structure
     */
    public static function execute_returns(): \core_external\external_single_structure {
        return new \core_external\external_single_structure([
            'last' => new \core_external\external_single_structure([
                'time' => new \core_external\external_value(PARAM_INT, 'The timestamp for the last announcement',
                    VALUE_REQUIRED, 0, NULL_ALLOWED),
                'str' => new \core_external\external_value(PARAM_RAW, 'String to appear above table',
                    VALUE_REQUIRED, 0, NULL_NOT_ALLOWED),
                ]),
            'status' => new \core_external\external_multiple_structure(new \core_external\external_single_structure([
                'userid' => new \core_external\external_value(PARAM_INT, 'The id of the user.',
                    VALUE_REQUIRED, 0, NULL_NOT_ALLOWED),
                'time' => new \core_external\external_value(PARAM_INT, 'The timestamp the student fetched the announcements.',
                    VALUE_REQUIRED, 0, NULL_NOT_ALLOWED),
                'str' => new \core_external\external_value(PARAM_RAW, 'The formatted time the student fetched the announcements.',
                    VALUE_REQUIRED, 0, NULL_NOT_ALLOWED),
                'ago' => new \core_external\external_value(PARAM_INT, 'How many seconds ago the student fetched the announcements.',
                    VALUE_REQUIRED, 0, NULL_NOT_ALLOWED),
            ]))
        ]);
    }
}

