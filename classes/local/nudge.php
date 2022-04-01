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

use local_nudge\dml\nudge_notification_db;
use local_nudge\local\nudge_notification;
use moodle_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once(__DIR__ . '/../../lib.php');

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
        '{course_link}',
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

    /**
     * @return array<mixed>
     */
    public function get_summary_fields() {
        return [
            $this->id,
            // TODO: Should this be a link?
            // TODO: If it has both notifications but the type is still only one -
            // show only one.
            $this->get_learner_notification()->title ?? 'None',
            $this->get_manager_notification()->title ?? 'None',
            \ucfirst($this->remindertype)
        ];
    }

    /**
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
                break;
        }
    }

    /** {@inheritDoc} */
    protected function cast_fields() {
        $this->isenabled = (bool) $this->isenabled;
    }
}
