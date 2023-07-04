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

namespace quizaccess_announcements;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * Defines the add announcement form class for the quizaccess_announcements plugin.
 *
 * Used to confirm before the announcement is actually deleted.
 *
 * @package    quizaccess_announcements
 * @copyright  Jeffrey Black
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class delete_announcement_form extends \moodleform {
    /** @var int The id of the quiz */
    private $quizid;
    /** @var int The id of the announcement to delete */
    private $delete;
    /** @var bool A boolean flag to indicate all announcements should be deleted */
    private $deleteall;
    /** @var string rendered HTML of announcemnents that are up for deleting */
    private $announcements;

    /**
     * Custom constructor for the delete announcement form to confirm deleting.
     *
     * @param int $quizid the id of the quiz
     * @param int $delete the id of the announcement to delete.
     * @param bool $deleteall if all announcements for quiz are being deleted.
     * @param string $announcements the html of the announcements to delete.
     */
    public function __construct($quizid, $delete, $deleteall, $announcements) {
        $this->quizid = $quizid;
        $this->delete = $delete;
        $this->deleteall = $deleteall;
        $this->announcements = $announcements;

        parent::__construct('delete.php');
    }

    /** Function providing the form definition */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'quizid');
        $mform->setType('quizid', PARAM_INT);
        $mform->setDefault('quizid', $this->quizid);

        if (!empty($this->deleteall)) {
            $mform->addElement('hidden', 'deleteall');
            $mform->setType('deleteall', PARAM_BOOL);
            $mform->setDefault('deleteall', true);
        } else {
            $mform->addElement('hidden', 'delete');
            $mform->setType('delete', PARAM_INT);
            $mform->setDefault('delete', $this->delete);
        }

        $mform->addElement('html', get_string('delete_announcement_header', 'quizaccess_announcements'));
        $mform->addElement('html', $this->announcements);

        $buttons = [];
        $buttons[] = $mform->createElement('submit', 'submitbutton', get_string(
            'delete_announcement_button', 'quizaccess_announcements'));
        $buttons[] = $mform->createElement('cancel', 'cancelbutton', get_string(
            'delete_announcement_cancel_button', 'quizaccess_announcements'));

        $mform->addGroup($buttons, 'buttonar', '', array(' '), false);
        $mform->setType('buttonar', PARAM_RAW);
    }
}
