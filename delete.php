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

use quizaccess_announcements\announcement_manager;

$delete = optional_param('delete', 0, PARAM_INT);
$deleteall = optional_param('deleteall', false, PARAM_BOOL);
$quizid = required_param('quizid', PARAM_INT);

// Identify quiz and check permission.
$cm = get_coursemodule_from_instance('quiz', $quizid);
if (!$cm) {
    throw new moodle_exception('invalidquizid', 'quiz');
}
require_login($cm->course, true, $cm);
$context = context_module::instance($cm->id);
$candel = has_capability('quizaccess/announcements:delete_announcement', $context);
if (!$candel) {
    throw new moodle_exception('cantdel', 'quizaccess_announcements');
}
$quizid = $cm->instance;

$rule = $DB->get_record('quizaccess_announcements_qui', ['quizid' => $quizid]);
if (empty($rule)) {
    throw new moodle_exception('notconfigured', 'quizaccess_announcements');
}

// Setup output.
$title = get_string('delete_announcement_conf', 'quizaccess_announcements');
$title .= format_string($cm->name);
$pagetitle = $title;
$url = new moodle_url("/mod/quiz/accessrule/announcements/delete.php",
    ['quizid' => $quizid, 'delete' => $delete]);
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->activityheader->disable();
$PAGE->add_body_class('limitedwidth');

// Get HTML of announcements to be deleted.
$renderedannouncements = '';
$anonman = new quizaccess_announcements\announcement_manager($quizid, $context);
if (!empty($deleteall)) {
    $delete = null;
    $announcementcount = $DB->count_records('quizaccess_announcements_ann', ['quizid' => $quizid]);
    if (!$announcementcount) {
        throw new moodle_exception('nonetodel', 'quizaccess_announcements');
    }
    $renderedannouncements = $anonman->get_rendered_announcements_html();
} else {
    $announcement = $DB->get_record('quizaccess_announcements_ann', ['id' => $delete]);
    if (empty($announcement)) {
        throw new moodle_exception('announcement_not_found', 'quizaccess_announcements');
    }
    if ($quizid != $announcement->quizid) {
        throw new moodle_exception('quizmismatch', 'quizaccess_announcements');
    }
    $renderedannouncements = $anonman->format_announcement($announcement);
    $renderedannouncements = $anonman->render_announcement($renderedannouncements);
    $renderedannouncements = $renderedannouncements->content;
}

// Setup form.
$form = new quizaccess_announcements\delete_announcement_form(
    $quizid, $delete, $deleteall, $renderedannouncements);
$form->set_data([]);
if ($form->is_cancelled()) {
    redirect(new moodle_url("announcements.php", ['quizid' => $quizid]));
}
if ($fromform = $form->get_data()) {
    $transaction = $DB->start_delegated_transaction();
    if (!empty($deleteall)) {
        quizaccess_announcements\deleter::delete_all_announcements($context->id, $quizid);
        $event = \quizaccess_announcements\event\announcements_deleted::create(['context' => $context]);
        $event->trigger();
    } else {
        quizaccess_announcements\deleter::delete_announcement($context->id, $delete);
        $event = \quizaccess_announcements\event\announcement_deleted::create(['context' => $context, 'objectid' => $delete]);
        $event->trigger();
    }
    $transaction->allow_commit();
    redirect(new moodle_url("announcements.php", ['quizid' => $quizid]));
}

// Output.
$output = $PAGE->get_renderer('quizaccess_announcements');
echo $output->delete_announcement_page($form);
