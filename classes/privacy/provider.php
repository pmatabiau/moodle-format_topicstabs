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
 * Privacy provider for format_topicstabs.
 *
 * @package		format_topicstabs
 * @author		Philippe Matabiau
 * @copyright	2026 ÉTS Montréal {@link http://etsmtl.ca}
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_topicstabs\privacy;

/**
 * The plugin stores no personal data.
 *
 * @package    format_topicstabs
 */
class provider implements \core_privacy\local\metadata\null_provider {

    /**
     * Explain why no data is stored.
     *
     * @return string
     */
    public static function get_reason(): string {
        return 'privacy:metadata';
    }
}
