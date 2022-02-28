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
 * @package     local_nudge
 * @author      Liam Kearney
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 * 
 * @var \core_config        $CFG
 * @var \moodle_database    $DB
 */

defined('MOODLE_INTERNAL') || die;

#region BEGIN: Evil extensible code using function names.

/**
 * Adds the link to start tracking a course for completion reminders.
 *
 * @access public
 * @param \navigation_node $parentnode
 * @param \stdClass $course
 * @param \context_course $context
 * @return void
 */
function local_nudge_extend_navigation_course(\navigation_node $parentnode, \stdClass $course, \context_course $context)
{
    if (!\has_capability('local/nudge:trackcourse', $context)) return;

    $url = new moodle_url('/local/nudge/track_course_form.php', [
        'courseid' => $course->id
    ]);

    $parentnode->add(
        \get_string('trackcourse', 'local_nudge'),
        $url,
        \navigation_node::TYPE_SETTING,
        null,
        null,
        new \pix_icon('i/settings', '')
    );
}

#endregion