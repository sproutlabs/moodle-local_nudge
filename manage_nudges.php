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

// @codeCoverageIgnoreStart
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');
// @codeCoverageIgnoreEnd

$courseid = \required_param('courseid', \PARAM_INT);

if ($courseid === 1) {
    throw new moodle_exception(
        'cantmanagesitenudges',
        'local_nudge'
    );
}

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
echo $OUTPUT->single_button(
    new moodle_url('/local/nudge/manage_notifications.php'),
    get_string('manage_nudge_notificationslink', 'local_nudge'),
    'get',
);

$table = new \flexible_table('nudge_table');
$table->define_baseurl(new moodle_url('/local/nudge/manage_nudges.php', ['courseid' => $courseid]));
$table->define_columns([
    'title',
    'status',
    'learnerreminder',
    'managerreminder',
    'type',
    'actions',
]);
$table->define_headers([
    get_string('manage_nudge_col_title', 'local_nudge'),
    get_string('manage_nudge_col_status', 'local_nudge'),
    get_string('manage_nudge_col_learnerreminder', 'local_nudge'),
    get_string('manage_nudge_col_managerreminder', 'local_nudge'),
    get_string('manage_nudge_col_type', 'local_nudge'),
    get_string('manage_nudge_col_actions', 'local_nudge'),
]);
$table->sortable(true, 'title', SORT_ASC);
$table->set_control_variables([\TABLE_VAR_SORT => 'ssort']);
$table->no_sorting('type');
$table->no_sorting('status');
$table->no_sorting('learnerreminder');
$table->no_sorting('managerreminder');
$table->no_sorting('actions');
$table->setup();

/** @var string Not SQL.. Poor type hint */
$tablesort = $table->get_sql_sort();
if (\strlen($tablesort) && \substr($tablesort, 0, 5) === 'title') {
    $sqlsort = 'ORDER BY ' . $tablesort;
} else {
    $sqlsort = '';
}

$nudgetable = nudge_db::$table;
$selectsql = <<<SQL
    SELECT
        *
    FROM
        {{$nudgetable}} as nudge
    WHERE
        nudge.courseid = :courseid
    {$sqlsort}
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
