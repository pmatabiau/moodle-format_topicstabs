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
 * This file contains main class for the course format TopicTabs
 *
 * @package    format
 * @subpackage topicstabs
 * @author Philippe MATABIAU
 * @copyright 2018 ÉTS Montréal {@link http://etsmtl.ca}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot. '/course/format/topics/lib.php');

/**
 * Main class for the TopicsTabs course format
 *
 * @package    format
 * @subpackage topicstabs
 * @author Philippe MATABIAU
 * @copyright 2018 ÉTS Montréal {@link http://etsmtl.ca}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_topicstabs extends format_topics {

	public function course_format_options($foreditform = false) {
		$courseformatoptions = parent::course_format_options($foreditform);
		if ($foreditform) {
			$courseformatoptions['hiddensections']['element_attributes'] = [
				[
					0 => new lang_string('hiddensectionscollapsed', 'format_topicstabs'),
					1 => new lang_string('hiddensectionsinvisible'),
					2 => new lang_string('restrictedsectionsinvisible', 'format_topicstabs'),
				]
			];
			unset($courseformatoptions['coursedisplay']['element_attributes'][0][COURSE_DISPLAY_MULTIPAGE]);
		}
		return $courseformatoptions;
	}

}

/**
 * Implements callback inplace_editable() allowing to edit values in-place
 *
 * @param string $itemtype
 * @param int $itemid
 * @param mixed $newvalue
 * @return \core\output\inplace_editable
 */
function format_topicstabs_inplace_editable($itemtype, $itemid, $newvalue) {
    global $DB, $CFG;
    require_once($CFG->dirroot . '/course/lib.php');
    if ($itemtype === 'sectionname' || $itemtype === 'sectionnamenl') {
        $section = $DB->get_record_sql(
            'SELECT s.* FROM {course_sections} s JOIN {course} c ON s.course = c.id WHERE s.id = ? AND c.format = ?',
            array($itemid, 'topicstabs'), MUST_EXIST);
        return course_get_format($section->course)->inplace_editable_update_section_name($section, $itemtype, $newvalue);
    }
}
