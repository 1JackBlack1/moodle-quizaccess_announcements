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
 * Defines status table for the quizaccess_announcements plugin.
 *
 * @package    quizaccess
 * @subpackage announcements
 * @copyright  Jeffrey Black
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace quizaccess_announcements;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/tablelib.php');
use table_sql;

class status_table extends table_sql {
    /** The current timestamp */
    private $now;
    /** The time between successive checks, used to determine status */
    private $checkinterval;
    /** The last announcement object, used to determine status */
    private $lastannouncement;
    /**
     * Construct function for this table.
     *
     * @param string $uniqueid the id for the table.
     * @param object $context the context module for the quiz.
     * @param int $quizid the id for the quiz.
     * @param moodle_url $url the url that this is viewed on.
     * @param object $lastannouncement the last announcement that was posted.
     * @param int $checkinterval how often students check for updates.
     * @param int $now the timestamp of now.
     */
    public function __construct($uniqueid, $context, $quizid, $url, $lastannouncement, $checkinterval, $now) {
        parent::__construct($uniqueid);
        $this->now = $now;
        $this->set_attribute('id', $uniqueid);
        $this->define_baseurl($url);
        $this->is_sortable = false;
        $this->checkinterval = (int)$checkinterval;
        $this->lastannouncement = $lastannouncement->time;
        $headers = [];
        $columns = [];
        $headers[] = get_string('fullname');
        $columns[] = 'fullname';
        $headers[] = get_string('username');
        $columns[] = 'username';
        $headers[] = get_string('status_lastchecked', 'quizaccess_announcements');
        $columns[] = 'time';
        $headers[] = get_string('status_lastcheckedago', 'quizaccess_announcements');
        $columns[] = 'ago';
        $canattempt = get_enrolled_with_capabilities_join($context, '', 'mod/quiz:attempt');
        $userfieldsapi = \core_user\fields::for_identity($context)->with_name()
            ->excluding('id', 'idnumber', 'picture', 'imagealt', 'institution', 'department', 'email');
        $userfields = $userfieldsapi->get_sql('u', true, '', '', false);
        $fields = 'u.id as id, ';
        $fields .= 'u.username as username, ';
        $fields .= $userfields->selects;
        $fields .= ', COALESCE(sta.timefetched, null) as timefetched ';
        $from = ' {user} u';
        $from .= "\n" . $userfields->joins;
        $from .= "\n" . $canattempt->joins;
        $from .= "\n" . 'LEFT JOIN {quizaccess_announcements_sta} sta ON sta.userid = u.id AND sta.quizid = :quizid ';
        $params = array_merge($userfields->params, $canattempt->params, ['quizid' => $quizid]);
        $joins = $canattempt->joins;
        $wheres = $canattempt->wheres;
        $this->set_sql($fields, $from, $wheres, $params);
        $this->define_columns($columns);
        $this->define_headers($headers);
        $this->column_class('username', 'username');
        $this->column_class('time', 'time');
        $this->column_class('ago', 'ago');
    }

    /**
     * Overrides pagesize so all students appear on a single page.
     *
     * @param int $pagesize the pagesize sent to the function.
     * @param int $total the total number of rows for the table.
     */
    public function pagesize($pagesize, $total) {
        $this->pagesize = null;
        $this->use_pages = false;
    }

    /**
     * function to provide some additional calculated columns for the row
     *
     * @param object $row the row to update.
     */
    public function format_row($row) {
        if (empty($row->timefetched)) {
            $row->time = '-';
            $row->ago = '-';
        } else {
            $lastfetched = $row->timefetched;
            $row->time = \quizaccess_announcements\announcement_manager::get_display_time($lastfetched);
            $row->ago = $this->now - $lastfetched;
        }
        return parent::format_row($row);
    }

    /**
     * Overrides the default sort order for the table to force only a specific order.
     *
     */
    public function get_sql_sort() {
        return 'timefetched ASC';
    }

    /**
     * Adds additional classes for the row to easily see student status.
     *
     * @param object $row the row object to format.
     * @return string classes for the row.
     */
    public function get_row_class($row) {
        $classes = 'u_' . $row->id;
        if (empty($row->timefetched)) {
            return $classes;
        } else if ($row->ago > $this->checkinterval * 2) {
            return $classes . ' table-danger';
        } else if ($row->ago > $this->checkinterval) {
            return $classes . ' table-warning';
        } else if (!empty($this->lastannouncement)
            && (int)$this->lastannouncement > $row->timefetched) {
            return $classes . ' table-warning';
        }
        return $classes . ' table-success';
    }
}
