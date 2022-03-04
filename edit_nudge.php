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
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 * 
 * @var \core_config        $CFG
 * @var \moodle_database    $DB
 * @var \moodle_page        $PAGE
 * @var \core_renderer      $OUTPUT
 */

use local_nudge\dml\nudge_db;
use local_nudge\form\nudge\edit;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$course_id = \required_param('courseid', \PARAM_INT);

// Require a login for this course for this course.
$course = \get_course($course_id);
\require_login($course);
$context = \context_course::instance($course->id);

// Require the permissions to track courses.
\require_capability('local/nudge:trackcourse', $context);

// Set up the page.
$base_url = new \moodle_url("/local/nudge/edit_nudge.php", ['courseid' => $course_id]);
$PAGE->set_url($base_url);

$mform = new edit(new \moodle_url('/local/nudge/edit_nudge.php', ['courseid' => $course_id]));

// Edit form submision handling.
if ($mform->is_cancelled()) {
    \redirect(new \moodle_url('/course/view.php', ['id' => $course_id]));
} else if ($nudge = $mform->get_data()) {
    $id = nudge_db::save($nudge);
    \redirect(new \moodle_url('/course/view.php', ['id' => $course_id]));
}

// Create an instance for this course if it doesn't exist yet and this page has been visited.
$nudge = nudge_db::find_or_create($course_id);

// Render the form.
echo $OUTPUT->header();
$mform->set_data($nudge);
$mform->display();
echo $OUTPUT->footer();
