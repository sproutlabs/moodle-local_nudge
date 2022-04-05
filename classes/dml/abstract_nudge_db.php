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

namespace local_nudge\dml;

use coding_exception;
use local_nudge\local\abstract_nudge_entity;
use stdClass;

/**
 * Abstract DML to wrap DB records returned as STDClass in more type hinted entity.
 *
 * @todo Handle IDs that a less than 0 with nice moodle exception saying "can't find entityclass with id etc".
 *
 * Coding exception used throughout and are not documented with @\throws since they should not occur at runtime.
 *
 * @package     local_nudge\dml
 * @author      Liam Kearney <liam@sproutlabs.com.au>
 * @copyright   (c) 2022, Sprout Labs { @see https://sproutlabs.com.au }
 *
 * @template T
 */
abstract class abstract_nudge_db {

    /**
     * @var string|null Table for this entity manager.
     */
    public static $table = null;

    /**
     * @var class-string|null Managed entity's class.
     */
    public static $entityclass = null;

    /**
     * See the static methods of {@see static} instead.
     *
     * Some IDEs/editors (mine) make __construct public regardless of actual visibility so just call it public.
     * Runtime will fail with the actual visibility if really needed.
     *
     * @deprecated Don't use this see comments above.
     * @access public
     */
    private function __construct() {
        throw new coding_exception('Read the doc-blocks for this method.');
    }

    /**
     * @param int $id
     * @throws coding_exception
     * @return T|null
     */
    public static function get_by_id(int $id): ?abstract_nudge_entity {
        if (!\is_int($id) || ($id <= 0)) {
            throw new coding_exception(\sprintf('You must supply an integer to %s', __METHOD__));
        }

        /** @var \moodle_database $DB */
        global $DB;

        $record = $DB->get_record(static::$table, ['id' => $id]);

        return ($record instanceof stdClass) ? new static::$entityclass($record) : null;
    }

    /**
     * Returns all registered entities
     *
     * @return array<T>
     */
    public static function get_all(): array {
        /** @var \moodle_database $DB */
        global $DB;

        /** @var array<T> $entities */
        $entities = [];
        foreach ($set = $DB->get_recordset(static::$table) as $record) {
            $entities[] = new static::$entityclass($record);
        }
        $set->close();

        return $entities;
    }

    /**
     * Gets an {@see T} instance using a filter
     *
     * @param array $filter A {@see moodle_database::get_record()} filter.
     * @return T|null
     */
    public static function get_filtered(array $filter): ?abstract_nudge_entity {
        if (!\is_array($filter)) {
            throw new coding_exception(\sprintf('You must supply an array to %s as a filter', __METHOD__));
        }

        /** @var \moodle_database $DB */
        global $DB;

        $record = $DB->get_record(static::$table, $filter);

        return ($record instanceof stdClass) ? new static::$entityclass($record) : null;
    }

    /**
     * Gets multiple {@see T} instances using a filter. (AND aggregated)
     *
     * @param array $filter A {@see moodle_database::get_record()} filter.
     * @return array<T>
     */
    public static function get_all_filtered(array $filter): array {
        if (!\is_array($filter)) {
            throw new coding_exception(\sprintf('You must supply an array to %s as a filter', __METHOD__));
        }

        /** @var \moodle_database $DB */
        global $DB;

        /** @var array<T> $entities */
        $entities = [];
        foreach ($set = $DB->get_recordset(static::$table, $filter) as $record) {
            $entities[] = new static::$entityclass($record);
        }
        $set->close();

        return $entities;
    }

    /**
     * Gets an instance of {@see T} filtered by SQL.
     *
     * Be careful to ensure your SQL returns all the fields required to be wrapped in
     * an {@see T} or you will encounter a {@throws \UnexpectedValueException}.
     *
     * @param string $sql MUST return a single instance. {@see static::get_all_sql()} for multiple.
     * @param array|null $params SQL Params.
     * @return T|null Returns a single wrapped instance of {@see T}.
     */
    public static function get_sql(string $sql, ?array $params = null): ?abstract_nudge_entity {
        if (!\is_string($sql)) {
            throw new coding_exception(\sprintf('You must supply a string to %s as SQL', __METHOD__));
        }

        /** @var \moodle_database $DB */
        global $DB;

        $record = $DB->get_record_sql($sql, $params);

        return ($record instanceof stdClass)
            ? new static::$entityclass($record)
            : null;
    }

