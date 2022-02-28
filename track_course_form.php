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
 * @var \moodle_page        $PAGE
 * @var \core_renderer      $OUTPUT
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

//                          name        default  filter
$courseid = \required_param('courseid',         \PARAM_INT);
$ruleid   = \optional_param('ruleid',   0,      \PARAM_INT);
$action   = \optional_param('action',   '',     \PARAM_ALPHA);
$confirm  = \optional_param('confirm',  false,  \PARAM_BOOL);
$status   = \optional_param('status',   0,      \PARAM_BOOL);

// Require a login for this course for this course.
$course = \get_course($courseid);
\require_login($course);
$context = \context_course::instance($course->id);

// Require the permissions to track courses.
\require_capability('local/nudge:trackcourse', $context);

// Set up the page.
$manageurl = new \moodle_url("/local/nudge/track_course_form.php", ['courseid' => $courseid]);
$PAGE->set_url($manageurl);
$PAGE->set_pagelayout('base');
$coursename = \format_string(
    $course->fullname,
    true,
    ['context' => $context]
);
$PAGE->set_title($coursename);
$PAGE->set_heading($coursename);

// Render the form.
echo $OUTPUT->header();
echo $OUTPUT->heading(\get_string('managetracking', 'local_nudge'));

// echo $OUTPUT->confirm($strconfirm, $confirmurl, $cancelurl);
echo $OUTPUT->footer();
return;
