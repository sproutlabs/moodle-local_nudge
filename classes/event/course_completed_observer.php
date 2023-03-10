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

use core\event\course_completed;
use local_nudge\dml\nudge_db;
use local_nudge\local\nudge;

/**
 * @package     local_nudge\event
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

class course_completed_observer {
    /**
     * Trigger all nudges for this course and user that should be triggered on course completion.
     *
     * @param course_completed $event
     */
    public static function trigger_course_completion_nudges(course_completed $event): void {
        /** @var \moodle_database $DB */
        global $DB;

        /** @var nudge[] */
        $nudgestotrigger = nudge_db::get_all_filtered([
            'remindertype' => nudge::REMINDER_DATE_COURSE_COMPLETION,
            'courseid' => $event->courseid
        ]);

        /** @var \core\entity\user|\stdClass */
        $user = $DB->get_record('user', ['id' => $event->relateduserid], '*', \MUST_EXIST);

        foreach ($nudgestotrigger as $nudge) {
            $nudge->trigger($user);
        }
    }
}
