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
 * @todo SESS key shouldn't show in browser omnibar I'm doing something wrong.
 * Base page to manipulate {@see nudge_notification}s.
 * Don't know what moodle calls this but its sort of CRUD controller without routing :D!
 * 
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
use local_nudge\dml\nudge_notification_db;
use local_nudge\form\nudge_notification\edit;
use local_nudge\local\nudge_notification;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$confirm    = optional_param('confirm', 0,  \PARAM_INT);
$delete     = optional_param('delete',  0,  \PARAM_INT);
$edited     = optional_param('edited',  0,  \PARAM_INT);
$edit       = optional_param('edit',    0,  \PARAM_INT);
$add        = optional_param('add',     0,  \PARAM_INT);

// Require a login for this course for the system.
\admin_externalpage_setup('configurenudgenotifications');

// Require the permissions to edit nudge notifications.
$systemcontext = \context_system::instance();
\require_capability('local/nudge:configurenudgenotifications', $systemcontext);

$base_edit_url = new moodle_url('/local/nudge/edit_notifications.php', [
    'sesskey' => \sesskey()
]);

$manageurl = new \moodle_url('/local/nudge/edit_notifications.php');
$PAGE->set_url($manageurl);

// Check sesskey if needed.
if (
    ($delete || $edit || $add) &&
    !\confirm_sesskey()
) {
    print_error('confirmsesskeybad', 'error');
}

// We want to delete a notification.
if ($delete) {
    $delete = \intval($delete);

    $notification_to_delete = nudge_notification_db::get_by_id($delete);

    if (!$notification_to_delete) {
        print_error('error:nudgenotificationdoesntexist');
    }

    // If we haven't confirmed yet.
    if (!$confirm) {
        $confirm_url = clone $base_edit_url;
        $confirm_url->params([
            'delete' => $delete,
            'confirm' => 1,
        ]);
        $cancel_url = clone $base_edit_url;
        $cancel_url->remove_all_params();

        echo $OUTPUT->header();
        echo $OUTPUT->confirm(
            get_string(
                'deletenudgenotificationconfirm',
                'local_nudge',
                format_string($notification_to_delete->title)
            ),
            $confirm_url->out(),
            $cancel_url
        );
        echo $OUTPUT->footer();
        die;
    }

    nudge_notification_db::delete($delete);

    // TODO totara / moodle cleanup:
    if (isset($CFG->totara_version)) {
        \core\notification::success(sprintf('Deleted nudge notification with ID: %s', $delete));
    } else {
        $OUTPUT->notification(sprintf('Deleted nudge notification with ID: %s', $delete), notification::NOTIFY_SUCCESS);
    }

    redirect($base_edit_url);
}

// We want to edit a notification.
if ($edit) {
    $edit = intval($edit);

    $nudge_notification = nudge_notification_db::get_by_id($edit);

    $nudge_edit_form_url = clone $base_edit_url;
    $nudge_edit_form_url->params([
        'edited' => $nudge_notification->id,
        'edit' => $nudge_notification->id
    ]);

    $mform = new edit($nudge_edit_form_url);

    if (!$edited) {
        echo $OUTPUT->header();
        $mform->set_data($nudge_notification);
        $mform->display();
        echo $OUTPUT->footer();
        die;
    }

    if ($mform->is_cancelled()) {
        \redirect($base_edit_url);
    } else if ($nudge_notification = $mform->get_data()) {
        nudge_notification_db::save($nudge_notification);
        \redirect($base_edit_url);
    }
}

// TODO: do something better (cancel still creates).
// We want to add a new notification.
if ($add) {
    $add = \intval($add);

    $new_notification = new nudge_notification();
    $new_notification_id = nudge_notification_db::save($new_notification);

    $nudge_add_form_url = clone $base_edit_url;
    $nudge_add_form_url->param('edit', $new_notification_id);

    redirect($nudge_add_form_url);
}


// We want to manage the notifications, no actions have been taken.
echo $OUTPUT->header();

/// ========= BEGIN NUDGE_NOTIFICATION ========= ///
$add_new_url = clone $base_edit_url;
$add_new_url->param('add', 1);
echo $OUTPUT->single_button($add_new_url, 'Add a notification', 'get');

$table = new \flexible_table('Testing');
$table->define_baseurl(new \moodle_url('/local/nudge/edit_notifications'));
$table->define_columns(['id', 'title', 'actions']);
$table->define_headers(['ID', 'Title', 'Actions']);
$table->sortable(false);
$table->setup();

$select_nudgenotifications_sql = <<<SQL
    SELECT
        *
    FROM
        {nudge_notification}
SQL;

/**
 * @var array<int, nudge_notification>
 */
$notifications = $DB->get_records_sql($select_nudgenotifications_sql);

foreach ($notifications as $notification) {

    $row_fields = [
        $notification->id,
        $notification->title
    ];

    \array_walk($row_fields, 'format_string');

    // Actions.
    $edit_url = clone $base_edit_url;
    $edit_url->param('edit', $notification->id);

    $delete_url = clone $base_edit_url;
    $delete_url->param('delete', $notification->id);

    $rowActions = [
        \implode('', [
            $OUTPUT->action_icon(
                $edit_url,
                // TODO Totara icon only fix for moodle and get_string for 'edit'
                new \pix_icon('t/edit', 'Edit')
            ),
            $OUTPUT->action_icon(
                $delete_url,
                new \pix_icon('t/delete', 'Delete')
            )
        ])
    ];

    $row = array_merge(
        $row_fields,
        $rowActions
    );

    $table->add_data($row);
}

$table->finish_html();
/// ========= END NUDGE_NOTIFICATION ========= ///

echo $OUTPUT->footer();
