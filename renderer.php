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
 * Defines the renderer for the quizaccess_announcements plugin.
 *
 * @package    quizaccess_announcements
 * @copyright  Jeffrey Black
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quizaccess_announcements_renderer extends plugin_renderer_base {
    /**
     * @var boolean candel indicates if you can delete.
     */
    public $candel;
    /**
     * @var object rule the rule object for the announcements.
     */
    public $rule;
    /**
     * @var context context for the quiz.
     */
    public $context;

    /**
     * Renders the page showing the list of current announcements
     * and the form to add a new announcement.
     *
     * @param object $rule the rule object for the announcements.
     * @param object $context the context module for the quiz.
     * @param object[] $announcements array of formatted announcement objects.
     * @param form $form the form to add an announcement.
     * @return string html to output.
     */
    public function manage_announcements_page($rule, $context, $announcements, $form) {
        $this->rule = $rule;
        $this->context = $context;
        $output = '';
        $output .= $this->header();
        $output .= \quizaccess_announcements\manage_monitor_buttons::monitor_button($rule->quizid, $context);
        $output .= $this->manage_list($announcements);
        if (!empty($form)) {
            $output .= $form->render();
        }
        $output .= $this->footer();
        return $output;
    }

    /**
     * Renders the page showing the delete confirmation.
     *
     * @param form $form the form to confirm deleting announcement(s).
     * @return string html to output.
     */
    public function delete_announcement_page($form) {
        $output = '';
        $output .= $this->header();
        $output .= $form->render();
        $output .= $this->footer();
        return $output;
    }

    /**
     * Renders the monitoring page showing the status of students taking an attempt.
     *
     * @param object $rule the rule object for the announcements.
     * @param object $context the context module for the quiz.
     * @param object $lastposted the last posted announcement.
     * @param object $table the status table.
     * Needs to output to avoid output buffering of table.
     */
    public function monitor_page($rule, $context, $lastposted, $table) {
        $admin = get_config('quizaccess_announcements');
        $data = new stdClass();
        $data->quizid = $rule->quizid;
        $data->checkinterval = $rule->checkinterval;
        if (empty($admin->refreshinterval)) {
            $data->refreshinterval = $rule->checkinterval;
        } else {
            $data->refreshinterval = min(
                $admin->refreshinterval, $rule->checkinterval);
        }
        $data->lastposted = $lastposted->str;
        $data->tableid = $table->uniqueid;
        $output = '';
        $output .= $this->header();
        $output .= \quizaccess_announcements\manage_monitor_buttons::manage_button($rule->quizid, $context);
        $output .= $this->output->render_from_template('quizaccess_announcements/monitor', $data);
        echo $output;
        $table->out(null, false);
        echo $this->footer();
    }

    /**
     * Renders the top of the quiz announcement page.
     *
     * @param object $rule the rule object for the announcements.
     * @param object $context the context module for the quiz.
     * @param string $announcements array of formatted announcement objects.
     * @return string html to appear at the top of the quiz attempt page.
     */
    public function attempt_page_header($rule, $context, $announcements) {
        $this->rule = $rule;
        $this->context = $context;
        $data = new stdClass();
        $data->header = $this->format_header();
        $data->announcements = $announcements;
        return $this->output->render_from_template('quizaccess_announcements/attempt', $data);
    }


    /**
     * Renders the list of current announcements, with delete links.
     *
     * @param objects[] $announcements array of formatted announcement objects.
     * @return string html containing current announcements.
     */
    private function manage_list($announcements) {
        $data = new stdClass();
        $data->header = $this->format_header();
        $data->announcements = $announcements;
        if ($announcements) {
            $data->hasannouncements = true;
        }
        $data->candel = $this->candel;
        if ($this->candel) {
            // Aliases for Moodle < 4.2.
            $type = true;
            if (defined('single_button::BUTTON_PRIMARY')) {
                $type = single_button::BUTTON_PRIMARY;
            }
            $url = '/mod/quiz/accessrule/announcements/delete.php';
            $params = ['quizid' => $this->rule->quizid, 'deleteall' => 1];
            $murl = new moodle_url($url, $params);
            $text = get_string('delete_all', 'quizaccess_announcements');
            $button = new single_button($murl, $text, 'get', $type);
            $data->delbtn = $this->render($button);
        }
        $data->quizid = $this->rule->quizid;
        return $this->output->render_from_template('quizaccess_announcements/manage', $data);
    }

    /**
     * Renders the announcement header.
     *
     * @return string html of the announcement header.
     */
    private function format_header() {
        $rewritten = file_rewrite_pluginfile_urls(
            $this->rule->header,
            'pluginfile.php',
            $this->context->id,
            'quizaccess_announcements',
            'header',
            null);
        return format_text(
            $rewritten,
            $this->rule->headerformat,
            ['noclean' => true, 'context' => $this->context->id]);
    }
}
