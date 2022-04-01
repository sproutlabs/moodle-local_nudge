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

use local_nudge\dml\nudge_notification_db;

use function get_string;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

// Require a login for this course for the system.
\admin_externalpage_setup("configurenudgenotifications");

// Require the permissions to edit nudge notifications.
$systemcontext = \context_system::instance();
\require_capability("local/nudge:configurenudgenotifications", $systemcontext);

echo $OUTPUT->header();

echo $OUTPUT->single_button(
    new moodle_url(
        '/local/nudge/edit_notification.php',
        [
            'id' => 0
        ]
    ),
    get_string('manage_notification_add', 'local_nudge'),
    'get'
);

$table = new \flexible_table('nudge_notification_table');
$table->define_baseurl(new \moodle_url('/local/nudge/manage_notifications.php'));
$table->define_columns([
    'id',
    'title',
    'count',
    'actions',
]);
$table->define_headers([
    'ID',
    'Title',
    'Linked Translation Count',
    'Actions',
]);
$table->sortable(false);
$table->setup();

$notificationtable = nudge_notification_db::$table;
$selectsql = <<<SQL
    SELECT
        *
    FROM
        {{$notificationtable}}
SQL;

$notificationstomanage = nudge_notification_db::get_all_sql($selectsql);

foreach ($notificationstomanage as $notification) {

    $rowfields = $notification->get_summary_fields();

    /** @var callable */
    $formatstring = '\format_string';
    \array_walk($rowfields, $formatstring);

    $rowactions = [
        \implode('', [
            $OUTPUT->action_icon(
                new \moodle_url('/local/nudge/edit_notification.php', [
                    'id' => $notification->id
                ]),
                new \pix_icon('t/edit', 'Edit')
            ),
            $OUTPUT->action_icon(
                new \moodle_url('/local/nudge/delete_notification.php', [
                    'id' => $notification->id
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
