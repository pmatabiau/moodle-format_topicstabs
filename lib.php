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
 * Topics with tabs course format: extends the core format_topics.
 *
 * @package		format_topicstabs
 * @author		Philippe Matabiau
 * @copyright	2026 ÉTS Montréal {@link http://etsmtl.ca}
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/format/topics/lib.php');

/**
 * Main class for the Topics with tabs course format.
 *
 * Everything (sections, activities, editing, course index, completion...) is inherited
 * from format_topics. This class only adds the options specific to the tabbed display.
 *
 * @package    format_topicstabs
 */
class format_topicstabs extends format_topics {

	/** @var string Classic tabs look. */
	public const TABSSTYLE_TABS = 'tabs';

	/** @var string Pills look. */
	public const TABSSTYLE_PILLS									= 'pills';

	public const HIDDENSECTIONS_COLLAPSED					= 0;
	public const HIDDENSECTIONS_HIDDEN_HIDDEN			= 1;
	public const HIDDENSECTIONS_RESTRICTED_HIDDEN = 2;

	/**
	 * Course format options: all those of format_topics, plus the tabs style.
	 *
	 * @param bool $foreditform
	 * @return array
	 */
	#[\Override]
	public function course_format_options($foreditform = false) {
		$options = parent::course_format_options($foreditform);

		if ($foreditform) {
			// Modification des options existantes pour l'affichages des sections cachées/restreintes.
			// Remplace le texte de l'option valeur 0 (sections montrées forme raccourcie).
			$options['hiddensections']['element_attributes'][0]->add_option(
					self::HIDDENSECTIONS_COLLAPSED,
					new lang_string('hiddensectionscollapsed', 'format_topicstabs'),
					[
						'description' => new lang_string('hiddensectionscollapsed_description'),
					],
			);
			// Ajoute l'option valeur 2 (sections restreintes cachées).
			$options['hiddensections']['element_attributes'][0]->add_option(
					self::HIDDENSECTIONS_RESTRICTED_HIDDEN,
					new lang_string('restrictedsectionsinvisible', 'format_topicstabs'),
					[
						'description' => new lang_string('restrictedsectionsinvisible_description', 'format_topicstabs'),
					],
			);

			// Ajout d'une option pour le style des onglets (classics ou pastilles).
//			$options['tabsstyle']												= [
//				'default' => self::TABSSTYLE_TABS,
//				'type'		=> PARAM_ALPHA,
//			];
//			$options['tabsstyle']['label']							= new lang_string('tabsstyle', 'format_topicstabs');
//			$options['tabsstyle']['help']								= 'tabsstyle';
//			$options['tabsstyle']['help_component']			= 'format_topicstabs';
//			$options['tabsstyle']['element_type']				= 'select';
//			$options['tabsstyle']['element_attributes'] = [
//				[
//					self::TABSSTYLE_TABS	=> new lang_string('tabsstyle_tabs', 'format_topicstabs'),
//					self::TABSSTYLE_PILLS => new lang_string('tabsstyle_pills', 'format_topicstabs'),
//				],
//			];
		}
		return $options;
	}

	/**
	 * Returns if an specific section is visible to the current user.
	 *
	 * @param section_info $section the section modinfo
	 * @return bool;
	 */
	#[\Override]
	public function is_section_visible(section_info $section): bool {
		// It is unlikely that a section is orphan, but it needs to be checked.
		if ($section->is_orphan() && !has_capability('moodle/course:viewhiddensections', $this->get_context())) {
			return false;
		}
		// Déjà visible pour cet utilisateur : rien à décider.
		if ($section->uservisible) {
			return true;
		}

		$hidesections = (int) $this->get_course()->hiddensections;

		// Masquée par l'enseignant : affichée (grisée) seulement si l'option du cours le permet.
		if (!$section->visible) {
			return $hidesections === self::HIDDENSECTIONS_COLLAPSED;
		}

		// Visible mais restreinte par des conditions : affichée avec son message de disponibilité,
		// sauf si l'option du cours demande de la masquer.
		return !empty($section->availableinfo) && $hidesections !== self::HIDDENSECTIONS_RESTRICTED_HIDDEN;
	}
}

/**
 * Implements callback inplace_editable() allowing to edit section names in place.
 *
 * Required because the component of the inplace editable is the format itself.
 *
 * @param string $itemtype
 * @param int $itemid
 * @param mixed $newvalue
 * @return \core\output\inplace_editable|null
 */
function format_topicstabs_inplace_editable($itemtype, $itemid, $newvalue) {
	global $DB, $CFG;
	require_once($CFG->dirroot . '/course/lib.php');
	if ($itemtype === 'sectionname' || $itemtype === 'sectionnamenl') {
		$section = $DB->get_record_sql(
				'SELECT s.* FROM {course_sections} s JOIN {course} c ON s.course = c.id WHERE s.id = ? AND c.format = ?',
				[$itemid, 'topicstabs'],
				MUST_EXIST
		);
		return course_get_format($section->course)->inplace_editable_update_section_name($section, $itemtype, $newvalue);
	}
}
