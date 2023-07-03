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
 * Define get_announcement service for the quizaccess_announcements plugin.
 *
 * @package    quizaccess_announcements
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
    class_alias('external_value', '\core_external\external_value');
}

use quizaccess_announcements\announcement_manager;
use context_module;

class get_announcements extends \core_external\external_api {

    /**
     * External function parameters.
     *
     * @return \core_external\external_function_parameters
     */
    public static function execute_parameters(): \core_external\external_function_parameters {
        return new \core_external\external_function_parameters([
           'quizid' => new \core_external\external_value(PARAM_INT, 'Quiz course module ID',
                VALUE_REQUIRED, null, NULL_NOT_ALLOWED),
           'lasttime' => new \core_external\external_value(PARAM_INT, 'Time last announcement was fetched',
                VALUE_REQUIRED, null, NULL_NOT_ALLOWED)
        ]);
    }

    /**
     * Get announcements for a quiz.
     *
     * @param string $quizid Quiz ID.
     * @param string $last timestamp of the last announcement that had been fetched.
     * @return array with 2 elements:
     *     1. content: the HTML of any new announcements.
     *     2. lasttime: the unix timestamp the announcements were fetched.
     */
    public static function execute(string $quizid, string $lasttime): array {
        list('quizid' => $quizid, 'lasttime' => $lasttime
            ) = self::validate_parameters(self::execute_parameters(),
            ['quizid' => $quizid, 'lasttime' => $lasttime]);
        global $DB;
        global $USER;

        $context = context_module::instance(
            get_coursemodule_from_instance(
            'quiz', $quizid, false, false, MUST_EXIST)->id);
        self::validate_context($context);

        $now = time();
        $anonman = new \quizaccess_announcements\announcement_manager($quizid, $context);
        $newanon = $anonman->get_rendered_new_announcements_html($lasttime);
        $result = ['content' => $newanon, 'lasttime' => $now];
        $last = $DB->get_record('quizaccess_announcements_sta',
            ["quizid" => $quizid, "userid" => $USER->id]);
        if ($last) {
            $last->previousfetch = $last->timefetched;
            $last->timefetched = $now;
            $DB->update_record('quizaccess_announcements_sta', $last);
        }
        $event = \quizaccess_announcements\event\announcements_viewed::create(['context' => $context]);
        $event->trigger();
        return $result;
    }

    /**
     * External function returns.
     *
     * @return \core_external\external_single_structure
     */
    public static function execute_returns(): \core_external\external_single_structure {
        return new \core_external\external_single_structure([
            'content' => new \core_external\external_value(PARAM_RAW, 'HTML of new announcements',
                    VALUE_REQUIRED, 0, NULL_NOT_ALLOWED),
            'lasttime' => new \core_external\external_value(PARAM_INT, 'timestamp of last time announcement was fetched',
                VALUE_REQUIRED, 0, NULL_NOT_ALLOWED)
        ]);
    }
}

