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

defined('MOODLE_INTERNAL') || die();

// Aliases for Moodle < 4.2.
if (!(class_exists('\mod_quiz\local\access_rule_base'))) {
    require_once($CFG->dirroot . '/mod/quiz/accessrule/accessrulebase.php');
    class_alias('\quiz_access_rule_base', '\mod_quiz\local\access_rule_base');
    class_alias('\quiz', '\mod_quiz\quiz_settings');
}

/**
 * Implementation of the quizaccess_announcements plugin.
 *
 * @package    quizaccess_announcements
 * @copyright  Jeffrey Black
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_announcements extends \mod_quiz\local\access_rule_base {

    /**
     * Return an appropriately configured instance of this rule, if it is applicable
     * to the given quiz, otherwise return null.
     * @param \mod_quiz\quiz_settings $quizobj information about the quiz in question.
     * @param int $timenow the time that should be considered as 'now'.
     * @param bool $canignoretimelimits whether the current user is exempt from
     *      time limits by the mod/quiz:ignoretimelimits capability.
     * @return quiz_access_rule_base|null the rule, if applicable, else null.
     */
    public static function make(\mod_quiz\quiz_settings $quizobj, $timenow, $canignoretimelimits) {
        global $PAGE;
        if (empty($quizobj->get_quiz()->announcements_use)) {
            return null;
        }
        return new self($quizobj, $timenow);
    }

    /**
     * This is called when the current attempt at the quiz is finished. This is
     * used to remove them from the status table.
     */
    public function current_attempt_finished() {
        global $DB;
        global $USER;
        $DB->delete_records('quizaccess_announcements_sta',
            ['quizid' => $this->quiz->id, 'userid' => $USER->id]);
    }

    /**
     * Information, such as might be shown on the quiz view page, relating to this restriction.
     * There is no obligation to return anything. If it is not appropriate to tell students
     * about this rule, then just return ''.
     * @return mixed a message, or array of messages, explaining the restriction
     *         (may be '' if no message is appropriate).
     */
    public function description() {
        $context = self::get_context_from_quizid($this->quiz->id);
        return \quizaccess_announcements\manage_monitor_buttons::buttons($this->quiz->id, $context);
    }

    /**
     * Sets up the attempt (review or summary) page with any special extra
     * properties required by this rule. securewindow rule is an example of where
     * this is used.
     *
     * @param moodle_page $page the page object to initialise.
     */
    public function setup_attempt_page($page) {
        global $DB;
        global $USER;
        // Setup announcement at top of page.
        $poll = $this->quiz->announcements_checkinterval;
        $now = $this->timenow;
        $quizid = $this->quiz->id;
        $context = self::get_context_from_quizid($quizid);
        $userid = $USER->id;
        $rule = new stdClass();
        $rule->header = $this->quiz->announcements_header;
        $rule->headerformat = $this->quiz->announcements_headerformat;
        $anonman = new quizaccess_announcements\announcement_manager($quizid, $context);
        $allannouncements = $anonman->get_rendered_announcements_html();
        $newannouncements = '';
        $last = $DB->get_record('quizaccess_announcements_sta',
            ["quizid" => $quizid, "userid" => $userid]);

        if ($page->pagetype === 'mod-quiz-review') {
            $poll = false;
        } else {
            if (!empty($last)) {
                $admin = get_config('quizaccess_announcements');
                $lasttime = (int)($last->timefetched);
                if (($now - $lasttime) < $admin->reannounce) {
                    $lasttime = (int)($last->previousfetch);
                }
                $newannouncements = $anonman->get_rendered_new_announcements_html($lasttime);
                $last->timefetched = $now;
                $last->previousfetch = $lasttime;
                $DB->update_record('quizaccess_announcements_sta', $last);
            } else {
                $last = new stdClass();
                $last->quizid = $quizid;
                $last->userid = $userid;
                $last->timefetched = $now;
                $last->previousfetch = $now;
                if (!$this->quizobj->is_preview_user()) {
                    $DB->insert_record('quizaccess_announcements_sta', $last);
                }
            }
        }
        $output = $page->get_renderer('quizaccess_announcements');
        $header = $output->attempt_page_header($rule, $context,
            $allannouncements);
        $page->requires->js_call_amd('quizaccess_announcements/announcements', 'init', [
            $this->quiz->id,
            $poll,
            $now,
            'QuizAnnouncements_container',
            $header,
            $newannouncements]);

        // Add link to announcements in fake block for staff who can make announcements.
        $content = \quizaccess_announcements\manage_monitor_buttons::buttons($quizid, $context);
        if ($content) {
            $addblock = new block_contents();
            $addblock->attributes['id'] = 'quizaccess_announcements_addblock';
            $addblock->title = get_string('add_block_header', 'quizaccess_announcements');
            $addblock->content = $content;
            $regions = $page->blocks->get_regions();
            $page->blocks->add_fake_block($addblock, reset($regions));
        }
    }

    /**
     * Add any fields that this rule requires to the quiz settings form. This
     * method is called from {@see mod_quiz_mod_form::definition()}, while the
     * security seciton is being built.
     * @param mod_quiz_mod_form $quizform the quiz settings form that is being built.
     * @param MoodleQuickForm $mform the wrapped MoodleQuickForm.
     */
    public static function add_settings_form_fields(
            mod_quiz_mod_form $quizform, MoodleQuickForm $mform) {
        global $DB;
        // Get admin settings for defaults and if locked.
        $admin = get_config('quizaccess_announcements');
        $context = $quizform->get_context();

        $heading = $mform->createElement(
            'header',
            'announcements',
            get_string('setting_heading', 'quizaccess_announcements'));
        $mform->insertElementBefore($heading, 'security');

        $useannouncements = $mform->createElement(
            'advcheckbox',
            'announcements_use',
            get_string('setting_use', 'quizaccess_announcements'));
        $mform->insertElementBefore($useannouncements, 'security');
        $mform->addHelpButton('announcements_use', 'setting_use', 'quizaccess_announcements');

        $pollinterval = $mform->createElement(
            'duration',
            'announcements_checkinterval',
            get_string('setting_checkinterval', 'quizaccess_announcements'));
        $mform->insertElementBefore($pollinterval, 'security');
        $mform->addHelpButton('announcements_checkinterval', 'setting_checkinterval', 'quizaccess_announcements');
        // Manually set default and freeze because apply admin settings doesn't work here.
        $mform->setDefault('announcements_checkinterval', $admin->checkinterval);
        if ($admin->checkinterval_locked) {
            $pollinterval->freeze();
        } else {
            $mform->disabledIf('announcements_checkinterval', 'announcements_use', 'neq', 1);
        }

        $header = $mform->createElement(
            'editor',
            'announcements_header_editor',
            get_string('setting_header', 'quizaccess_announcements'),
            null,
            ['noclean' => true, 'trusttext' => true, 'subdirs' => true,
                'maxfiles' => EDITOR_UNLIMITED_FILES, 'context' => $context]);
        $mform->insertElementBefore($header, 'security');
        $mform->setType('announcement_header_editor', PARAM_RAW);
        $mform->addHelpButton('announcements_header_editor', 'setting_header', 'quizaccess_announcements');
        $mform->disabledIf('announcements_header_editor', 'announcements_use', 'neq', 1);

        // Fix up files, because there is no data preprocessing for access rules.
        $quizid = $quizform->get_instance();
        $rule = null;
        if (!empty($quizid)) {
            $rule = $DB->get_record('quizaccess_announcements_qui', ['quizid' => $quizid]);
        }
        $headertext = $admin->defaultheader;
        $headerformat = 1;
        if (!empty($rule) && !empty($rule->header)) {
            $headertext = $rule->header;
            $headerformat = $rule->headerformat;
        }
        $data = $quizform->get_current();
        $draftid = file_get_submitted_draft_itemid('announcements_header');
        $headertext = file_prepare_draft_area(
            $draftid,
            $context->id,
            'quizaccess_announcements',
            'header',
            0,
            ['subdirs' => true],
            $headertext);
        $data->announcements_header_editor = [
            'text' => $headertext,
            'format' => $headerformat,
            'itemid' => $draftid,
        ];
    }

    /**
     * Validate the data from any form fields added using {@see add_settings_form_fields()}.
     * @param array $errors the errors found so far.
     * @param array $data the submitted form data.
     * @param array $files information about any uploaded files.
     * @param mod_quiz_mod_form $quizform the quiz form object.
     * @return array $errors the updated $errors array.
     */
    public static function validate_settings_form_fields(array $errors,
            array $data, $files, mod_quiz_mod_form $quizform) {
        if (empty($data['announcements_use'])) {
            return $errors;
            die;
        }
        // Get admin settings for defaults.
        $admin = get_config('quizaccess_announcements');
        if (!$admin->checkinterval_locked) {
            $checkinterval = $data['announcements_checkinterval'];
            if ($checkinterval < $admin->mincheckinterval) {
                $errors['announcements_checkinterval'] = get_string(
                    'error_checkinterval_tosmall',
                    'quizaccess_announcements',
                    $admin->mincheckinterval);
            }
            if (!empty($admin->maxcheckinterval) &&
                $admin->maxcheckinterval > $admin->mincheckinterval &&
                $checkinterval > $admin->maxcheckinterval) {
                $errors['announcements_checkinterval'] = get_string(
                    'error_checkinterval_tobig',
                    'quizaccess_announcements',
                    $admin->maxcheckinterval);
            }
        }

        return $errors;
    }

    /**
     * Save any submitted settings when the quiz settings form is submitted. This
     * is called from {@see quiz_after_add_or_update()} in lib.php.
     * @param object $quiz the data from the quiz form, including $quiz->id
     *      which is the id of the quiz being saved.
     */
    public static function save_settings($quiz) {
        global $DB;

        // Get admin settings for defaults.
        $admin = get_config('quizaccess_announcements');
        $oldrule = $DB->get_record('quizaccess_announcements_qui', ['quizid' => $quiz->id]);
        if (empty($oldrule) &&
            empty($quiz->announcements_use)) {
            return;
        }
        $newrule = new stdClass();
        $newrule->quizid = $quiz->id;
        $newrule->useannouncements = $quiz->announcements_use;

        if ($admin->checkinterval_locked) {
            $newrule->checkinterval = $admin->checkinterval;
        } else {
            $newrule->checkinterval = $quiz->announcements_checkinterval;
        }
        $newrule->headerformat = $quiz->announcements_header_editor['format'];
        // Fix up files.
        $context = self::get_contextid_from_quizid($quiz->id);
        $newrule->header = file_save_draft_area_files(
            $quiz->announcements_header_editor['itemid'],
            $context,
            'quizaccess_announcements',
            'header',
            0,
            ['subdirs' => true],
            $quiz->announcements_header_editor['text']);
        if (empty($newrule->header)) {
            $newrule->header = $admin->defaultheader;
        }
        // Update database.
        if (empty($oldrule)) {
            $DB->insert_record('quizaccess_announcements_qui', $newrule);
        } else {
            $newrule->id = $oldrule->id;
            $DB->update_record('quizaccess_announcements_qui', $newrule);
        }
    }

    /**
     * Delete any rule-specific settings when the quiz is deleted. This is called
     * from {@see quiz_delete_instance()} in lib.php.
     * @param object $quiz the data from the database, including $quiz->id
     *      which is the id of the quiz being deleted.
     * @since Moodle 2.7.1, 2.6.4, 2.5.7
     */
    public static function delete_settings($quiz) {
        global $DB;
        // Find if a rule currently exists.
        $rule = $DB->get_record('quizaccess_announcements_qui', ['quizid' => $quiz->id]);
        if (empty($rule)) {
            return;
        }
        // Delete all announcement related DB for the quiz.
        $transaction = $DB->start_delegated_transaction();
        $contextid = self::get_contextid_from_quizid($quiz->id);
        quizaccess_announcements\deleter::delete_rule($contextid, $quiz->id);
        $transaction->allow_commit();
    }

    /**
     * Return the bits of SQL needed to load all the settings from all the access
     * plugins in one DB query. The easiest way to understand what you need to do
     * here is probalby to read the code of {@see quiz_access_manager::load_settings()}.
     *
     * If you have some settings that cannot be loaded in this way, then you can
     * use the {@see get_extra_settings()} method instead, but that has
     * performance implications.
     *
     * @param int $quizid the id of the quiz we are loading settings for. This
     *     can also be accessed as quiz.id in the SQL. (quiz is a table alisas for {quiz}.)
     * @return array with three elements:
     *     1. fields: any fields to add to the select list. These should be alised
     *        if neccessary so that the field name starts the name of the plugin.
     *     2. joins: any joins (should probably be LEFT JOINS) with other tables that
     *        are needed.
     *     3. params: array of placeholder values that are needed by the SQL. You must
     *        used named placeholders, and the placeholder names should start with the
     *        plugin name, to avoid collisions.
     */
    public static function get_settings_sql($quizid) {
        $admin = get_config('quizaccess_announcements');
        return [
            'announcements.useannouncements AS announcements_use, '.
            'COALESCE(announcements.checkinterval, ' . $admin->checkinterval . ') AS announcements_checkinterval, '.
            'announcements.header AS announcements_header, '.
            'announcements.headerformat AS announcements_headerformat ',
            'LEFT JOIN {quizaccess_announcements_qui} announcements ON announcements.quizid = quiz.id',
            []];
    }

    /**
     * Obtains the context module for this quiz.
     *
     * @param int $quizid the id of the quiz.
     * @return module the context module for the quiz.
     */
    private static function get_context_from_quizid($quizid) {
        return context_module::instance(get_coursemodule_from_instance('quiz', $quizid, false, false, MUST_EXIST)->id);
    }

    /**
     * Obtains the id for the context module for this quiz.
     *
     * @param int $quizid the id of the quiz.
     * @return int the contextid for the context module for the quiz.
     */
    private static function get_contextid_from_quizid($quizid) {
        return self::get_context_from_quizid($quizid)->id;
    }
}
