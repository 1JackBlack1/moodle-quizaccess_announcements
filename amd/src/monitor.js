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
 * Check student status for the quizaccess_announcements plugin.
 *
 * @module     quizaccess_announcements/monitor
 * @copyright  Jeffrey Black
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getstatus} from './repository';
import {get_string as getString} from 'core/str';

/**
 * Variables to hold the quizid, the time interval to check and the container element.
 * param {number} quizid id of the quiz.
 * param {number} studentinterval polling interval for students checking announcemnets.
 * param {number} delay polling interval for checking student status.
 * param {Element} hcont container for last announcement message.
 * param {Element} tcont table which contains student status.
 * param {number} ecnt count of number of failures.
 */
var quizid;
var studentinterval;
var delay;
var hcont;
var tcont;
var ecnt;


/**
 * Clears classes to pretify rows.
 *
 * @param {Element} row row to remove classes for.
 */
function clearclasses(row) {
    row.classList.remove('table-success');
    row.classList.remove('table-warning');
    row.classList.remove('table-danger');
}

/**
 * Updates the table of student data.
 *
 * @param {Element} ntbody new table body element to add row to.
 * @param {number|false} last timestamp of last time announcement was posted.
 * @returns {function} a function that can be passed a student to update.
 */
function updatestudentnow(ntbody, last) {
    /**
     * Updates the student data.
     *
     * @param {object} student objcet containing details of student.
     */
    return function updatestudent(student) {
        let row = tcont.querySelector('tr.u_' + student.userid);
        if (!row) {
            window.console.log("Unable to find row for student " + student.userid);
            return;
        }
        try {
            clearclasses(row);
            row.querySelector('td.cell.time').innerHTML = student.str;
            row.querySelector('td.cell.ago').innerHTML = student.ago;
            if (student.ago > studentinterval * 2) {
                row.classList.add('table-danger');
            } else if (student.ago > studentinterval) {
                row.classList.add('table-warning');
            } else if ((last !== null) && (last > student.time)) {
                row.classList.add('table-warning');
            } else {
                row.classList.add('table-success');
            }
        } catch (e) {
            window.console.error("Error updating student status", e);
            ecnt = 10;
        }
        ntbody.appendChild(row);
    };
}

/**
 * Makes request to get status.
 *
 */
async function fetchstatus() {
    var getstatusp = getstatus(quizid)
    .then((statret) => {
        ecnt = 0;
        hcont.innerHTML = statret.last.str;
        var ntbody = document.createElement('tbody');
        statret.status.forEach(updatestudentnow(ntbody, statret.last.time));
        var row = tcont.rows[0];
        while (row) {
            clearclasses(row);
            row.querySelector('td.cell.time').innerHTML = '-';
            row.querySelector('td.cell.ago').innerHTML = '-';
            ntbody.appendChild(row);
            row = tcont.rows[0];
        }
        tcont.replaceWith(ntbody);
        tcont = ntbody;
        return statret;
    }).catch((err) => {
        window.console.error("Error in AJAX request", err);
        ecnt++;
        return err;
    });
    await getstatusp;
    if (ecnt < 5) {
        window.setTimeout(fetchstatus, delay);
    } else {
        getString('monitor_ajax_error', 'quizaccess_announcements')
        .then((str) => {
            hcont.innerHTML = str;
            return str;
        }).catch((err) => {
            window.console.error("Error getting string", err);
            hcont.innerHTML = 'Error fetching student status. Additionally, error fetching error string.';
        });
    }
}

/**
 * Sets up the page for monitoring student status.
 *
 * @param {number} qid id of the quiz.
 * @param {number} checkinterval how often students should fetch announcements.
 * @param {number} interval how often to update status.
 * @param {string} lastid id of the last post status element.
 * @param {string} contid id of the container element.
 */
function init(qid, checkinterval, interval, lastid, contid) {
    quizid = qid;
    studentinterval = parseInt(checkinterval);
    delay = parseInt(interval) * 1000;
    hcont = document.getElementById(lastid);
    tcont = document.getElementById(contid);
    ecnt = 0;
    if (!(tcont && (tcont = tcont.querySelector('tbody')))) {
        window.console.error("Unable to find table body to update.", contid);
        return;
    }
    window.setTimeout(fetchstatus, delay);
}

export {init};
