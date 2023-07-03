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
 * Display announcements for the quizaccess_announcements plugin.
 *
 * @module     quizaccess_announcements/announcements
 * @copyright  Jeffrey Black
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getannouncements} from './repository';
import Templates from 'core/templates';
import ModalFactory from 'core/modal_factory';
import * as Str from 'core/str';


/**
 * Vars to store
 * @param {number} quizid id number of quiz.
 * @param {number} delay time interval between checks (ms).
 * @param {number} lastfetched the unixtimestamp for the last time announcements were checked.
 * @param {Element} anoncont container to hold announcements.
 * @param {Element} noneyet element showing no announcemnets have been made.
 * @param {object} modal the modal dialogue to show announcements in.
 */
var quizid;
var delay;
var lastfetched;
var anoncont;
var noneyet;
var modal;

/**
 * Shows new announcements in a modal dialog.
 *
 * @param {string} newanons string containing text of new announcements.
 */
const showmodal = (newanons) => {
    if (modal.isVisible()) {
        Templates.appendNodeContents(modal.body, newanons, '');
    } else {
        Templates.replaceNodeContents(modal.body, newanons, '');
        modal.show();
    }
};

/**
 * Adds new announcement text. Note: scripts not evaluated.
 *
 * @param {string} newanons HTML containing new announcements.
 */
const addanons = (newanons) => {
    if (!newanons) {
        return;
    }
    if (noneyet) {
        noneyet.remove();
    }
    Templates.appendNodeContents(anoncont, newanons, '');
    showmodal(newanons);
};

/**
 * Function to poll for announcements.
 *
 */
const fetchannouncements = async() => {
    var getanonp = getannouncements(quizid, lastfetched)
    .then((newanons) => {
        addanons(newanons.content);
        lastfetched = newanons.lasttime;
        return newanons;
    }).catch((err) => {
        window.console.error("Error in AJAX request", err);
        return err;
    });
    await getanonp;
    window.setTimeout(fetchannouncements, delay);
};

/**
 * Sets up the page for displaying announcements.
 *
 * @param {number} qid id of the quiz.
 * @param {number|false} poll delay between fetching announcements or false to disable.
 * @param {number} now timestamp of last time announcements were fetched.
 * @param {string} containerid id of the container for the announcements.
 * @param {string} header html for the announcement header.
 * @param {string} toshow html for current announcements to show.
 */
export const init = (qid, poll, now, containerid, header, toshow) => {
    Templates.prependNodeContents(document.getElementById('region-main'), header, '');
    anoncont = document.getElementById(containerid);
    if (poll && anoncont) {
        quizid = qid;
        delay = parseInt(poll) * 1000;
        noneyet = anoncont.querySelector('[data-noannouncements]');
        lastfetched = now;
        Str.get_string('popupheader', 'quizaccess_announcements')
        .then((str) => {
            return ModalFactory.create({
                title: str,
                type: ModalFactory.types.ALERT,
                large: true
            });
        }).then((m) => {
            modal = m;
            if (toshow) {
                showmodal(toshow);
            }
            window.setTimeout(fetchannouncements, delay);
            return m;
        }).catch((err) => {
            window.console.error('Error setting up announcements', err);
        });
    }
};
