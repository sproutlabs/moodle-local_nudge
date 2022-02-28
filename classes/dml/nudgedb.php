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
 * @package     local_nudge
 * @author      Liam Kearney
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

namespace local_nudge\dml;

use coding_exception;
use local_nudge\local\nudge;

defined('MOODLE_INTERNAL') || die();

/**
 * We can't use Totara's entity system so let's use the tried and tested entity system from Catalyst IT's auth_outage.
 * 
 * @package     local_nudge\dml
 * @author      Liam Kearney
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 */
class nudgedb
{
    /**
     * See the static methods of {@see \local_nudge\dml\nudgedb} instead.
     * 
     * Some IDEs/editors (mine..) make __construct public regardless of actual visibility so just call it public.
     * Then redirect but at runtime fail with the actual visibility if really needed.
     * 
     * @access public
     */
    private function __construct()
    {
    }

    /**
     * Returns all registered nudge instances
     * 
     * @todo sort param.
     * 
     * @return array<nudge>
     */
    public static function get_all($sort = nudge::SORT_RELATIVE_NOW)
    {
        /** @var \moodle_database $DB */
        global $DB;

        foreach ($set = $DB->get_recordset('local_nudge') as $instance) {
            /** @var array<nudge> $instances */
            $instances[] = $instance;
        }
        $set->close();

        return $instances;
    }

    /**
     * @param int $id
     * @throws coding_exception
     * @return nudge|null
     */
    public static function get_by_id($id)
    {
        if (!\is_int($id) || ($id <= 0)) {
            throw new coding_exception(\sprintf('You must supply an integer to %s', __METHOD__));
        }

        /** @var \moodle_database $DB */
        global $DB;

        $record = $DB->get_record('local_nudge', ['id' => $id]);

        return ($record) ? new nudge($record) : null;
    }


    /**
     * @param int|null $id
     * @throws coding_exception
     * @return void
     */
    public static function delete($id = null)
    {
        if (!\is_int($id) || ($id <= 0)) {
            throw new coding_exception(\sprintf('You must supply an integer to %s', __METHOD__));
        }

        /** @var \moodle_database $DB */
        global $DB;

        // TODO: see auth_outage for logging events here.

        $DB->delete_records('local_nudge', ['id' => $id]);
    }
}
