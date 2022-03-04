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
 * @package     local_nudge\task
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

namespace local_nudge\task;

require_once($CFG->libdir . '/completionlib.php');

use completion_info;
use context_course;
use core\task\scheduled_task;
use core_user;
use local_nudge\dml\nudge_db;
use local_nudge\dml\nudge_notification_db;

defined('MOODLE_INTERNAL') || die();

/**
 * local_nudge\task\cron_task 
 * --- 
 * @package     local_nudge\task
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 * 
 * @todo Allthough nudge has a really nice DML thanks to a quick almost copy n paste of Catalyst's auth_outage
 * it might be a bit too expensive to use it during the cron so a switch to SQL here only might be a good idea.
 * 
 * This is a very pref expensive plugin so a seperate cron worker for this task should be recommended in the readme for serious use of the plugin.
 */
class cron_task extends scheduled_task
{
    // PROCESS LIKE THIS SWITCH:
    // ```-------------------```
    // IF the reminder type is fixed
    // run the notification then toggle isenabled to false
    // 
    // If the reminder type is prior to course end
    // run the notification when the `(course enddate - remindertypeperiod) > time()`
    // then toggle isenabled to false
    // 
    // If the reminder type is post enrollment
    // run the notification when `(user enrollment date + remindertypeperiod) > time()`
    // once course end is reached then isenabled should be toggled to false.
    // ```-------------------```

    // IDEA: nudge should store the last user that modified it's ID and notify them each time isenabled is toggled via an event.

    /**
     * @return string
     */
    public function get_name()
    {
        return \get_string('crontask', 'local_nudge');
    }

    /**
     * @return void
     */
    public function execute()
    {   
        foreach (nudge_db::get_enabled() as $enabledInstance) {
            /**
             * This type hint relies on the the Totara user entity for hinting of stdClass properties.
             * @var array<\core\entity\user>
             */
            $incomplete_users = [];

            /**
             * This type hint relies on the the Totara course entity for hinting of stdClass properties.
             * @var \core\entity\course
             */
            $course = \get_course($enabledInstance->courseid);
            $context = context_course::instance($enabledInstance->courseid);

            /**
             * @var array<\core\entity\user> $enrolled_users
             */
            $enrolled_users = \get_enrolled_users($context);

            // Find incomplete users for this instance of nudge.
            foreach ($enrolled_users as $enrolled_user) {
                $course_completion_info = new completion_info($course);
                $user_has_not_completed_course = !$course_completion_info->is_course_complete($enrolled_user->id);

                if ($user_has_not_completed_course) {
                    $incomplete_users[] = $enrolled_user;
                }
            }

            // Email incomplete users for this instance of nudge.
            foreach ($incomplete_users as $incomplete_user) {
                $content = $this->get_email_template($enabledInstance, $course, $incomplete_user);
                
                \email_to_user(
                    $incomplete_user,
                    core_user::get_noreply_user(),
                    "Course nudge email from course with ID: {$enabledInstance->courseid}",
                    '',
                    $content
                );
            }
        }
    }

    /**
     * @param nudge $nudge
     * @param \core\entity\course $course
     * @param \core\entity\user $user
     * @return string
     */
    private function get_email_template($nudge, $course, $user): string
    {
        if ($nudge->linkedlearnernotificationid === 0) {
            return 'TODO: xyz';
        }
        /** @var \local_nudge\local\nudge_notification */
        $notification = nudge_notification_db::get_by_id(\intval($nudge->linkedlearnernotificationid));
        $content = $notification->body;

        $content = \str_replace('[user_fullname]', $user->firstname, $content);
        $content = \str_replace('[course_fullname]', $course->fullname, $content);
        $content = \str_replace('[course_link]', "http://192.168.1.111:89/server/course/view.php?id={$course->id}", $content);
        $content = \str_replace('[educator_email]', $user->email, $content);

        return $content;
    }
}
