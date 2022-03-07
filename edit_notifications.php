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
 * Abstract sort of CRUD controller but a more transaction script approach.
 * This is by no means readable, sorry!
 *
 * @package     local_nudge
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 *
 * Since we have no class scope we can make these known to VSCODE.
 * @var \core_config        $CFG
 * @var \moodle_database    $DB
 * @var \moodle_page        $PAGE
 * @var \core_renderer      $OUTPUT
 */

// VSCODE's current pluginset doesn't support typehinted global so we have to type hint them in the local scope.
// phpcs:disable moodle.Commenting.InlineComment.TypeHintingMatch
// phpcs:disable moodle.Commenting.InlineComment.DocBlock

use core\output\notification;
use local_nudge\dml\nudge_notification_content_db;
use local_nudge\dml\nudge_notification_db;
use local_nudge\form\nudge_notification\edit as notification_edit_form;
use local_nudge\form\nudge_notification_content\edit as notification_content_edit_form;
use local_nudge\local\nudge_notification;
use local_nudge\local\nudge_notification_content;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

/**
 * @var array{
 *      'notifications': array{
 *          'model': class-string<nudge_notification>,
 *          'dml': class-string<nudge_notification_db>,
 *          'editform': class-string<notification_edit_form>,
 *          'columns': array{
 *              'data': array<string>,
 *              'headers': array<string>
 *          }
 *      },
 *      'notificationcontents': array{
 *          'model': class-string<nudge_notification_content>,
 *          'dml': class-string<nudge_notification_content_db>,
 *          'editform': class-string<notification_content_edit_form>,
 *          'columns': array{
 *              'data': array<string>,
 *              'headers': array<string>
 *          }
 *      }
 * }
 */
$paramtomodel = [
    'notifications' => [
        'model' => nudge_notification::class,
        'dml' => nudge_notification_db::class,
        'editform' => notification_edit_form::class,
        'columns' => [
            'data' => ['id', 'title', 'actions'],
            'headers' => ['ID', 'Title', 'Actions']
        ]
    ],
    'notificationcontents' => [
        'model' => nudge_notification_content::class,
        'dml' => nudge_notification_content_db::class,
        'editform' => notification_content_edit_form::class,
        'columns' => [
            'data' => ['notificationtitle', 'lang', 'subject', 'body', 'actions'],
            'headers' => ['Linked Notification', 'Language', 'Subject', 'Body', 'Actions']
        ]
    ]
];

$model = required_param('model', \PARAM_ALPHA);

/** @var int $confirm */
$confirm    = optional_param('confirm', 0,  \PARAM_INT);
/** @var int $delete */
$delete     = optional_param('delete',  0,  \PARAM_INT);
/** @var int $edited */
$edited     = optional_param('edited',  0,  \PARAM_INT);
/** @var int $edit */
$edit       = optional_param('edit',    0,  \PARAM_INT);
/** @var int $add */
$add        = optional_param('add',     0,  \PARAM_INT);

// TODO Forbidden / Unknown model.
if (!\in_array($model, array_keys($paramtomodel))) {
    die('Unknown Model');
}

/**
 * Type is now narrowed to:
 * @var 'notifications'|'notificationcontents' $model
 * This offers errors when a call like $paramtomodel[$model]['dml']::xyz() doesn't exist on either model.
 *
 * Shorthand alias.
 */
$currentmodel = $paramtomodel[$model];

$baseediturl = new moodle_url('/local/nudge/edit_notifications.php', [
    'sesskey' => \sesskey(),
    'model' => $model
]);

// Require a login for this course for the system.
\admin_externalpage_setup("configurenudge{$model}");

// Require the permissions to edit nudge notifications.
$systemcontext = \context_system::instance();
\require_capability("local/nudge:configurenudge{$model}", $systemcontext);

$manageurl = new \moodle_url('/local/nudge/edit_notifications.php');
$PAGE->set_url($manageurl);

// Check sesskey if needed.
if (
    ($delete || $edit || $add) &&
    !\confirm_sesskey()
) {
    print_error('confirmsesskeybad', 'error');
    die();
}

