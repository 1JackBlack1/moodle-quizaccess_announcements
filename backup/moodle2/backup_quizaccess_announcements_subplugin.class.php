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
 * Backup the quizaccess_announcements plugin.
 *
 * @package    quizaccess_announcements
 * @copyright  Jeffrey Black
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/backup/moodle2/backup_mod_quiz_access_subplugin.class.php');

/**
 * Class to backup the quizaccess_announcements plugin.
 */
class backup_quizaccess_announcements_subplugin extends backup_mod_quiz_access_subplugin {

    /**
     * Stores the data related to the quizaccess_announcements plugin for a particular quiz.
     *
     * @return backup_subplugin_element
     */
    protected function define_quiz_subplugin_structure() {
        parent::define_quiz_subplugin_structure();
        $quizid = backup::VAR_ACTIVITYID;

        // Create XML elements.
        $subplugin = $this->get_subplugin_element();
        $subpluginwrapper = new backup_nested_element($this->get_recommended_name());
        $subplugintablesettings = new backup_nested_element('quizsettings',
            ['id'], ['useannouncements', 'checkinterval', 'header', 'headerformat']);
        $subplugintableannouncements = new backup_nested_element('announcements',
            ['id'], ['content', 'contentformat', 'timeposted']);

        // Connect XML elements into the tree.
        $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subplugintablesettings);
        $subpluginwrapper->add_child($subplugintableannouncements);

        // Set source to populate the data.
        $subplugintablesettings->set_source_table('quizaccess_announcements_qui', ['quizid' => $quizid]);
        $subplugintableannouncements->set_source_table('quizaccess_announcements_ann', ['quizid' => $quizid]);

        // Add files.
        $subplugintablesettings->annotate_files('quizaccess_announcements', 'header', null);
        $subplugintableannouncements->annotate_files('quizaccess_announcements', 'announcement', 'id');

        return $subplugin;
    }
}
