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

use core\output\notification;
use local_nudge\dml\nudge_db;
use local_nudge\form\nudge\edit;
use local_nudge\local\nudge;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$nudgeid = \required_param('id', \PARAM_INT);
$courseid = \required_param('courseid', \PARAM_INT);
// TODO: CourseID doesn't exist.

\require_login($courseid);
$context = \context_course::instance($courseid);
\require_capability('local/nudge:configurenudges', $context);

$manageurl = new \moodle_url('/local/nudge/manage_nudges.php', ['courseid' => $courseid]);
$PAGE->set_url($manageurl);

$mform = new edit();

if ($mform->is_cancelled()) {
    \redirect($manageurl);
} else if ($editdata = $mform->get_data()) {
    if ($editdata === null) {
        \redirect(
            $manageurl,
            'Unable to save nudge',
            null,
            notification::NOTIFY_ERROR
        );
    }

    $nudge = nudge_db::create_or_refresh($editdata);
    \redirect(
        $manageurl,
        "Edited nudge '{$nudge->title}' successfully",
        null,
        notification::NOTIFY_SUCCESS
    );
}

// We do this so cancelations of unsaved nudge forms know which course to return to.
$newnudge = new nudge();
$newnudge->courseid = $courseid;

if ($nudgeid === 0) {
    $nudge = $newnudge;
} else {
    $nudge = nudge_db::get_by_id($nudgeid);
    if ($nudge === null) {
        throw new moodle_exception('nudgedoesntexist', 'local_nudge', '', $nudgeid);
    }
}

$mform->set_data($nudge);

echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();
