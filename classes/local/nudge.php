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

namespace local_nudge\local;

use core\message\message;
use core_user;
use local_nudge\dml\nudge_notification_db;
use local_nudge\local\nudge_notification;
use stdClass;
use totara_core\relationship\relationship;

/**
 * @package     local_nudge\local
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @copyright   GNU GPL v3 or later
 */
class nudge extends abstract_nudge_entity {

    // BEGIN ENUM - REMINDER DATE    ////////////////////
    /**
     * This Nudge instance's reminder timing is a fixed date selected when setting up the reminder.
     */
    public const REMINDER_DATE_INPUT_FIXED = 'fixed';

    /**
     * This Nudge instance's reminder timing is based on the user's date of enrollment.
     */
    public const REMINDER_DATE_RELATIVE_ENROLLMENT = 'enrollment';

    /**
     * This Nudge instance's reminder timing is infered from the course's end date.
     */
    public const REMINDER_DATE_RELATIVE_COURSE_END = 'courseend';
    // END ENUM - REMINDER DATE    ////////////////////

    // BEGIN ENUM - REMINDER RECIPIENT    ////////////////////
    /**
     * This Nudge instance's recipient will be only the learner.
     */
    public const REMINDER_RECIPIENT_LEARNER = 'learner';

    /**
     * This Nudge instance's recipient will be only the learner's managers.
     */
    public const REMINDER_RECIPIENT_MANAGERS = 'managers';

    /**
     * This Nudge instance's will have both the learner and their managers as the recipients.
     */
    public const REMINDER_RECIPIENT_BOTH = 'both';
    // END ENUM - REMINDER RECIPIENT    ////////////////////

    /** {@inheritDoc} */
    public const DEFAULTS = [
        'isenabled' => 0,
        'reminderrecipient' => self::REMINDER_RECIPIENT_LEARNER,
        'remindertype' => self::REMINDER_DATE_RELATIVE_COURSE_END
    ];

    /**
     * {@see nudge::hydrate_notification_template()}
     *
     * @var array<string>
     */
    public const TEMPLATE_VARIABLES = [
        '{user_firstname}',
        '{user_lastname}',
        '{course_fullname}',
        '{course_shortname}',
        '{sender_firstname}',
        '{sender_lastname}',
        '{notification_name}',
    ];

    /**
     * @var int|null The id of the Course linked to this nudge entity.
     */
    public $courseid = null;

    /**
     * @var int|null The has one for a {@see nudge_notification} that will be sent to the Learner.
     * 0 (no foreign reference) will use the default language string.
     */
    public $linkedlearnernotificationid = null;

    /**
     * @var int|null The has one for a {@see nudge_notification} that will be sent to the Manager.
     * 0 (no foreign reference) will use the default language string.
     */
    public $linkedmanagernotificationid = null;

    /**
     * @var bool|null Is this instance of nudge enabled (Is nudge enabled for this course)?
     */
    public $isenabled = null;

    /**
     * @var int|null The timestamp this nudge instance was last modified at.
     */
    public $lastmodified = null;

    /**
     * @var string|null The the reminder recipients of this nudge.
     */
    public $reminderrecipient = null;

    /**
     * @var string|null The the reminder type of this nudge.
     */
    public $remindertype = null;

    /**
     * The fixed reminder date to nudge at if {@see self::$remindertype} == {@see self::REMINDER_DATE_INPUT_FIXED}.
     *
     * @var string|null Timestamp.
     */
    public $remindertypefixeddate = null;

    /**
     * Time in seconds representing either:
     * ---
     * the duration prior to course end date to nudge at if
     * ```
     * {@see self::$remindertype} == {@see self::REMINDER_DATE_RELATIVE_COURSE_END}
     * ```
     * OR
     * the period of time post learner enrollment to repeat nudges if
     * ```
     * {@see self::$remindertype} == {@see self::REMINDER_DATE_RELATIVE_ENROLLMENT}.
     * ```
     *
     * @todo Validate that the the duration field cannot exceed MYSQL bigint on the form and below constructor.
     *
     * @var int|null Time in seconds.
     */
    public $remindertypeperiod = null;

    /**
     * @return nudge_notification|null
     */
    public function get_learner_notification() {
        if ($this->linkedlearnernotificationid == 0) {
            return null;
        }
        // TODO: casting.
        return nudge_notification_db::get_by_id(\intval($this->linkedlearnernotificationid));
    }

    /**
     * @return nudge_notification|null
     */
    public function get_manager_notification() {
        if ($this->linkedmanagernotificationid == 0) {
            return null;
        }
        // TODO: casting.
        return nudge_notification_db::get_by_id(\intval($this->linkedmanagernotificationid));
    }

    /**
     * @return \core\entity\course|\stdClass
     */
    public function get_course() {
        return \get_course($this->courseid);
    }

