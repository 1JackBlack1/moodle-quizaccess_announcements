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
 * Administration settings definitions for the quizaccess_announcements plugin.
 *
 * @package    quizaccess
 * @subpackage announcements
 * @copyright  Jeffrey Black
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

global $ADMIN;

if ($hassiteconfig) {
    $setting = (new admin_setting_configduration(
        'quizaccess_announcements/checkinterval',
        get_string('setting_checkinterval', 'quizaccess_announcements'),
        get_string('admin_checkinterval_desc', 'quizaccess_announcements'),
        30,
        1));
    $setting->set_locked_flag_options(admin_setting_flag::ENABLED, false);
    $settings->add($setting);

    $settings->add(new admin_setting_configduration(
        'quizaccess_announcements/mincheckinterval',
        get_string('admin_mincheckinterval', 'quizaccess_announcements'),
        get_string('admin_mincheckinterval_desc', 'quizaccess_announcements'),
        30,
        1));

    $settings->add(new admin_setting_configduration(
        'quizaccess_announcements/maxcheckinterval',
        get_string('admin_maxcheckinterval', 'quizaccess_announcements'),
        get_string('admin_maxcheckinterval_desc', 'quizaccess_announcements'),
        300,
        60));

    $settings->add(new admin_setting_configduration(
        'quizaccess_announcements/reannounce',
        get_string('admin_reannounce', 'quizaccess_announcements'),
        get_string('admin_reannounce_desc', 'quizaccess_announcements'),
        5,
        1));

    $settings->add(new admin_setting_configduration(
        'quizaccess_announcements/refreshinterval',
        get_string('admin_refreshinterval', 'quizaccess_announcements'),
        get_string('admin_refreshinterval_desc', 'quizaccess_announcements'),
        15,
        1));

    $settings->add(new admin_setting_confightmleditor(
        'quizaccess_announcements/defaultheader',
        get_string('admin_defaultheader', 'quizaccess_announcements'),
        get_string('admin_defaultheader_desc', 'quizaccess_announcements'),
        get_string('admin_defaultheader_val', 'quizaccess_announcements')));
}
