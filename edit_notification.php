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
use local_nudge\dml\nudge_notification_content_db;
use local_nudge\dml\nudge_notification_db;
use local_nudge\form\nudge_notification\edit;
use local_nudge\local\nudge_notification;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

\admin_externalpage_setup('configurenudgenotifications');

$notificationid = \required_param('id', \PARAM_INT);

$manageurl = new \moodle_url('/local/nudge/manage_notifications.php');
$PAGE->set_url($manageurl);

// Using this isn't great.
$mform = new edit($notificationid);

if ($mform->is_cancelled()) {
    \redirect($manageurl);
} else if ($editdata = $mform->get_data()) {
    if ($editdata === null) {
        \redirect(
            $manageurl,
            'Unable to save notification',
            null,
            notification::NOTIFY_ERROR
        );
    }

    $notification = nudge_notification_db::create_or_refresh($editdata->notification);
    $notificationid = $notification->id;

    foreach ($editdata->notificationcontents as $notificationcontent) {
        $notificationcontent->nudgenotificationid = $notificationid;
        nudge_notification_content_db::save($notificationcontent);
    }

    \redirect(
        $manageurl,
        "Edited notification '{$notification->title}' successfully",
        null,
        notification::NOTIFY_SUCCESS
    );
}

if ($notificationid === 0) {
    $nudgenotification = new nudge_notification();
} else {
    $nudgenotification = nudge_notification_db::get_by_id($notificationid);
    if ($nudgenotification === null) {
        throw new moodle_exception('nudgenotificationdoesntexist', 'local_nudge', '', $notificationid);
    }
}

$mform->set_data($nudgenotification->as_notification_form());

echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();
