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
 * Page to manage announcements for the quizaccess_announcements plugin.
 *
 * @package    quizaccess_announcements
 * @copyright  Jeffrey Black
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require(__DIR__ . '/../../../../config.php');

$quizid = optional_param('quizid', null, PARAM_INT);
$cmid = optional_param('cmid', null, PARAM_INT);
$cm;

// Identify quiz and check permissions.
if (empty($quizid) && empty($cmid)) {
    throw new moodle_exception('noquizspecified', 'quizaccess_announcements');
}
if (!empty($quizid)) {
    $cm = get_coursemodule_from_instance('quiz', $quizid);
    if (!$cm) {
        throw new moodle_exception('invalidquizid', 'quizaccess_announcements');
    }
    if (!empty($cmid) && ($cmid != $cm->id)) {
        throw new moodle_exception('quizcmidmismatch', 'quizaccess_announcements');
    }
} else {
    $cm = get_coursemodule_from_id('quiz', $cmid);
    if (!$cm) {
        throw new moodle_exception('invalidcmid', 'quizaccess_announcements');
    }
    $quizid = $cm->instance;
}
require_login($cm->course, true, $cm);

$context = context_module::instance($cm->id);

if (!has_capability('quizaccess/announcements:make_announcement', $context)) {
    throw new moodle_exception('cantmonitor', 'quizaccess_announcements');
}
$quizid = $cm->instance;

// Setup output.
$title = get_string('monitor_title', 'quizaccess_announcements', format_string($cm->name));
$pagetitle = $title;
$url = new moodle_url("/mod/quiz/accessrule/announcements/monitor.php", ['quizid' => $quizid]);
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->activityheader->disable();
$rule = $DB->get_record('quizaccess_announcements_qui', ['quizid' => $quizid]);
if (empty($rule)) {
    throw new moodle_exception('notconfigured', 'quizaccess_announcements');
}
$output = $PAGE->get_renderer('quizaccess_announcements');

// Use a consistent "Now" time for everything.
$now = time();
// Get last announcement and setup table and output.
$lastposted = \quizaccess_announcements\last_posted::get_obj($quizid, $now);
$table = new \quizaccess_announcements\status_table('quizaccess_announcements_status',
    $context, $quizid, $url, $lastposted, $rule->checkinterval, $now);
$output->monitor_page($rule, $context, $lastposted, $table, $now);
$event = \quizaccess_announcements\event\student_status_viewed::create(['context' => $context]);
$event->trigger();
