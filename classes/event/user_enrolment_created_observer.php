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

namespace local_nudge\event;

use container_workspace\workspace;
use core\event\user_enrolment_created;
use core_container\factory;
use local_nudge\dml\nudge_db;
use local_nudge\local\nudge;

/**
 * @package     local_nudge\event
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

class user_enrolment_created_observer {
    /**
     * Trigger all nudges for this container and user that should be triggered on workspace join.
     *
     * @param user_enrolment_created $event
     */
    public static function trigger_workspace_joined_nudges(user_enrolment_created $event): void {
        /** @var \moodle_database $DB */
        global $DB;

        if (!class_exists('container_workspace\workspace')) {
            return;
        }

        /** @var workspace $workspace */
        $workspace = factory::from_id($event->courseid);
        if (!$workspace->is_typeof(workspace::get_type())) {
            return;
        }

        /** @var nudge[] */
        $nudgestotrigger = nudge_db::get_all_filtered([
            'remindertype' => nudge::REMINDER_DATE_USER_ENROLMENT,
            'courseid' => $event->courseid
        ]);

        /** @var \core\entity\user|\stdClass */
        $user = $DB->get_record('user', ['id' => $event->relateduserid], '*', \MUST_EXIST);

        foreach ($nudgestotrigger as $nudge) {
            $nudge->trigger($user);
        }
    }
}