    // TODO these next few functions don't belong here.

    /**
     * @param \core\entity\user $user
     */
    public function trigger($user): void {
        /** @var \core_config */
        global $CFG;
        switch ($this->reminderrecipient) {
            case (self::REMINDER_RECIPIENT_BOTH):
                \message_send($this->get_email_message($user));

                foreach (self::totara_get_managers_for_user($user) as $manager) {
                    \message_send($this->get_email_message($user, $manager));
                }

                break;

            case (self::REMINDER_RECIPIENT_LEARNER):
                \message_send($this->get_email_message($user));
                break;

            case (self::REMINDER_RECIPIENT_MANAGERS):
                // TODO: manager on moodle
                if (!isset($CFG->totara_version)) {
                    throw new \moodle_exception('manageronmoodle', 'local_nudge');

                    \message_send($this->get_email_message($user, null));
                    break;
                }

                foreach (self::totara_get_managers_for_user($user) as $manager) {
                    \message_send($this->get_email_message($user, $manager));
                }

                break;
            default:
                // Weird.
                break;
        }
    }

    /**
     * @param \core\entity\user $user
     * @return array<\core\entity\user>
     */
    public static function totara_get_managers_for_user($user): array {
        /**
         * @var array<\core\entity\user> $allmanagers
         */
        $allmanagers = [];

        $managerrelation = relationship::load_by_idnumber('manager');
        $usermanagerrelations = $managerrelation->get_users(['user_id' => $user->id], \context_system::instance());

        foreach ($usermanagerrelations as $managerdto) {
            $allmanagers[] = core_user::get_user($managerdto->get_user_id());
        }

        return $allmanagers;
    }

    /**
     * Gets a templated {@see message} for this instance of nudge.
     *
     * @param \core\entity\user $user The user or a manager.
     */
    public function get_email_message($user, $manager = null): message {
        /** @var \moodle_database $DB */
        global $DB;

        // Grab some context for the template.
        if ($manager === null) {
            $notification = $this->get_learner_notification();
        } else {
            $notification = $this->get_manager_notification();
        }

        $notificationcontents = $notification->get_contents($user->lang);
        $notificationcontent = array_pop($notificationcontents);
        /** @var \core\entity\user|false */
        $userfrom = $DB->get_record('user', ['id' => $notification->userfromid]);
        $course = $this->get_course();

        // Passing a whole bunch of values through to avoid new queries.
        $subject = $this->hydrate_notification_template(
            $notificationcontent->subject,
            $user,
            $course,
            $userfrom,
            $notification
        );

        $body = $this->hydrate_notification_template(
            $notificationcontent->body,
            $user,
            $course,
            $userfrom,
            $notification
        );

        $message = new message();
        $message->component = 'local_nudge';
        $message->name = ($manager) ? 'manageremail' : 'learneremail';
        $message->userfrom = $userfrom;
        $message->userto = ($manager === null) ? $user : $manager;
        $message->subject = $subject;
        $message->fullmessageformat = \FORMAT_HTML;
        $message->fullmessagehtml = $body;
        $message->notification = 1;
        $message->courseid = $course->id;
        $message->contexturl = new \moodle_url('/course/view.php', ['id' => $course->id]);
        $message->contexturlname = 'Course Link';

        $content = ['*' => [
            'header' => <<<HTML
                <h1>{$notification->title}</h1>
            HTML,
        ]];
        $message->set_additional_content('email', $content);

        return $message;
    }

    /**
     * @param \core\entity\user $user
     * @param \core\entity\course $course
     * @param \core\entity\user $userfrom
     */
    public function hydrate_notification_template(
        string $contenttotemplate,
        $user,
        $course,
        $userfrom,
        nudge_notification $notification
    ): string {
        $templatevars = self::TEMPLATE_VARIABLES;

        $templatevars['{user_firstname}'] = $user->firstname;
        $templatevars['{user_lastname}'] = $user->lastname;
        $templatevars['{course_fullname}'] = $course->fullname;
        $templatevars['{course_shortname}'] = $course->shortname;
        $templatevars['{sender_firstname}'] = $userfrom->firstname;
        $templatevars['{sender_lastname}'] = $userfrom->lastname;
        $templatevars['{notification_name}'] = $notification->title;

        $result = \strtr($contenttotemplate, $templatevars);

        return $result;
    }

    /**
     * @return array<mixed>
     */
    public function get_summary_fields() {
        return [
            $this->id,
            // TODO: Should this be a link?
            $this->get_learner_notification()->title ?? '',
            $this->get_manager_notification()->title ?? '',
            \ucfirst($this->remindertype)
        ];
    }

    /** {@inheritDoc} */
    protected function cast_fields() {
        $this->isenabled = (bool) $this->isenabled;
    }
}
