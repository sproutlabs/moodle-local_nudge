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

use local_nudge\dml\nudge_notification_db;
use local_nudge\form\nudge_notification\delete;

// @codeCoverageIgnoreStart
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
// @codeCoverageIgnoreEnd

\admin_externalpage_setup('configurenudgenotifications');

$id = \required_param('id', PARAM_INT);

$manageurl = new \moodle_url('/local/nudge/manage_notifications.php');
$PAGE->set_url($manageurl);

$mform = new delete();
if ($mform->is_cancelled()) {
    \redirect(new \moodle_url($manageurl));
} else if ($deletedata = $mform->get_data()) {
    nudge_notification_db::delete(\intval($deletedata->id));
    \redirect($manageurl);
}

$nudgenotification = nudge_notification_db::get_by_id($id);
if ($nudgenotification === null) {
    throw new \invalid_parameter_exception(sprintf('Nudge Notification with id: %s was not found.', $id));
}

$idholder = new stdClass();
$idholder->id = $nudgenotification->id;
$idholder->title = $nudgenotification->title;

$mform->set_data($idholder);

echo $OUTPUT->header();

$mform->display();

echo $OUTPUT->footer();
