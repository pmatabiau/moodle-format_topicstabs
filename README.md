# Topics with tabs (format_topicstabs)

Course format for Moodle 5.1+/5.2 which **extends `format_topics`**.
Outside editing mode, every section (general section 0 included) becomes a tab.
In editing mode the standard Topics display is kept.

## Installation
Copy this folder to `<moodle>/public/course/format/topicstabs`, then visit *Site administration > Notifications*.
(Moodle >= 5.1: plugins go under `public/`.)

## Features
- Everything inherited from `format_topics` (editing, course index, completion, restrictions...).
- Tabs navigation with ARIA roles and keyboard support (left/right arrows, Home, End).
- Deep links: `#section-N` open the right tab (course index, notifications...).
- The last visited tab is remembered during the browser session.
- Everything is shown when printing.

## Hidden and restricted sections
Sections that a student cannot open (hidden by the teacher, or restricted by access conditions) are handled
by the course setting **Hidden sections**, which this format extends with a third value:

| Course setting | Hidden by the teacher | Restricted by access conditions |
|---|---|---|
| Hidden or restricted sections are shown in their shortened form | Tab shown (dimmed) | Tab shown (dimmed), with the availability message |
| Hidden sections are completely invisible | No tab | Tab shown (dimmed), with the availability message |
| Restricted sections are completely invisible | No tab | No tab |

- A section that is shown but not accessible gets a dimmed tab with an eye-slash icon (hidden by the teacher) or a lock icon (restricted by access conditions).
- Teachers, and any user who can view hidden sections or ignore availability conditions, always see every section
  and every tab: this setting only affects students.
- The tabs are built on the server from the very sections that Moodle displays, and the visibility rule is
  implemented in `format_topicstabs::is_section_visible()`. The tabs therefore always match the page content,
  and the course index uses the same rule.

## About
- [Philippe Matabiau](https://github.com/pmatabiau) (author)
- [Plugin project](https://github.com/pmatabiau/moodle-format_topicstabs) at GitHub.com