    /**
     * Gets instances of {@see T} filtered by SQL.
     *
     * Be careful to ensure your SQL returns all the fields required to be wrapped in
     * an {@see T} or you will encounter a {@throws UnexpectedValueException}.
     *
     * @param string $sql Will return multiple. {@see static::get_sql} for a non array wrapped return.
     * @param array|null $params SQL Params.
     * @return array<T>
     */
    public static function get_all_sql(string $sql, ?array $params = null): array {
        if (!\is_string($sql)) {
            throw new coding_exception(\sprintf('You must supply a string to %s as SQL', __METHOD__));
        }

        /** @var \moodle_database $DB */
        global $DB;

        /** @var array<T> $entities */
        $entities = [];
        foreach ($set = $DB->get_recordset_sql($sql, $params) as $record) {
            $entities[] = new static::$entityclass($record);
        }
        $set->close();

        return $entities;
    }

    /**
     * Persists or create a database row from a {@see T} instance.
     *
     * @param T $instance
     */
    public static function save(abstract_nudge_entity $instance) {
        /** @var \moodle_database $DB */
        global $DB;

        $instance = clone $instance;

        $instance->lastmodified = \time();

        if ($instance->id === null || $instance->id === 0) {
            // Add defaults they exist and are null in the current record.
            static::populate_defaults($instance);

            self::call_hook('on_before_create', $instance);

            $createdid = $DB->insert_record(static::$table, $instance);

            self::call_hook('on_after_create', $createdid);

            return $createdid;
        }

        self::call_hook('on_before_save', $instance);

        $DB->update_record(static::$table, $instance);

        self::call_hook('on_after_save', $instance->id);

        return $instance->id;
    }

    // These delete functions don't actually wrap an entity so they are a pretty much pointless wrapper around $DB->delete etc.
    // But it is nice to have everything consistant.

    /**
     * Removes an {@see T} instance from the database.
     *
     * WARNING: May delete all {@see T} instances that use `id` as something other than the primary key.
     *
     * @param int|null $id
     * @throws coding_exception
     * @return void
     */
    public static function delete(?int $id = null): void {
        if (!\is_int($id) || ($id <= 0)) {
            throw new coding_exception(\sprintf('You must supply an integer to %s', __METHOD__));
        }

        /** @var \moodle_database $DB */
        global $DB;

        self::call_hook('on_before_delete', $id);

        $DB->delete_records(static::$table, ['id' => $id]);

        self::call_hook('on_after_delete', $id);
    }

    /**
     * Removes all instances of {@see T} matching the filter.
     *
     * @todo Bulk hooks.
     * @param array $filter
     * @return void
     */
    public static function delete_all(array $filter): void {
        if (!\is_array($filter)) {
            throw new coding_exception(\sprintf('You must supply an array to %s as a filter', __METHOD__));
        }

        /** @var \moodle_database $DB */
        global $DB;

        $DB->delete_records(static::$table, $filter);
    }

    /**
     * Removes all instances of {@see T} filtered by SQL.
     *
     * @todo Bulk hooks.
     * @param string $sql
     * @param array|null $params
     * @return void
     */
    public static function delete_all_select(string $sql, ?array $params = null): void {
        if (!\is_string($sql)) {
            throw new coding_exception(\sprintf('You must supply a string to %s as SQL', __METHOD__));
        }

        /** @var \moodle_database $DB */
        global $DB;

        $DB->delete_records_select(static::$table, $sql, $params);
    }

    /**
     * Populates default fields if they are not already set.
     *
     * Replaces the following values (strict) if registered as a default:
     *  - null
     *  - '' (empty strings)
     *  - 0 (int)
     *
     * @param T $instance
     * @return void
     */
    public static function populate_defaults(abstract_nudge_entity $instance): void {
        foreach (static::$entityclass::DEFAULTS as $defaultfield => $value) {
            if ($instance->{$defaultfield} === null ||
                $instance->{$defaultfield} === '' ||
                $instance->{$defaultfield} === 0
            ) {
                $instance->{$defaultfield} = $value;
            }
        }
    }

    /**
     * Calls a very simple hook.
     *
     * @param string $methodname
     * @param mixed $data Most often a {@see T} or int
     * @return void
     */
    private static function call_hook(string $methodname, $data): void {
        if (\method_exists(static::class, $methodname)) {
            \call_user_func_array([static::class , $methodname], [$data]);
        }
    }
}
