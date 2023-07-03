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
 * Define services for the quizaccess_announcements plugin.
 *
 * @package    quizaccess
 * @subpackage announcements
 * @copyright  Jeffrey Black
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'quizaccess_announcements_get_announcements' => [
        'classname' => 'quizaccess_announcements\external\get_announcements',
        'description' => 'Get announcements during an exam.',
        'type' => 'read',
        'ajax' => true,
    ],
    'quizaccess_announcements_get_student_status' => [
        'classname' => 'quizaccess_announcements\external\get_student_status',
        'description' => 'Get the last time students accessed announements. Used to check for connectivity issues.',
        'type' => 'read',
        'ajax' => true,
    ]
];
