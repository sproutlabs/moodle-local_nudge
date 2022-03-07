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
 * Adds a link to edit nudge notifications from the courses and categories sidebar for the root course.
 *
 * @package     local_nudge
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 *
 * @var \core_config        $CFG
 * @var \admin_root         $ADMIN
 */

defined('MOODLE_INTERNAL') || die();

$ADMIN->add(
    'courses',
    new \admin_category('nudge', 'Nudge'),
    (isset($CFG->totara_version))
        ? 'configurecatalog'
        : 'restorecourse'
);

$ADMIN->add(
    'nudge',
    new \admin_externalpage(
        'configurenudgenotifications',
        new \lang_string('configurenudgenotifications', 'local_nudge'),
        new \moodle_url('/local/nudge/edit_notifications.php', ['model' => 'notifications']),
        'local/nudge:configurenudgenotifications'
    )
);

$ADMIN->add(
    'nudge',
    new \admin_externalpage(
        'configurenudgenotificationcontents',
        new \lang_string('configurenudgenotificationcontents', 'local_nudge'),
        new \moodle_url('/local/nudge/edit_notifications.php', ['model' => 'notificationcontents']),
        'local/nudge:configurenudgenotificationcontents'
    )
);
