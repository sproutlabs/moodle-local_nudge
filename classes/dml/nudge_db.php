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
 * DML for {@see nudge}
 *
 * @package     local_nudge\dml
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

namespace local_nudge\dml;

use local_nudge\local\nudge;

defined('MOODLE_INTERNAL') || die();

/**
 * {@inheritDoc}
 * @extends abstract_nudge_db<nudge>
 */
class nudge_db extends abstract_nudge_db
{
    /** {@inheritdoc} */
    protected static $table = 'nudge';

    /** {@inheritdoc} */
    protected static $entity_class = nudge::class;

    /**
     * Finds a {@see nudge} instance for this course or creates one.
     * @param int $courseid
     * @return nudge
     */
    public static function find_or_create($courseid)
    {
        $existing_nudge = static::get_filtered([
            'courseid' => $courseid
        ]);

        if ($existing_nudge !== null) return $existing_nudge;

        $new_nudge = new static::$entity_class([
            'courseid' => $courseid
        ]);

        static::save($new_nudge);

        return $new_nudge;
    }

    /**
     * Returns an array of active instances.
     * @return array<nudge>
     */
    public static function get_enabled()
    {
        return static::get_all_filtered([
            'isenabled' => 1
        ]);
    }
}