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
 * Defines the add announcement form class for the quizaccess_announcements plugin.
 *
 * @package    quizaccess_announcements
 * @copyright  Jeffrey Black
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace quizaccess_announcements;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class add_announcement_form extends \moodleform {
    /** The id of the quiz */
    private $quizid;
    /** The context module for this quiz */
    private $context;

    /**
     * Custom constructor for the add announcement form.
     *
     * @param int $quizid the id of the quiz
     * @param object $context the context module for the quiz.
     */
    public function __construct($quizid, $context) {
        $this->quizid = $quizid;
        $this->context = $context;
        parent::__construct('announcements.php');
    }

    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'quizid');
        $mform->setType('quizid', PARAM_INT);
        $mform->setDefault('quizid', $this->quizid);

        $mform->addElement(
            'header',
            'announcements',
            get_string('add_announcement_header', 'quizaccess_announcements'));

        $mform->addElement(
            'editor',
            'content',
            get_string('add_announcement_content', 'quizaccess_announcements'),
            null,
            [
                'noclean' => true,
                'trusttext' => true,
                'subdirs' => true,
                'maxfiles' => EDITOR_UNLIMITED_FILES,
                'context' => $this->context]);
        $mform->setType('content', PARAM_RAW);
        $mform->addHelpButton('content', 'add_announcement_content', 'quizaccess_announcements');

        $mform->addElement('submit', 'submitbutton', get_string(
            'add_announcement_button', 'quizaccess_announcements'));
    }

    public function validation($data, $files) {
        $errors = [];
        if (empty($data['content']['text'])) {
            $errors['content'] = get_string(
                "add_announcement_blank", 'quizaccess_announcements');
        }
        return $errors;
    }
}
