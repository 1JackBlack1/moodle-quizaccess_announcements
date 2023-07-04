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
 * Steps definitions related to quizaccess_announcements.
 *
 * @package   quizaccess_announcements
 * @category  test
 * @copyright Jeffrey Black
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode as TableNode;
use Behat\Mink\Exception\ExpectationException as ExpectationException;

/**
 * Steps definitions related to quizaccess_announcements.
 *
 * @copyright 2014 Marina Glancy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_quizaccess_announcements extends behat_base {

    /**
     * Convert page names to URLs for steps like 'When I am on the "[identifier]" "[page type]" page'.
     *
     * Recognised page names are:
     * | pagetype          | name meaning                                | description                                           |
     * | manage            | Quiz name                                   | The page to manage announcements (announcements.php)  |
     * | monitor           | Quiz name                                   | The monitor student page (monitor.php.php)            |
     *
     * @param string $type identifies which type of page this is, e.g. 'manage'.
     * @param string $quizname identifies the quiz, e.g. 'Quiz 1'.
     * @return moodle_url the corresponding URL.
     * @throws Exception with a meaningful error message if the specified page cannot be found.
     */
    protected function resolve_page_instance_url(string $type, string $quizname): moodle_url {
        global $DB;
        $quizid = $this->get_quizid_by_name($quizname);

        switch (strtolower($type)) {
            case 'manage':
                return new moodle_url('/mod/quiz/accessrule/announcements/announcements.php',
                        ['quizid' => $quizid]);
            case 'monitor':
                return new moodle_url('/mod/quiz/accessrule/announcements/monitor.php',
                        ['quizid' => $quizid]);
            default:
                throw new Exception('Unrecognised quiz page type "' . $type . '."');
        }
    }

    /**
     * Setup live announcements for a quiz.
     *
     * @param string $quizname the name of the quiz to add sections to.
     * @param int $pollinginterval the time in seconds between successive attempts to fetch announcements. Minimum of 10.
     * @param string $headertext the HTML to display above the announcements.
     *
     * @Given /^quiz "([^"]*)" has announcements configured to poll every "(\d+)" seconds with a header of "((?:[^"]|\\")+)"$/
     */
    public function configure_announcements($quizname, $pollinginterval, $headertext) {
        global $DB;

        // Get the quiz.
        $quizid = $this->get_quizid_by_name($quizname);
        // Sanitise data.
        $pollinginterval = intval($pollinginterval);
        if ($pollinginterval < 10) {
            $pollinginterval = 10;
        }
        if (empty($headertext)) {
            $headertext = "<h3>Announcements</h3>";
        }

        // Add the rule.
        $rule = new stdClass();
        $rule->quizid = $quizid;
        $rule->useannouncements = 1;
        $rule->checkinterval = $pollinginterval;
        $rule->header = $headertext;
        $rule->headerformat = 0;
        $DB->insert_record('quizaccess_announcements_qui', $rule);
    }

    /**
     * Create announcements for the quiz.
     *
     * The first row should be column names:
     * | time | text |
     *
     * time        When to have the announcement posted. Can be now, # s ago, or a unix timestamp.
     * text        text of the announcement.
     *
     * Then there should be a number of rows of data, one for each anouncement you want to add.
     *
     * @param string $quizname the name of the quiz to add sections to.
     * @param TableNode $data information about the announcement to add.
     *
     * @Given /^quiz "([^"]*)" has the following announcements posted:$/
     */
    public function create_announcements($quizname, $data) {
        $quizid = $this->get_quizid_by_name($quizname);
        foreach ($data->getHash() as $anondata) {
            if (!array_key_exists('time', $anondata)) {
                throw new ExpectationException('When adding announcements to a quiz, ' .
                        'the time column is required.', $this->getSession());
            }
            if (!array_key_exists('text', $anondata)) {
                throw new ExpectationException('When adding announcements to a quiz, ' .
                        'the text column is required.', $this->getSession());
            }
            $time = $this->parse_time($anondata['time']);
            $text = $anondata['text'];
            $this->create_announcement($quizid, $text, $time);
        }
    }

    /**
     * Create an announcement for the quiz.
     *
     * @param string $quizname the name of the quiz to add sections to.
     * @param string $text the HTML to display for the announcement.
     *
     * @Given /^quiz "([^"]*)" has announcement "((?:[^"]|\\")+)" posted$/
     */
    public function create_announcement_now($quizname, $text) {
        $quizid = $this->get_quizid_by_name($quizname);
        $time = time();
        $this->create_announcement($quizid, $text, $time);
    }

    /**
     * Create an announcement for the quiz at a given time ago.
     *
     * @param string $quizname the name of the quiz to add sections to.
     * @param string $text the HTML to display for the announcement.
     * @param int $ago the time in the past the announcement was posted.
     *
     * @Given /^quiz "([^"]*)" has announcement "((?:[^"]|\\")+)" posted "(\d+)" s ago$/
     */
    public function create_announcement_in_past($quizname, $text, $ago) {
        $quizid = $this->get_quizid_by_name($quizname);
        $ago = intval($ago);
        $time = time() - $ago;
        $this->create_announcement($quizid, $text, $time);
    }

    /**
     * Update student status.
     *
     * The first row should be column names:
     * | username | time | prev |
     *
     * username    the username of the student.
     * time        Time student last fetched announcements. Can be now, # s ago, or a unix timestamp or "none".
     * prev        Optional column for the previous time the student fetched announcements.
     *
     * Then there should be a number of rows of data, one for each student you want to update.
     *
     * @param string $quizname the name of the quiz you want to update.
     * @param TableNode $data information about the students you want to update.
     *
     * @Given /^quiz "([^"]*)" has the following student status for quizaccess_announcements:$/
     */
    public function update_status($quizname, $data) {
        $quizid = $this->get_quizid_by_name($quizname);
        foreach ($data->getHash() as $studata) {
            if (!array_key_exists('username', $studata)) {
                throw new ExpectationException('When updating student status for a quiz, ' .
                        'the username column is required.', $this->getSession());
            }
            if (!array_key_exists('time', $studata)) {
                throw new ExpectationException('When updating student status for a quiz, ' .
                        'the time column is required.', $this->getSession());
            }
            $username = $studata['username'];
            $time = $studata['time'];
            if ($time === "none" ) {
                $this->remove_student_status($quizid, $username);
            } else {
                $time = $this->parse_time($time);
                if (array_key_exists('prev', $studata)) {
                    $prev = $this->parse_time($studata['prev']);
                } else {
                    $prev = $time;
                }
                $this->update_student_status($quizid, $username, $time, $prev);
            }
        }
    }

    /**
     * Check student status.
     *
     * The first row should be column names:
     * | username | status |
     *
     * username    the username of the student.
     * status      success|warning|danger|none indicating the status of the student.
     *
     * Then there should be a number of rows of data, one for each student you want to check.
     *
     * @param TableNode $data information about the status of the student.
     *
     * @Then /^the quizaccess_announcements status table should have the following statuses:$/
     */
    public function check_status($data) {
        $possiblestatuses = ['success', 'warning', 'danger'];
        foreach ($data->getHash() as $studata) {
            if (!array_key_exists('username', $studata)) {
                throw new ExpectationException('When checking student status for a quiz, ' .
                        'the username column is required.', $this->getSession());
            }
            if (!array_key_exists('status', $studata)) {
                throw new ExpectationException('When checking student status for a quiz, ' .
                        'the status column is required.', $this->getSession());
            }
            $username = $studata['username'];
            $status = $studata['status'];
            $rowclasses = [];
            foreach ($possiblestatuses as $possiblestatus) {
                $query = "contains(@class, 'table-" . $possiblestatus . "')";
                if ($possiblestatus !== $status) {
                    $query = "not(" . $query . ")";
                }
                $rowclasses[] = $query;
            }
            $rowclassstring = implode(' and ', $rowclasses);
            $exception = new ExpectationException('Student "' . $username . '" ' .
                        'does not have the status "' . $status .
                        '" for quizaccess_announcements.', $this->getSession());
            $xpath = "//table[@id = 'quizaccess_announcements_status']" .
                "//tr[" . $rowclassstring . "]" .
                "//td[contains(@class, 'username') and " .
                "normalize-space(text())='" . $username . "']";
            $this->find('xpath', $xpath, $exception);
            if ($status === 'none') {
                $exception = new ExpectationException('Student "' . $username . '" ' .
                            'displays a time when they should have none' .
                            ' for quizaccess_announcements.', $this->getSession());
                $xpath = "//table[@id = 'quizaccess_announcements_status']" .
                    "//tr[td[contains(@class, 'username') and " .
                    "normalize-space(text())='" . $username . "'] and " .
                    "td[contains(@class, 'time') and normalize-space(text())='-'] and " .
                    "td[contains(@class, 'ago') and normalize-space(text())='-']]";
                $this->find('xpath', $xpath, $exception);
            }
        }
    }

    /**
     * Identify Time something was meant to happen.
     *
     * @param string $timestring the string describing the time.
     * @return int $time the timestamp to return.
     */
    protected function parse_time($timestring): int {
        $now = time();
        if ($timestring == 'now' || empty($timestring)) {
            return $now;
        }
        $sp = strpos($timestring, ' s ago');
        if ($sp) {
            $ago = intval(substr($timestring, 0, $sp));
            return $now - $ago;
        }
        return intval($timestring);
    }

    /**
     * Create an announcement in the database.
     *
     * @param int $quizid the id of the quiz to add an announecment to.
     * @param string $text the HTML to display for the announcement.
     * @param int $time the timestamp the announcement was posted.
     */
    protected function create_announcement($quizid, $text, $time) {
        global $DB;

        // Sanitise data.
        if (empty($text)) {
            $text = "Attention: An announcment has been made";
        }
        $time = intval($time);
        if (empty($time)) {
            $time = time();
        }

        // Add the announcement.
        $anon = new stdClass();
        $anon->quizid = $quizid;
        $anon->content = $text;
        $anon->contentformat = 0;
        $anon->timeposted = $time;
        $DB->insert_record('quizaccess_announcements_ann', $anon);
    }

    /**
     * Update student status in the database.
     *
     * @param int $quizid the id of the quiz to add an announecment to.
     * @param text $username the username of the student to update.
     * @param int $time the timestamp the student last fetched announcements.
     * @param int $prev the timestamp the student previously fetched announcements.
     */
    protected function update_student_status($quizid, $username, $time, $prev) {
        global $DB;

        // Get user.
        $user = $DB->get_record('user', ['username' => $username], '*', MUST_EXIST);
        $userid = $user->id;
        // Sanitise data.
        $time = intval($time);
        if (empty($time)) {
            $time = time();
        }
        $prev = intval($prev);
        if (empty($prev)) {
            $prev = $time;
        }

        // Check if old student status record exists.
        $oldrec = $DB->get_record('quizaccess_announcements_sta', ["quizid" => $quizid, "userid" => $userid]);
        if (!empty($oldrec)) {
            $oldrec->timefetched = $time;
            $oldrec->previousfetch = $prev;
            $DB->update_record('quizaccess_announcements_sta', $oldrec);
        } else {
            $status = new stdClass();
            $status->quizid = $quizid;
            $status->userid = $userid;
            $status->timefetched = $time;
            $status->previousfetch = $prev;
            $DB->insert_record('quizaccess_announcements_sta', $status);
        }
    }

    /**
     * Remove student status from database (for finished attempt).
     *
     * @param int $quizid the id of the quiz to add an announecment to.
     * @param text $username the username of the student to remove.
     */
    protected function remove_student_status($quizid, $username) {
        global $DB;

        // Get user.
        $user = $DB->get_record('user', ['username' => $username], '*', MUST_EXIST);
        $userid = $user->id;

        // Delete matching records.
        $DB->delete_records('quizaccess_announcements_sta', ["quizid" => $quizid, "userid" => $userid]);
    }

    /**
     * Get a quiz by name.
     *
     * @param string $name quiz name.
     * @return int the id for the quiz.
     */
    protected function get_quizid_by_name(string $name): int {
        global $DB;
        $quiz = $DB->get_record('quiz', array('name' => $name), '*', MUST_EXIST);
        return $quiz->id;
    }
}
