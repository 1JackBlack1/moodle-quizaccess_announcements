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
 * Strings for the quizaccess_announcements plugin.
 *
 * @package    quizaccess_announcements
 * @copyright  Jeffrey Black
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$string['add_announcement_blank'] = 'Announcement text cannot be blank.';
$string['add_announcement_button'] = 'Add new announcement';
$string['add_announcement_content'] = 'Announcement content';
$string['add_announcement_content_help'] = 'The content of the announcement which will be displayed to students. HTML supported.';
$string['add_announcement_header'] = 'Add announcement';
$string['add_block_header'] = 'Announcements';
$string['admin_checkinterval_desc'] = 'The default polling interval between fetching announcements. A higher duration will improve server performance but increase the time required for students to recieve announcements.';
$string['admin_defaultheader'] = 'Default announcement header';
$string['admin_defaultheader_desc'] = 'The header that will appear above announcements if one is not specified in a quiz.';
$string['admin_defaultheader_val'] = '<h3>Announcements</h3>';
$string['admin_maxcheckinterval'] = 'Maximum polling interval';
$string['admin_maxcheckinterval_desc'] = 'The minimum duration staff can set the announcement polling interval to for an individual quiz. Set as 0 to remove limits.';
$string['admin_mincheckinterval'] = 'Minimum polling interval';
$string['admin_mincheckinterval_desc'] = 'The minimum duration staff can set the announcment polling interval to for an individual quiz. Set at 0 to remove limits';
$string['admin_reannounce'] = 'Reannounce buffer time';
$string['admin_reannounce_desc'] = 'Guards against a race condition which could cause students to miss an announcement. If the AJAX fetches announcements and the student navigates to the next page before seeing, this buffer time results in them still seeing the announcement.';
$string['admin_refreshinterval'] = 'Status refresh time';
$string['admin_refreshinterval_desc'] = 'The monitor status page will automatically refresh the status of students after this duration.';
$string['announcement_not_found'] = 'Unable to find announcement.';
$string['announcements:delete_announcement'] = 'Delete announcements.';
$string['announcements:make_announcement'] = 'Post announcements.';
$string['announcements:view_status'] = 'View last time students fetched announcements.';
$string['cantadd'] = 'You do not have permission to add announcements.';
$string['cantaddordel'] = 'You do not have permission to manage announcements.';
$string['cantdel'] = 'You do not have permission to delete announcements.';
$string['cantmonitor'] = 'You do not have permission to monitor student status.';
$string['delete'] = 'Delete';
$string['delete_all'] = 'Delete all announcements';
$string['delete_announcement_button'] = 'Yes I am sure I wish to delete the announcement(s)';
$string['delete_announcement_cancel_button'] = 'Cancel';
$string['delete_announcement_conf'] = 'Delete Announcement';
$string['delete_announcement_confirmation'] = 'Are you sure you wish to delte the following announcment.';
$string['delete_announcement_header'] = '<h3>Are you sure you wish to delete the following announcement(s)?</h3>';
$string['error_checkinterval_tobig'] = 'Intervall too large. Must be no more than {$a} seconds';
$string['error_checkinterval_tosmall'] = 'Intervall too small. Must be at least {$a} seconds';
$string['event:announcement_created'] = 'Announcement created';
$string['event:announcement_deleted'] = 'Announcement deleted';
$string['event:announcements_deleted'] = 'All announcements deleted';
$string['event:announcements_viewed'] = 'Announcements queried';
$string['event:student_status_viewed'] = 'Student status viewed';
$string['invalidcmid'] = 'Unable to find quiz matching provided cmid.';
$string['invalidquizid'] = 'Unable to find quiz matching provided quizid.';
$string['manage_announcements'] = 'Manage Announcements: {$a}';
$string['manage_button_text'] = 'Add or manage announcements';
$string['monitor_ajax_error'] = 'Error obtaining student status.';
$string['monitor_button_text'] = 'Monitor student status';
$string['monitor_header'] = 'Current student status';
$string['monitor_pre_text'] = 'This quiz is currently set to refresh every {$a} seconds';
$string['monitor_title'] = 'Monitor {$a}';
$string['no_announcements'] = 'No announcements have been made.';
$string['no_attempts_in_progress'] = 'There are currently no attempts in progress.';
$string['noquizspecified'] = 'You must specify a quiz by its quizid or cmid.';
$string['pluginname'] = 'Live Quiz announcements';
$string['popupheader'] = 'A new announcement has been made';
$string['privacy:metadata'] = 'This plugin only stores data on students while a quiz attempt is active. Once the attempt is submitted that data is removed.';
$string['quizcmidmismatch'] = 'quizid provided does not correspond to provided cmid.';
$string['quizmismatch'] = 'Quiz specified does not match announcement.';
$string['setting_checkinterval'] = 'Announcement polling interval';
$string['setting_checkinterval_help'] = 'After successfully fetching announcements, there will be a pause of this duration before it attempts to fetch announcements again. A higher duration will improve server performance but increase the time required for students to recieve announcements.';
$string['setting_header'] = 'Announcement header';
$string['setting_header_help'] = 'HTML that will appear at the top of a quiz attempt page before the announcements';
$string['setting_heading'] = 'Live Announcements';
$string['setting_use'] = 'Enable live announcements';
$string['setting_use_help'] = 'Will allow staff to send live announcements to students who are taking the test.';
$string['status_last_announcement'] = 'The last announcement was posted at {$a->time}, which was {$a->ago} seconds ago.';
$string['status_lastchecked'] = 'Time last accessed';
$string['status_lastcheckedago'] = 'Time since last accessed';
$string['status_no_announcements'] = 'No announcements have been posted.';
