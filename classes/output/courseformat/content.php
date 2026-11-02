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
 * @package		format_topicstabs
 * @author		Philippe Matabiau
 * @copyright	2026 ÉTS Montréal {@link http://etsmtl.ca}
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace format_topicstabs\output\courseformat;

use format_topics\output\courseformat\content as topics_content;
use renderer_base;

/**
 * Content of the Topics with tabs format.
 *
 * Outside editing mode the page is rendered by our own mustache template (format_topicstabs/content): the tabs and
 * the tab panes are built on the server, from the very sections that Moodle exports, so that the tabs always match
 * what is displayed (restricted sections, hidden sections...). In editing mode (or in single section mode) the
 * standard Topics template is used, so the reactive course editor is not disturbed.
 *
 * @package    format_topicstabs
 * @copyright  2026 ÉTS Montréal {@link http://etsmtl.ca}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content extends topics_content {

    /**
     * Whether the page is displayed as tabs (otherwise the standard Topics display is used).
     *
     * @return bool
     */
    protected function uses_tabs(): bool {
        global $PAGE;
        $course = $this->format->get_course();
        return !$PAGE->user_is_editing()
            && $this->format->get_sectionnum() === null
            && $course->coursedisplay != COURSE_DISPLAY_MULTIPAGE;
    }

    #[\Override]
    public function get_template_name(renderer_base $renderer): string {
        return $this->uses_tabs() ? 'format_topicstabs/content' : parent::get_template_name($renderer);
    }

    #[\Override]
    public function export_for_template(renderer_base $output) {
        global $PAGE;

        $data = parent::export_for_template($output);
        if (!$this->uses_tabs()) {
            return $data;
        }

        $format = $this->format;
        $modinfo = $format->get_modinfo();

        // One tab per exported section (section 0 is exported apart, as "initialsection", and stays above the tabs).
        $tabs = [];
        $hasmarked = false;
        foreach ($data->sections as $section) {
            $info = $modinfo->get_section_info((int) $section->num);
            $marked = $format->is_section_current($info);
            $hasmarked = $hasmarked || $marked;
            $tabs[] = (object) [
                'num' => $section->num,
                'name' => $format->get_section_name($info),
                'marked' => $marked,
                // Hidden by the teacher (and not shown as usual to this user).
                'hidden' => !$info->visible,
                // Visible, but not accessible to this user because of access restrictions.
                'restricted' => $info->visible && !$info->uservisible,
                'dimmed' => !$info->visible || !$info->uservisible,
                'active' => false,
            ];
        }

        // Tab shown before the JS chooses according to the URL / last visit: the marked section, or the first one.
        foreach ($tabs as $key => $tab) {
            if ($tab->marked || (!$hasmarked && $key === 0)) {
                $tab->active = true;
                break;
            }
        }
        foreach ($data->sections as $key => $section) {
            $section->tabactive = $tabs[$key]->active;
        }

        $options = $format->get_format_options();
        $style = $options['tabsstyle'] ?? \format_topicstabs::TABSSTYLE_TABS;
        $data->navclass = $style === \format_topicstabs::TABSSTYLE_PILLS ? 'nav-pills' : 'nav-tabs';
        $data->topicstabs = $tabs;
        $data->hastabs = count($tabs) > 1;

        if ($data->hastabs) {
            $PAGE->requires->js_call_amd('format_topicstabs/tabs', 'init', [(int) $format->get_courseid()]);
        }
        return $data;
    }
}
