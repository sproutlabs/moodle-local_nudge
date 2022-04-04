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

// phpcs:disable moodle.Commenting

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

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');

$courseid = \required_param('courseid', \PARAM_INT);

// Require a login for this course for this course.
$course = \get_course($courseid);
\require_login($course);
$context = \context_course::instance($course->id);

// Require the permissions to track courses.
\require_capability('local/nudge:configurenudges', $context);

$baseurl = new \moodle_url('/local/nudge/manage_nudges.php', ['courseid' => $courseid]);
$PAGE->set_url($baseurl);

echo $OUTPUT->header();

echo $OUTPUT->single_button(
    new moodle_url(
        '/local/nudge/edit_nudge.php',
        [
            'id' => 0,
            'courseid' => $courseid
        ]
    ),
    get_string('manage_nudge_add', 'local_nudge'),
    'get'
);

$table = new \flexible_table('nudge_table');
$table->define_baseurl(new moodle_url('/local/nudge/manage_nudges.php'));
$table->define_columns([
    'id',
    'learnerreminder',
    'managerreminder',
    'type',
    'actions'
]);
$table->define_headers([
    'ID',
    'Learner Reminder',
    'Manager Reminder',
    'Type',
    'Actions'
]);
$table->sortable(false);
$table->setup();

$nudgetable = nudge_db::$table;
$selectsql = <<<SQL
    SELECT
        *
    FROM
        {{$nudgetable}} as nudge
    WHERE
        nudge.courseid = :courseid
SQL;

$nudgestomanage = nudge_db::get_all_sql($selectsql, ['courseid' => $courseid]);

foreach ($nudgestomanage as $nudge) {

    $rowfields = $nudge->get_summary_fields();

    /** @var callable */
    $formatstring = '\format_string';
    \array_walk($rowfields, $formatstring);

    $rowactions = [
        \implode('', [
            $OUTPUT->action_icon(
                new \moodle_url('/local/nudge/edit_nudge.php', [
                    'id' => $nudge->id,
                    'courseid' => $courseid
                ]),
                new \pix_icon('t/edit', 'Edit')
            ),
            $OUTPUT->action_icon(
                new \moodle_url('/local/nudge/delete_nudge.php', [
                    'id' => $nudge->id,
                    'courseid' => $courseid
                ]),
                new \pix_icon('t/delete', 'Delete')
            )
        ])
    ];

    $row = array_merge(
        $rowfields,
        $rowactions
    );

    $table->add_data($row);
}

$table->finish_html();

echo $OUTPUT->footer();
