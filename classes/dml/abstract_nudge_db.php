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
 * This class provides some abstract methods that local_nudge uses to manage its entities.
 * 
 * @package     local_nudge\dml
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @license     GNU GPL v3 or later
 */

namespace local_nudge\dml;

use coding_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();

/**
 * @package     local_nudge\dml
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 * 
 * @template T
 */
abstract class abstract_nudge_db
{
    /**
     * @var string|null Table for this entity manager.
     */
    protected static $table = null;

    /**
     * @var class-string|null Managed entity's class.
     */
    protected static $entity_class = null;

    /**
     * See the static methods of {@see static} instead.
     * 
     * Some IDEs/editors (mine) make __construct public regardless of actual visibility so just call it public.
     * Runtime will fail with the actual visibility if really needed.
     * 
     * @deprecated Don't use this see comments above.
     * @access public
     */
    private function __construct()
    {
        throw new coding_exception('Read the doc-blocks for this method.');
    }

    /**
     * Returns all registered entities
     * @todo sort param.
     * 
     * @return array<T>
     */
    public static function get_all()
    {
        /** @var \moodle_database $DB */
        global $DB;

        /** @var array<T> $entities */
        $entities = [];
        foreach ($set = $DB->get_recordset(static::$table) as $record) {
            $entities[] = new static::$entity_class($record);
        }
        $set->close();

        return $entities;
    }

    /**
     * @param int $id
     * @throws coding_exception
     * @return T|null
     */
    public static function get_by_id($id)
    {
        if (!\is_int($id) || ($id <= 0)) {
            throw new coding_exception(\sprintf('You must supply an integer to %s', __METHOD__));
        }

        /** @var \moodle_database $DB */
        global $DB;

        $record = $DB->get_record(static::$table, ['id' => $id]);

        return ($record instanceof stdClass) ? new static::$entity_class($record) : null;
    }

    /**
     * Gets an entity instance using a filter
     * @param array $filter A {@see moodle_database::get_record()} filter.
     * @return T|null
     */
    public static function get_filtered($filter)
    {
        if (!\is_array($filter)) {
            throw new coding_exception(\sprintf('You must supply an array to %s as a filter', __METHOD__));
        }

        /** @var \moodle_database $DB */
        global $DB;

        $record = $DB->get_record(static::$table, $filter);

        return ($record instanceof stdClass) ? new static::$entity_class($record) : null;
    }

    /**
     * Gets multiple entity instances using a filter. (AND aggregated)
     * @param array $filter A {@see moodle_database::get_record()} filter.
     * @return array<T>
     */
    public static function get_all_filtered($filter)
    {
        if (!\is_array($filter)) {
            throw new coding_exception(\sprintf('You must supply an array to %s as a filter', __METHOD__));
        }

        /** @var \moodle_database $DB */
        global $DB;

        /** @var array<T> $entities */
        $entities = [];
        foreach ($set = $DB->get_recordset(static::$table, $filter) as $record) {
            $entities[] = new static::$entity_class($record);
        }
        $set->close();

        return $entities;
    }

    /**
     * Persists or create a database row from a {@see nudge} instance.
     * @todo Flesh out with events etc.
     * @param T $instance
     */
    public static function save($instance)
    {
        /** @var \moodle_database $DB */
        global $DB;

        $instance = clone $instance;

        $instance->lastmodified = \time();

        if ($instance->id === null) {
            // Add defaults in if unset.
            foreach (static::$entity_class::DEFAULTS as $default_field => $value) {
                if ($instance->$default_field === null) {
                    $instance->$default_field = $value;
                }
            }

            $instance->id = $DB->insert_record(static::$table, $instance);
        }

        $DB->update_record(static::$table, $instance);

        return $instance->id;
    }

    /**
     * Removes an entity instance from the database.
     * 
     * WARNING: Will delete all entity instances if `id` is not the primary key.
     * 
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

        $DB->delete_records(static::$table, ['id' => $id]);
    }
}
