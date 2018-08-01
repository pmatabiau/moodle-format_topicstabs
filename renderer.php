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
 * Renderer for outputting the topics course format.
 *
 * @package    format
 * @subpackage topicstabs
 * @author Philippe MATABIAU
 * @copyright 2018 ÉTS Montréal {@link http://etsmtl.ca}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/format/topics/renderer.php');

/**
 * Basic renderer for topicstabs format.
 *
 * @package    format
 * @subpackage topicstabs
 * @author Philippe MATABIAU
 * @copyright 2018 ÉTS Montréal {@link http://etsmtl.ca}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class format_topicstabs_renderer extends format_topics_renderer {

  private $format_instance;
  private $aSections = [];

  /**
   * Output the html for a multiple section page
   *
   * @param stdClass $course The course entry from DB
   * @param array $sections (argument not used)
   * @param array $mods (argument not used)
   * @param array $modnames (argument not used)
   * @param array $modnamesused (argument not used)
   */
  public function print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused) {
    global $PAGE;

    // if edition mode, use topics course format
    if ($PAGE->user_is_editing() || $course->coursedisplay == COURSE_DISPLAY_MULTIPAGE) {
      // Include course format js module
      $PAGE->requires->js('/course/format/topics/format.js');
      return parent::print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused);
    }

    $this->format_instance = course_get_format($course);
    $_course = $this->format_instance->get_course();
    $modinfo = get_fast_modinfo($_course);
    $context = context_course::instance($_course->id);

    // Title with completion help icon.
    $completioninfo = new completion_info($_course);
    $o['top'] = [
      $completioninfo->display_help_icon(),
      $this->output->heading($this->page_title(), 2, 'accesshide')
    ];
    // Copy activity clipboard..
    $o['top'][] = $this->course_activity_clipboard($_course, 0);

    // The list of sections..
