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
use moodle_exception;
use stdClass;
use moodle_url;

// @codeCoverageIgnoreStart
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once(__DIR__ . '/../../lib.php');
// @codeCoverageIgnoreEnd

/**
 * @package     local_nudge\local
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @copyright   GNU GPL v3 or later
 */
class nudge extends abstract_nudge_entity {

    // This is just here since the nudge entity is pretty universal.
    // Example: `Monday 17th of June at 11:38am, 2444`
    public const DATE_FORMAT_NICE = 'l jS \of F \a\\t g:ia, Y';

    // BEGIN ENUM - REMINDER DATE    ////////////////////
    /**
     * This Nudge instance's reminder timing is a fixed date selected when setting up the reminder.
     */
    public const REMINDER_DATE_INPUT_FIXED = 'fixed';

    /**
     * This Nudge instance's reminder timing is relative to the course's end date.
     */
    public const REMINDER_DATE_RELATIVE_COURSE_END = 'courseend';

    /**
     * This Nudge instance's reminder timing is relative to the user's date of enrollment.
     */
    public const REMINDER_DATE_RELATIVE_ENROLLMENT = 'enrollment';

    /**
     * This Nudge instance's reminder timing recurrs based on the user's date of enrollment.
     */
    public const REMINDER_DATE_RELATIVE_ENROLLMENT_RECURRING = 'enrollmentrecurring';

    /**
     * This Nudge instance's reminder timing is based on course completion
     */
    public const REMINDER_DATE_COURSE_COMPLETION = 'coursecompletion';
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

    public const DEFAULTS = [
        'title' => 'Untitled Nudge',
        'isenabled' => 0,
        'reminderrecipient' => self::REMINDER_RECIPIENT_LEARNER,
        'remindertype' => self::REMINDER_DATE_RELATIVE_COURSE_END,
    ];