// We want to delete a notification.
if ($delete) {
    $delete = \intval($delete);

    $notificationtodelete = $currentmodel['dml']::get_by_id($delete);

    if (!$notificationtodelete) {
        print_error('error:nudgenotificationdoesntexist');
    }

    // If we haven't confirmed yet.
    if (!$confirm) {
        $confirmurl = clone $baseediturl;
        $confirmurl->params([
            'delete' => $delete,
            'confirm' => 1,
        ]);
        $cancelurl = clone $baseediturl;
        $cancelurl->remove_all_params();

        echo $OUTPUT->header();
        echo $OUTPUT->confirm(
            get_string(
                'deletenudgenotificationconfirm',
                'local_nudge',
                format_string($notificationtodelete->title)
            ),
            $confirmurl->out(),
            $cancelurl
        );
        echo $OUTPUT->footer();
        die;
    }

    $currentmodel['dml']::delete($delete);

    // TODO totara / moodle cleanup.
    if (isset($CFG->totara_version)) {
        \core\notification::success(sprintf('Deleted %s with ID: %s', $currentmodel['model']::SINGULAR_NAME, $delete));
    } else {
        $OUTPUT->notification(
            sprintf(
                'Deleted %s with ID: %s',
                $currentmodel['model']::SINGULAR_NAME,
                $delete
            ),
            notification::NOTIFY_SUCCESS
        );
    }

    redirect($baseediturl);
}

// We want to edit a model.
if ($edit) {
    $edit = intval($edit);

    $nudgenotification = $currentmodel['dml']::get_by_id($edit);

    $nudgeeditformurl = clone $baseediturl;
    $nudgeeditformurl->params([
        'edited' => $nudgenotification->id,
        'edit' => $nudgenotification->id
    ]);

    $mform = new $currentmodel['editform']($nudgeeditformurl);

    if (!$edited) {
        echo $OUTPUT->header();
        $mform->set_data($nudgenotification);
        $mform->display();
        echo $OUTPUT->footer();
        die;
    }

    if ($mform->is_cancelled()) {
        \redirect($baseediturl);
    } else if ($nudgenotification = $mform->get_data()) {
        $currentmodel['dml']::save($nudgenotification);
        \redirect($baseediturl);
    }
}

// TODO: do something better (cancel still creates).
// We want to add a new notification.
if ($add) {
    $add = \intval($add);

    $newnotification = new $currentmodel['model']();
    $newnotificationid = $currentmodel['dml']::save($newnotification);

    $nudgeaddformurl = clone $baseediturl;
    $nudgeaddformurl->param('edit', $newnotificationid);

    redirect($nudgeaddformurl);
}

// We want to manage the notifications, no actions have been taken.
echo $OUTPUT->header();

$addnewurl = clone $baseediturl;
$addnewurl->param('add', '1');
// TODO lang string "add a notification".
echo $OUTPUT->single_button($addnewurl, 'Add a notification', 'get');

$table = new \flexible_table('GridFieldOnABudget');
$table->define_baseurl(new \moodle_url('/local/nudge/edit_notifications'));
$table->define_columns($currentmodel['columns']['data']);
$table->define_headers($currentmodel['columns']['headers']);
$table->sortable(false);
$table->setup();

$selectsql = <<<SQL
    SELECT
        *
    FROM
        {{$currentmodel['dml']::$table}}
SQL;

$modelstodisplay = $currentmodel['dml']::get_all_sql($selectsql);

foreach ($modelstodisplay as $modeltodisplay) {

    $rowfields = $modeltodisplay->get_summary_fields();

    /** @var callable */
    $formatstring = '\format_string';
    \array_walk($rowfields, $formatstring);

    // Actions.
    $editurl = clone $baseediturl;
    $editurl->param('edit', (string)$modeltodisplay->id);

    $deleteurl = clone $baseediturl;
    $deleteurl->param('delete', (string)$modeltodisplay->id);

    $rowactions = [
        \implode('', [
            $OUTPUT->action_icon(
                $editurl,
                // TODO Totara icon only fix for moodle and get_string for 'edit'.
                new \pix_icon('t/edit', 'Edit')
            ),
            $OUTPUT->action_icon(
                $deleteurl,
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