//    $numsections = $this->format_instance->get_last_section_number();
    $aSections = $modinfo->get_section_info_all();

    // section 0: always out of tabs ; use parent template (<li>)
    $o['section0'] = '';
    if ($aSections[0]->summary || !empty($modinfo->sections[0])) {
      $o['section0'] = [
        parent::section_header($aSections[0], $_course, false, 0),
        $this->_section_content($_course, 0, $aSections[0]),
        parent::section_footer(),
      ];
    }

    $this->hydrate_sections($modinfo, $_course);
    foreach ($this->aSections as $sectionNum => $sectionInfos) {
      if ($sectionInfos['display'] == FALSE)
        continue;

      $o['sections'][$sectionNum] = [
        $this->_section_header($sectionNum, $_course),
        $this->_section_content($_course, $sectionNum, $sectionInfos['datas']),
        $this->_section_footer(),
      ];
    }

    // build rendering
    $output = [
      $o['top'],
      html_writer::start_div('topicstabs'),
      $o['section0'],
      $this->build_nav_tabs(),
      '<!-- Tab panes -->',
      html_writer::start_div('tab-content'),
      $o['sections'],
      html_writer::end_div(), // end div tab-content
      html_writer::end_div(), // end div topicstabs
    ];

    echo $this->multi_implode(PHP_EOL, $output);
  }

  protected function hydrate_sections($modinfo, $course) {
    $numsections = $this->format_instance->get_last_section_number();
    $this->aSections['active'] = 1;

    foreach ($modinfo->get_section_info_all() as $section => $thissection) {
      if (!$this->is_showed_section($course, $section, $thissection) || $section > $numsections) {
        $this->aSections[$section]['display'] = FALSE;
        continue;
      }

      $this->aSections[$section] = [
        'display' => TRUE,
        'name' => get_section_name($course, $section),
        'marked' => $this->format_instance->is_section_current($section),
        'muted' => !$thissection->visible,
        'datas' => $thissection,
      ];
      if ($this->aSections[$section]['marked'])
        $this->aSections['active'] = $section;
    }
  }

  protected function build_nav_tabs() {
    $o = ['<!-- Nav tabs -->'];
    $o[] = html_writer::start_tag('ul', [
      'style' => 'list-style: outside none none',
      'class' => 'nav nav-tabs',
      'role' => 'tablist',
    ]);
    foreach ($this->aSections as $sectionNum => $sectionInfos) {
      if ($sectionInfos['display'] == FALSE)
        continue;

      $active = ($sectionNum == $this->aSections['active']) ? 'active' : '';
      $marked = $sectionInfos['marked'] ? 'tabcurrent' : '';
      $muted = $sectionInfos['muted'] ? 'muted' : ''; // hidden for studiants

      $tabTitle = html_writer::link('#section-' . $sectionNum, $sectionInfos['name'], [
        'class' => "nav-link $active $marked $muted",
        'data-toggle' => 'tab',
        'role' => 'tab',
      ]);
      $o[] = html_writer::tag('li', $tabTitle, ['class' => 'nav-item']);
    }
    $o[] = html_writer::end_tag('ul');
    return $o;
  }

  protected function _section_header($sectionNum, $course) {
    global $PAGE;

    $onsectionpage = FALSE;
    $aCssClasses = ['section', 'main', 'clearfix', 'tab-pane', 'fade'];

    $section = $this->aSections[$sectionNum];
    $oSection = $section['datas'];

    if ($section['marked'])
      $aCssClasses[] = 'current';
    if ($section['muted'])
      $aCssClasses[] = 'muted';
    if ($sectionNum == $this->aSections['active'])
      array_push($aCssClasses, 'active', 'show');

    $o[] = html_writer::start_div(implode(' ', $aCssClasses), [
      'id' => 'section-' . $sectionNum,
      'role' => 'tabpanel',
      'aria-labelledby' => $section['name'],
      ]);

    // Create a span that contains the section title to be used to create the keyboard section move menu.
    $o[] = html_writer::span($section['name'], 'hidden sectionname');

    $leftcontent = $this->section_left_content($oSection, $course, $onsectionpage);
    $o[] = html_writer::div($leftcontent, 'left side');
    $rightcontent = $this->section_right_content($oSection, $course, $onsectionpage);
    $o[] = html_writer::div($rightcontent, 'right side');

    $o[] = html_writer::start_div('content');

    $sectionname = html_writer::span($this->section_title_without_link($oSection, $course));
    $o[] = $this->output->heading($sectionname, 3, 'sectionname');

    $o[] = $this->section_availability($oSection);

    $o[] = html_writer::start_div('summary');
    if ($oSection->uservisible || $oSection->visible) {
      // Show summary if section is available or has availability restriction information.
      // Do not show summary if section is hidden but we still display it because of course setting
      // "Hidden sections are shown in collapsed form".
      $o[] = $this->format_summary_text($oSection);
    }
    $o[] = html_writer::end_div(); // end div summary

    return $o;
  }

  protected function _section_content($course, $section, $thissection) {
    $o = [];
    if ($section == 0 || $thissection->uservisible) {
      $o[] = $this->courserenderer->course_section_cm_list($course, $thissection, 0);
      $o[] = $this->courserenderer->course_section_add_cm_control($course, $section, 0);
    }

    return $o;
  }

  /**
   * Generate the display of the footer part of a section
   *
   * @return string HTML to output.
   */
  protected function _section_footer() {
    $o[] = html_writer::end_div(); // end div .content
    $o[] = html_writer::end_div(); // end div section-#

    return $o;
  }

  protected function is_showed_section($course, $section, $thissection) {
    // section 0 already treated ; section 'orphaned' display only in edit mode (so with the topics format)
    if ($section == 0)
      return false;

    // Show the section if the user is permitted to access it, OR if it's not available
    // but there is some available info text which explains the reason & should display,
    // OR it is hidden but the course has a setting to display hidden sections as unavilable.
    $showsection = $thissection->uservisible ||
        ($thissection->visible && !$thissection->available && !empty($thissection->availableinfo)) ||
        (!$thissection->visible && !$course->hiddensections);
    if (!$showsection)
      return false;

    return true;
  }

  private function multi_implode($glue, $arr) {
    $o = '';
    foreach ($arr as $item) {
      $o .= is_array($item) ? $this->multi_implode($glue, $item) : $item;
      $o .= $glue;
    }
    return substr($o, 0, 0 - strlen($glue));
  }

}