    /**
     * {@see hydrate_notification_template()}
     *
     * @var array<string>
     */
    public const TEMPLATE_VARIABLES = [
        '{user_firstname}' => 'Unresolved',
        '{user_lastname}' => 'Unresolved',
        '{course_fullname}' => 'Unresolved',
        '{course_shortname}' => 'Unresolved',
        '{course_link}' => 'Unresolved',
        '{course_enddate}' => 'Unresolved',
        '{sender_firstname}' => 'Unresolved',
        '{sender_lastname}' => 'Unresolved',
        '{sender_email}' => 'Unresolved',
        '{notification_title}' => 'Unresolved',
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
     * @var string|null This is a decorative title for this nudge.
     */
    public $title = null;

    /**
     * @var bool|null Is this instance of nudge enabled (Is nudge enabled for this course)?
     */
    public $isenabled = null;

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
     * @var int|null Timestamp.
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
     * the period of time post learner enrollment to send nudges
     * ```
     * {@see self::$remindertype} == {@see self::REMINDER_DATE_RELATIVE_ENROLLMENT}.
     * ```
     * OR
     * the period of time post learner enrollment to repeat nudges if
     * ```
     * {@see self::$remindertype} == {@see self::REMINDER_DATE_RELATIVE_ENROLLMENT_RECURRING}.
     * ```
     *
     * @var int|null Time in seconds.
     */
    public $remindertypeperiod = null;

    /**
     * Returns the notification configured to go to the learners.
     *
     * @return nudge_notification|null
     */
    public function get_learner_notification() {
        if ($this->linkedlearnernotificationid == 0) {
            return null;
        }
        return nudge_notification_db::get_by_id($this->linkedlearnernotificationid);
    }

    /**
     * Returns the notification configured to go to a learner's managers.
     *
     * @return nudge_notification|null
     */
    public function get_manager_notification() {
        if ($this->linkedmanagernotificationid == 0) {
            return null;
        }
        return nudge_notification_db::get_by_id($this->linkedmanagernotificationid);
    }

    /**
     * Returns the course this nudge is linked to.
     *
     * @return \core\entity\course|\stdClass
     */
    public function get_course() {
        return \get_course($this->courseid);
    }

    /**
     * Returns an array of mixed values to be casted to string an rendered as raw html on the display tables.
     *
     * @return array<mixed>
     */
    public function get_summary_fields(): array {
        $learnernotification = $this->get_learner_notification() ?? false;
        $managernotification = $this->get_manager_notification() ?? false;

        if ($this->reminderrecipient !== self::REMINDER_RECIPIENT_BOTH) {
            if ($this->reminderrecipient !== self::REMINDER_RECIPIENT_LEARNER) {
                $learnernotification = false;
            }
            if ($this->reminderrecipient !== self::REMINDER_RECIPIENT_MANAGERS) {
                $managernotification = false;
            }
        }

        return [
            $this->get_nudge_edit_link(),
            $this->get_status_badge(),
            ($learnernotification)
                ? $learnernotification->get_notification_edit_link()
                : 'None',
            ($managernotification)
                ? $managernotification->get_notification_edit_link()
                : 'None',
            \ucfirst($this->remindertype)
        ];
    }

    /**
     * Gets a link to edit this nudge
     *
     * Note that this is scoped to course so the user may need that capability.
     *
     * @return string
     */
    public function get_nudge_edit_link(): string {
        /** @var \core_config $CFG */
        global $CFG;

        $link = "{$CFG->wwwroot}/local/nudge/edit_nudge.php?id={$this->id}&courseid={$this->courseid}";

        $linktitle = \get_string('nudge_edit_link', 'local_nudge', $this->title);

        $linkhtml = <<<HTML
            <a href="{$link}">{$linktitle}</a>
        HTML;

        return $linkhtml;
    }

    /**
     * Gets a simple bootstrap badge showing the {@see self::$isenabled} status of this nudge.
     *
     * @return string
     */
    public function get_status_badge(): string {
        $badgeclass = ($this->isenabled)
            ? 'success'
            : 'danger';
        $badgetext = ($this->isenabled)
            ? get_string('nudge_status_text_enabled', 'local_nudge')
            : get_string('nudge_status_text_disabled', 'local_nudge');

        return <<<HTML
            <div class="badge badge-{$badgeclass}">
                {$badgetext}
            </div>
        HTML;
    }

    /**
     * Notifies both the user who created and last modified this nudge.
     * This is especially handy if the nudge has encountered an exception case and needs to be corrected.
     *
     * @param string $subject
     * @param string $body Can be rich html.
     * @return void
     */
    public function notify_owners(string $subject, string $body): void {
        $course = $this->get_course();

        foreach ([$this->lastmodifiedby, $this->createdby] as $userid) {
            $user = core_user::get_user($userid, '*', \IGNORE_MISSING & \IGNORE_MULTIPLE);

            if (!$user) {
                continue;
            }

            $message = new message();
            $message->component = 'local_nudge';
            $message->name = 'owneremail';
            $message->userfrom = core_user::get_noreply_user();
            $message->userto = $user;
            $message->subject = $subject;
            $message->fullmessageformat = \FORMAT_HTML;
            $message->fullmessagehtml = $body;
            $message->notification = 1;
            $message->courseid = $course->id;
            $message->contexturl = new moodle_url('/course/view.php', ['id' => $course->id]);
            $message->contexturlname = 'Course Link';
            \message_send($message);
        }
    }

    /**
     * Triggers this nudge to cause messages to send.
     * This doesn't account for timing it merely sends the configured notifications to the configured recipients.
     *
     * @param \core\entity\user|stdClass $user
     */
    public function trigger($user): void {
        switch ($this->reminderrecipient) {
            case (self::REMINDER_RECIPIENT_BOTH):
                \message_send(nudge_get_email_message($this, $user));
                foreach (nudge_get_managers_for_user($user) as $manager) {
                    if ($manager === null) {
                        continue;
                    }
                    \message_send(nudge_get_email_message($this, $user, $manager));
                }
                break;

            case (self::REMINDER_RECIPIENT_LEARNER):
                \message_send(nudge_get_email_message($this, $user));
                break;

            case (self::REMINDER_RECIPIENT_MANAGERS):
                foreach (nudge_get_managers_for_user($user) as $manager) {
                    if ($manager === null) {
                        continue;
                    }
                    \message_send(nudge_get_email_message($this, $user, $manager));
                }
                break;
            default:
                // Weird.
                throw new moodle_exception(
                    'expectedunreachable',
                    'local_nudge'
                );
        }
    }

    protected function cast_fields(): void {
        $this->courseid = (int) $this->courseid;
        $this->linkedlearnernotificationid = (int) $this->linkedlearnernotificationid;
        $this->linkedmanagernotificationid = (int) $this->linkedmanagernotificationid;
        $this->title = (string) $this->title;
        $this->isenabled = (bool)($this->isenabled ?? false);
        $this->reminderrecipient = (string) $this->reminderrecipient;
        $this->remindertype = (string) $this->remindertype;
        $this->remindertypefixeddate = (int) $this->remindertypefixeddate;
        $this->remindertypeperiod = (int) $this->remindertypeperiod;
    }
}
