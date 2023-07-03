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

$add = optional_param('add', false, PARAM_BOOL);
$quizid = optional_param('quizid', null, PARAM_INT);
$cmid = optional_param('cmid', null, PARAM_INT);
$cm;

// Identify quiz.
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

// Determine if they can add or delete.
$canadd = has_capability('quizaccess/announcements:make_announcement', $context);
$candel = has_capability('quizaccess/announcements:delete_announcement', $context);

if (!$canadd && !$candel) {
    throw new moodle_exception('cantaddordel', 'quizaccess_announcements');
}

// Setup output.
$title = get_string('manage_announcements', 'quizaccess_announcements', format_string($cm->name));
$pagetitle = $title;
$url = new moodle_url("/mod/quiz/accessrule/announcements/announcements.php", ['quizid' => $quizid]);
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);
$PAGE->activityheader->disable();
$PAGE->add_body_class('limitedwidth');

// Get rule and associated bits.
$rule = $DB->get_record('quizaccess_announcements_qui', ['quizid' => $quizid]);
if (empty($rule)) {
    throw new moodle_exception('notconfigured', 'quizaccess_announcements');
}
$anonman = new quizaccess_announcements\announcement_manager($quizid, $context);
$announcements = $anonman->get_rendered_announcements();

// Setup add form.
$form = null;
if ($canadd) {
    $form = new quizaccess_announcements\add_announcement_form($quizid, $context);
    $form->set_data([]);
    if ($fromform = $form->get_data()) {
        $transaction = $DB->start_delegated_transaction();
        $new = new stdClass();
        $new->quizid = 0;
        $new->content = "&nbsp;";
        $new->contentformat = $fromform->content['format'];
        $new->timeposted = 0;
        $new = $DB->insert_record('quizaccess_announcements_ann', $new);
        $new = $DB->get_record('quizaccess_announcements_ann', ["id" => $new]);
        $new->quizid = $fromform->quizid;
        $new->content = file_save_draft_area_files(
            $fromform->content['itemid'],
            $context->id,
            'quizaccess_announcements',
            'announcement',
            $new->id,
            ['subdirs' => true],
            $fromform->content['text']
        );
        $new->timeposted = time();
        $DB->update_record('quizaccess_announcements_ann', $new);
        $transaction->allow_commit();
        $event = \quizaccess_announcements\event\announcement_created::create(['context' => $context, 'objectid' => $new->id]);
        $event->trigger();
        redirect(new moodle_url("announcements.php", ['quizid' => $quizid]));
    }
}

// Output.
$output = $PAGE->get_renderer('quizaccess_announcements');
$output->candel = $candel;
echo $output->manage_announcements_page($rule, $context, $announcements, $form);
