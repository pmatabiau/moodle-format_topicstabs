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
 * Contains the topicstabs content output class.
 *
 * @package			format
 * @subpackage	topicstabs
 * @author			Philippe MATABIAU
 * @copyright		2023 ÉTS Montréal {@link http://etsmtl.ca}
 * @license			http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_topicstabs\output\courseformat;

use core_courseformat\output\local\content as content_base;

class content extends content_base {

	/**
	 * @var bool Topic format has add section after each topic.
	 *
	 * The responsible for the buttons is core_courseformat\output\local\content\section.
	 */
	protected $hasaddsection = false;

	public function get_template_name(\renderer_base $renderer): string {
		global $PAGE;
		$format	 = $this->format;
		$course	 = $format->get_course();

		if ($PAGE->user_is_editing() || $course->coursedisplay == COURSE_DISPLAY_MULTIPAGE) {
			return parent::get_template_name($renderer);
		} else
			return 'format_topicstabs/content';
	}

	public function export_for_template(\renderer_base $output) {
		global $PAGE;
		$data			 = parent::export_for_template($output);
		$format = $this->format;
		$course	 = $format->get_course();

		if ($PAGE->user_is_editing() || $course->coursedisplay == COURSE_DISPLAY_MULTIPAGE) {
			return $data;
		}

//		$numsections = $format->get_last_section_number();

		$aSections = [];
		foreach ($data->sections as $section) {
			$marked	 =  !empty($section->iscurrent) && $section->iscurrent ? 'tabcurrent' : '';
			$muted	 =  !empty($section->ishidden) && $section->ishidden ? 'muted' : ''; // hidden for studiants

			$aSections[] = [
				'id'		 => $section->header->id,
				'num'		 => $section->header->num,
				'name'	 => $section->header->name,
				'active' => null,
				'marked' => $marked,
				'muted'	 => $muted,
			];
		}
		$data->topicstabs = $aSections;

		return $data;
	}

}
