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
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

namespace local_nudge\local;

use coding_exception;
use UnexpectedValueException;

defined('MOODLE_INTERNAL') || die();

class nudge_user
{
    /**
     * @var array<string, mixed> Field defaults.
     */
    const DEFAULTS = [];

    /**
     * @var int|null Primary key for {@see nudge_user}.
     */
    public $id = null;

    /**
     * @var int|null Foreign key for has_one to {@see \core\entity\user}.
     */
    public $userid = null;

    /**
     * @var int|null Foreign key for has_one to {@see nudge}.
     */
    public $nudgeid = null;

    /**
     * Used to track individual enrollments for recurring notifications.
     * 
     * @var int|null Timestamp representing the last time this user was sent a notification.
     */
    public $recurrancetime = null;

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
            $setter = "set_{$key}";
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
