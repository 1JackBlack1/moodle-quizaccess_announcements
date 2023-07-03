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
 * AJAX calls for the quizaccess_announcements plugin.
 *
 * @module     quizaccess_announcements/monitor
 * @copyright  Jeffrey Black
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {call} from 'core/ajax';

/*
 * WebService to get student status for monitor.php.
 */
export const getstatus = (quizid) => call([{
    methodname: 'quizaccess_announcements_get_student_status',
    args: {quizid},
}])[0];

/*
 * WebService to get the current announcements for a quiz attempt.
 */
export const getannouncements = (quizid, lasttime) => call([{
    methodname: 'quizaccess_announcements_get_announcements',
    args: {
        quizid,
        lasttime
    },
}])[0];
