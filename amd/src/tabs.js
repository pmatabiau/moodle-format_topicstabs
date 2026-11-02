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
 * Behaviour of the tabs of the Topics with tabs format.
 *
 * The tabs and panes are rendered by the server and switched by Bootstrap (data-bs-toggle="tab"). This module
 * only chooses the tab to show on load (URL hash, then last visited tab, then the one rendered by the server)
 * and keeps the URL hash up to date.
 *
 * @module     format_topicstabs/tabs
 * @copyright	2026 ÉTS Montréal {@link http://etsmtl.ca}
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Loading the Bootstrap tab module registers its data API (data-bs-toggle="tab").
import 'theme_boost/bootstrap/tab';

const ROOT = '[data-region="topicstabs"]';

/**
 * Show the tab of a section, unless it is already the active one.
 *
 * @param {HTMLElement} root
 * @param {String|null} number section number
 * @returns {Boolean} whether the section has a tab
 */
const showSection = (root, number) => {
    const link = /^\d+$/.test(number) ? root.querySelector(`[data-section-number="${number}"]`) : null;
    if (link && !link.classList.contains('active')) {
        link.click();
    }
    return !!link;
};

/**
 * Initialise the tabs.
 *
 * @param {Number} courseid
 */
export const init = (courseid) => {
    const root = document.querySelector(ROOT);
    if (!root) {
        return;
    }
    const key = `format_topicstabs_${courseid}`;

    const fromHash = () => {
        const match = /^#section-(\d+)$/.exec(window.location.hash);
        return match ? showSection(root, match[1]) : false;
    };

    if (!fromHash()) {
        try {
            showSection(root, window.sessionStorage.getItem(key));
        } catch (e) {
            // Storage not available: keep the tab chosen by the server.
        }
    }

    root.addEventListener('shown.bs.tab', (event) => {
        const number = event.target.dataset.sectionNumber;
        window.history.replaceState(null, '', `#section-${number}`);
        try {
            window.sessionStorage.setItem(key, number);
        } catch (e) {
            // Storage not available.
        }
    });

    // Links to a section (course index, ...).
    window.addEventListener('hashchange', fromHash);
};
