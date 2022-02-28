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
 * @package     local_nudge\local
 * @author      Liam Kearney
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 * 
 * @var \core_config        $CFG
 * @var \moodle_database    $DB
 */

namespace local_nudge\local;

use coding_exception;
use UnexpectedValueException;

/**
 * A nudge entity represents a course that wants to use completion reminders.
 * 
 * The (not quite single) responsiblity of this entity is to store metadata about this courses reminders.
 * 
 * @package     local_nudge\local
 * @author      Liam Kearney
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @copyright   GNU GPL v3 or later
 */
class nudge
{
    // TODO: SORT.
    const SORT_RELATIVE_NOW = '';

    /**
     * This Nudge instance's reminder timing is a fixed date selected when setting up the reminder.
     */
    const REMINDER_DATE_INPUT_FIXED = 'fixed';

    /**
     * This Nudge instance's reminder timing is based on the user's date of enrollment.
     */
    const REMINDER_DATE_RELATIVE_ENROLLMENT = 'enrollment';

    /**
     * This Nudge instance's reminder timing is infered from the course's end date.
     */
    const REMINDER_DATE_RELATIVE_COURSE_END = 'courseend';

    /**
     * @var int|null The autoincrement index of this entity.
     */
    public $id = null;

    /**
     * @var int|null The id of the Course linked to this nudge entity.
     */
    public $course = null;

    /**
     * @var int|null The timestamp this nudge instance was last modified at.
     */
    public $lastmodified = null;

    /**
     * @var string|null The the reminder type of this nudge.
     */
    public $remindertype = null;

    /**
     * @todo Make this less redundant if another option is selected.
     * @var string|null The fixed reminder date if needed.
     */
    public $fixedreminderdate = null;

    /**
     * @param stdClass|array|null $data The data to wrap with a nudge entity/instance.
     * @throws UnexpectedValueException Passed a field that doesn't exist.
     * @throws coding_exception
     */
    public function __construct($data = null)
    {
        if ($data == null) return;
        if (\is_object($data)) $data = (array)$data;
        if (!\is_array($data)) {
            throw new coding_exception(\sprintf('You must provide valid data to %s to wrap a instance of %s', __METHOD__, __CLASS__));
        }

        foreach ($data as $key => $value) {
            $setter = "set{$key}";
            if (\method_exists($this, $setter)) {
                $this->$setter($key);
                continue;
            }

            if (\property_exists($this, $key)) {
                $this->$key = $value;
                continue;
            }

            throw new UnexpectedValueException(\sprintf(
                '%s\'s %s method was passed a property/field that doesn\'t exist on %s. Property name was: %s',
                __CLASS__,
                __METHOD__,
                __CLASS__,
                $key
            ));
        }

        $this->cast_fields();
    }

    /**
     * Casts the fields populated by {@see self::__construct()} to some sane defaults.
     * 
     * @return void
     */
    private function cast_fields()
    {
        // TODO.
    }
}
