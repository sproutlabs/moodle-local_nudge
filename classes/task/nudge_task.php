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

namespace local_nudge\task;

use stdClass;
use completion_info;
use context_course;
use core\task\scheduled_task;
use local_nudge\dml\nudge_db;
use local_nudge\dml\nudge_user_db;
use local_nudge\local\nudge;
use local_nudge\local\nudge_user;

defined('MOODLE_INTERNAL') || die();

/** @var \core_config $CFG */
global $CFG;
require_once($CFG->libdir . '/completionlib.php');

/**
 * @package     local_nudge\task
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 *
 * @todo Allthough nudge has a really nice DML thanks to a quick almost copy n paste of Catalyst's auth_outage
 * it might be a bit too expensive to use it during the cron so a switch to SQL here only might be a good idea.
 *
 * This is a very pref expensive plugin so a seperate cron worker for this task
 * should be recommended in the readme for serious use of the plugin.
 */
class nudge_task extends scheduled_task {
    private const USER_COURSE_ENROLLMENT_TIME_SQL = <<<SQL
    SELECT
        timestart
    FROM
        {user_enrolments} AS enrolment
        LEFT JOIN {enrol} AS enrol ON enrolment.enrolid = enrol.id
    WHERE
        enrolment.userid = :userid
        AND enrol.courseid = :courseid
    ORDER BY
        timestart ASC
    LIMIT 1
    SQL;

    /**
     * @return string
     */
    public function get_name() {
        return \get_string('crontask', 'local_nudge');
    }

    /**
     * @return void
     */
    public function execute() {
        foreach (nudge_db::get_enabled() as $enabledinstance):
            switch ($enabledinstance->remindertype):
                // Manually defined time to reminder incomplete users.
                case (nudge::REMINDER_DATE_INPUT_FIXED):
                    // NO-OP
                    if ($enabledinstance->remindertypefixeddate < time()) {
                        break;
                    }

                    $this->send_emails_for_incomplete_users($enabledinstance);

                    $enabledinstance->isenabled = false;
                    nudge_db::save($enabledinstance);

                    break;
                // If we want to reminder people of an upcoming end of course.
                case (nudge::REMINDER_DATE_RELATIVE_COURSE_END):
                    /** @var \core\entity\course */
                    $nudgescourse = $enabledinstance->get_course();
                    $timetoremindofendofcourse = $nudgescourse->enddate - $enabledinstance->remindertypeperiod;

                    // NO-OP
                    if ($timetoremindofendofcourse < \time()) {
                        break;
                    }

                    $this->send_emails_for_incomplete_users($enabledinstance);

                    $enabledinstance->isenabled = false;
                    nudge_db::save($enabledinstance);

                    break;
                // We want to remind people every x period after enrollment and before course enddate.
                case (nudge::REMINDER_DATE_RELATIVE_ENROLLMENT):
                    // This one is more expensive and complicated. It will need a load of optimisation before production
                    // but for a some wireframing now its fine.
                    $this->handle_recurring($enabledinstance);
                    break;

                default:
                    continue 2;
            endswitch;
        endforeach;
    }

    /**
     * @param nudge $nudge
     */
    private function handle_recurring($nudge) {
        $nudgescourse = $nudge->get_course();

        // TODO handle: \enrol_get_enrolment_end()

        // No more need to recurr the course has ended.
        if ($nudgescourse->enddate < \time()) {
            $nudge->isenabled = false;
            nudge_db::save($nudge);

            nudge_user_db::delete_all(['nudgeid' => $nudge->id]);
            return;
        }

        foreach ($this->get_incomplete_users_for_nudge($nudge) as $incompleteuser) {
            $previoustiming = nudge_user_db::get_filtered([
                'nudgeid' => $nudge->id,
                'userid' => $incompleteuser->id
            ]);

            // TODO: Extract into fn.
            if ($previoustiming === null) {
                $userstartdate = $this->get_user_enroltime_in_course(
                    $incompleteuser->id,
                    $nudgescourse->id
                );

                // Save a record for the future.
                $previoustiming = new nudge_user([
                    'nudgeid' => $nudge->id,
                    'userid' => $incompleteuser->id,
                    'recurrancetime' => ($userstartdate + $nudge->remindertypeperiod)
                ]);

                // Refresh with the ID so duplicates are not created.
                // Totara's persistant is patched for this by default.
                // I didn't know of it until mid-development.
                // Turns out because of the difference between totara and moodle a custom solution is good.
                $previousid = nudge_user_db::save($previoustiming);
                $previoustiming = nudge_user_db::get_by_id($previousid);
            }

            // NO-OP for this user.
            if ($previoustiming->recurrancetime > \time()) {
                continue;
            }

            // TODO: Handle results - maybe don't update next recurr time if it fails.
            $nudge->trigger($incompleteuser);

            // Setup the next period for this user.
            $previoustiming->recurrancetime = \time() + $nudge->remindertypeperiod;
            nudge_user_db::save($previoustiming);
        }
    }

    /**
     * Sends emails for incomplete users in an instance of {@see nudge}.
     *
     * @param nudge $nudge
     * @return void
     */
    private function send_emails_for_incomplete_users($nudge) {
        foreach ($this->get_incomplete_users_for_nudge($nudge) as $incompleteuser) {
            // TODO: handle error.
            $nudge->trigger($incompleteuser);
        }
    }

    /**
     * Gets a list of incomplete users for a given {@see nudge} instance.
     *
     * This type hint relies on the the Totara course entity for hinting of stdClass properties.
     * @return \Generator<\core\entity\user|stdClass>
     */
    private function get_incomplete_users_for_nudge(nudge $nudge) {
        $nudgescourse = $nudge->get_course();
        $nudgescontext = context_course::instance($nudge->courseid);

        /**
         * @var array<\core\entity\user|stdClass> $enrolledusers
         */
        $enrolledusers = \get_enrolled_users($nudgescontext);

        // Find incomplete users for this instance of nudge.
        foreach ($enrolledusers as $enrolleduser) {
            $userhasnotcompletedcourse = !(
                (new completion_info($nudgescourse))
                    ->is_course_complete($enrolleduser->id)
            );

            if ($userhasnotcompletedcourse) {
                yield $enrolleduser;
            }
        }
    }

    /**
     * Gets the enroltime for a user within a course.
     *
     * Couldn't find core API for this.
     *
     * If the user has multiple enrollment instances it picks the enrollment instance with the lowest timestarted
     * AKA the enrollment instance which first introduced the user to the course.
     *
     * @todo There seems to be a bug with something related in get_field_sql. works for now.
     *
     * @param int $userid
     * @param int $courseid
     * @return int
     */
    private function get_user_enroltime_in_course(int $userid, int $courseid): ?int {
        /** @var \moodle_database $DB */
        global $DB;

        return $DB->get_field_sql(self::USER_COURSE_ENROLLMENT_TIME_SQL, [
            'userid' => $userid,
            'courseid' => $courseid
        ]) ?: null;
    }
}
