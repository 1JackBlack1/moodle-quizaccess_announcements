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
 * Restore the quizaccess_announcements plugin.
 *
 * @package    quizaccess
 * @subpackage announcements
 * @copyright  Jeffrey Black
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/backup/moodle2/restore_mod_quiz_access_subplugin.class.php');

class restore_quizaccess_announcements_subplugin extends restore_mod_quiz_access_subplugin {

    /**
     * Provides path structure required to restore data for quizaccess_announcements plugin.
     *
     * @return array
     */
    protected function define_quiz_subplugin_structure() {
        $paths = [];
        $elepath = $this->get_pathfor('/quizsettings');
        $paths[] = new restore_path_element('quizaccess_announcements_quizsettings', $elepath);
        $elepath = $this->get_pathfor('announcements');
        $paths[] = new restore_path_element('quizaccess_announcements_announcements', $elepath);
        return $paths;
    }

    /**
     * Processes the quizsettings element, if it is in the file.
     * @param array $data the data read from the XML file.
     */
    public function process_quizaccess_announcements_quizsettings($data) {
        global $DB;
        $data = (object)$data;
        $data->quizid = $this->get_new_parentid('quiz');
        $oldid = $data->id;
        $newid = $DB->insert_record('quizaccess_announcements_qui', $data);
        $this->set_mapping('quizaccess_announcements_quizsettings', $oldid, $newid, true);
        $this->add_related_files('quizaccess_announcements', 'header', null);
    }

    /**
     * Processes the announcements elements, if they are in the file.
     * @param array $data the data read from the XML file.
     */
    public function process_quizaccess_announcements_announcements($data) {
        global $DB;
        $data = (object)$data;
        $data->quizid = $this->get_new_parentid('quiz');
        $oldid = $data->id;
        $newid = $DB->insert_record('quizaccess_announcements_ann', $data);
        $this->set_mapping('quizaccess_announcements_announcement', $oldid, $newid, true);
        $this->add_related_files('quizaccess_announcements', 'announcement', 'quizaccess_announcements_announcement');
    }
    // TODO: Await updates to Moodle to allow rewriting URLS during restore.
}

